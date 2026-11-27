<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2026 pdir / digital agentur // pdir GmbH
 *
 * @package    social-feed-bundle
 * @link       https://github.com/pdir/social-feed-bundle
 * @license    http://www.gnu.org/licences/lgpl-3.0.html LGPL
 * @author     Mathias Arzberger <develop@pdir.de>
 * @author     Philipp Seibt <develop@pdir.de>
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\SocialFeedBundle\Controller;

use Contao\BackendUser;
use Contao\Message;
use Contao\System;
use Doctrine\DBAL\Connection;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedServiceCallbackListener;
use Pdir\SocialFeedBundle\Importer\InstagramClient;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/_instagram', defaults: ['_scope' => 'backend', '_token_check' => false])]
class InstagramController
{
    /**
     * @var InstagramClient
     */
    private $client;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * InstagramController constructor.
     */
    public function __construct(InstagramClient $client, Connection $db, RouterInterface $router, TokenStorageInterface $tokenStorage)
    {
        $this->client = $client;
        $this->db = $db;
        $this->router = $router;
        $this->session = System::getContainer()->get('request_stack')->getCurrentRequest()->getSession();
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/auth', name: 'instagram_auth', methods: ['GET'])]
    public function authAction(Request $request): Response
    {
        // Missing code query parameter
        if (!($code = $request->query->get('code'))) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        // User not logged in
        if (null === $this->getBackendUser()) {
            return new Response(Response::$statusTexts[Response::HTTP_UNAUTHORIZED], Response::HTTP_UNAUTHORIZED);
        }

        $sessionData = $this->session->get(SocialFeedServiceCallbackListener::SESSION_KEY);

        // Social feed ID not found in session
        if (null === $sessionData || !isset($sessionData['sfServiceId'])) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        // Invalid state parameter (CSRF protection)
        if (isset($sessionData['state']) && !hash_equals((string) $sessionData['state'], (string) $request->query->get('state'))) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        // Social feed service not found
        if (false === ($service = $this->db->fetchAssociative('SELECT * FROM tl_sf_service WHERE id=?', [$sessionData['sfServiceId']]))) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        System::getContainer()->get('contao.framework')->initialize();
        System::loadLanguageFile('tl_sf_service');

        $redirectUri = $this->router->generate('instagram_auth', [], RouterInterface::ABSOLUTE_URL);

        if (InstagramClient::isUnifiedLogin($service['loginType'] ?? null)) {
            return $this->handleUnifiedLogin($service, $code, $redirectUri, $sessionData);
        }

        $longLivedAccessToken = $this->client->getAccessToken(
            (string) $service['appId'],
            (string) $service['appSecret'],
            $code,
            $redirectUri
        );

        if (null === $longLivedAccessToken || empty($longLivedAccessToken['access_token'])) {
            return $this->redirectWithError($sessionData, $GLOBALS['TL_LANG']['tl_sf_service']['instagramTokenError'] ?? 'The Instagram access token could not be requested. Please check the system log.');
        }

        // Update the existing account, new accounts are selected in the account overview
        $userData = $this->client->getUserData($longLivedAccessToken['access_token'], (int) $service['id'], false);

        if (!empty($userData['data']['id'])) {
            $this->updateAccount((int) $service['id'], (string) $userData['data']['id'], (string) ($userData['data']['username'] ?? ''));
        }

        // Store the access token and remove temporary session key
        $this->db->update('tl_sf_service', [
            'accessToken' => $longLivedAccessToken['access_token'],
            'tokenExpires' => time() + (int) ($longLivedAccessToken['expires_in'] ?? 0),
            'tstamp' => time(),
        ], ['id' => $sessionData['sfServiceId']]);

        $this->session->remove(SocialFeedServiceCallbackListener::SESSION_KEY);

        return $this->redirectToAccountSelection((int) $service['id']);
    }

    /**
     * Handle the callback of the unified login (Facebook Login for Business with config_id).
     *
     * @param array<string, mixed> $service
     * @param array<string, mixed> $sessionData
     */
    private function handleUnifiedLogin(array $service, string $code, string $redirectUri, array $sessionData): Response
    {
        $token = $this->client->getUnifiedAccessToken((string) $service['appId'], (string) $service['appSecret'], $code, $redirectUri);

        if (null === $token) {
            return $this->redirectWithError($sessionData, $GLOBALS['TL_LANG']['tl_sf_service']['instagramTokenError'] ?? 'The Instagram access token could not be requested. Please check the system log.');
        }

        $accounts = $this->client->getUnifiedInstagramAccounts($token['access_token']);

        if ([] === $accounts) {
            return $this->redirectWithError($sessionData, $GLOBALS['TL_LANG']['tl_sf_service']['instagramAccountError'] ?? 'No Instagram account was shared during login. Please select an Instagram professional account and try again.');
        }

        // Update the existing accounts, new accounts are selected in the account overview. The token is stored at the service.
        foreach ($accounts as $account) {
            $this->updateAccount((int) $service['id'], $account['id'], $account['username']);
        }

        $this->db->update('tl_sf_service', [
            'accessToken' => $token['access_token'],
            // empty = never expires (system user access token)
            'tokenExpires' => $token['expires_at'] > 0 ? $token['expires_at'] : '',
            'tstamp' => time(),
        ], ['id' => $sessionData['sfServiceId']]);

        $this->session->remove(SocialFeedServiceCallbackListener::SESSION_KEY);

        return $this->redirectToAccountSelection((int) $service['id']);
    }

    /**
     * Continue with the selection of the accounts (dialog "add account" of the account overview).
     */
    private function redirectToAccountSelection(int $serviceId): Response
    {
        return new RedirectResponse($this->router->generate(AccountController::class, ['connect' => $serviceId]));
    }

    /**
     * Update the existing Instagram account with the given Instagram user ID. New accounts are not created.
     */
    private function updateAccount(int $serviceId, string $instagramUserId, string $username): void
    {
        $account = SocialFeedAccountModel::findOneBy(['type = ?', 'identifier = ?'], ['Instagram', $instagramUserId]);

        // Migrated accounts have no Instagram user ID yet, so assign the account of the service without ID
        $account ??= SocialFeedAccountModel::findOneBy(['type = ?', 'service = ?', "identifier = ''"], ['Instagram', $serviceId]);

        if (null === $account) {
            return;
        }

        $account->identifier = $instagramUserId;
        $account->name = $username ?: ($account->name ?: $instagramUserId);
        $account->service = $serviceId;
        $account->tstamp = time();
        $account->save();
    }

    /**
     * @param array<string, mixed> $sessionData
     */
    private function redirectWithError(array $sessionData, string $message): Response
    {
        $this->session->remove(SocialFeedServiceCallbackListener::SESSION_KEY);

        if (empty($sessionData['backUrl'])) {
            return new Response($message, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        Message::addError($message);

        return new RedirectResponse($sessionData['backUrl']);
    }

    /**
     * Get the backend user.
     */
    private function getBackendUser(): ?BackendUser
    {
        if (null === ($token = $this->tokenStorage->getToken())) {
            return null;
        }

        $user = $token->getUser();

        if (!($user instanceof BackendUser)) {
            return null;
        }

        return $user;
    }
}
