<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
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
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedListener;
use Pdir\SocialFeedBundle\Importer\InstagramClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('_instagram', defaults: ['_scope' => 'backend', '_token_check' => false])]
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

        $sessionData = $this->session->get(SocialFeedListener::SESSION_KEY);

        // Social feed ID not found in session
        if (null === $sessionData || !isset($sessionData['socialFeedId'])) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        // Invalid state parameter (CSRF protection)
        if (isset($sessionData['state']) && !hash_equals((string) $sessionData['state'], (string) $request->query->get('state'))) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        // Social feed account not found
        if (false === ($module = $this->db->fetchAssociative('SELECT * FROM tl_social_feed WHERE id=?', [$sessionData['socialFeedId']]))) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        if (InstagramClient::isUnifiedLogin($module['psf_instagramLoginType'] ?? null)) {
            return $this->handleUnifiedLogin($module, $code, $sessionData);
        }

        $longLivedAccessToken = $this->client->getAccessToken(
            $module['psf_instagramAppId'],
            $module['psf_instagramAppSecret'],
            $code,
            $this->router->generate('instagram_auth', [], RouterInterface::ABSOLUTE_URL)
        );

        if (null === $longLivedAccessToken || empty($longLivedAccessToken['access_token'])) {
            return new Response(Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Get the user and media data
        $this->client->getUserData($longLivedAccessToken['access_token'], (int) $module['id'], false);
        // $mediaData = $this->client->getMediaData($accessToken, (int) $module['id'], false);

        // Store the access token and remove temporary session key
        $this->db->update('tl_social_feed', ['psf_instagramAccessToken' => $longLivedAccessToken['access_token'], 'access_token_expires' => time() + $longLivedAccessToken['expires_in']], ['id' => $sessionData['socialFeedId']]);
        $this->session->remove(SocialFeedListener::SESSION_KEY);

        return new RedirectResponse($sessionData['backUrl']);
    }

    /**
     * Handle the callback of the unified login (Facebook Login for Business with config_id).
     */
    private function handleUnifiedLogin(array $module, string $code, array $sessionData): Response
    {
        System::getContainer()->get('contao.framework')->initialize();
        System::loadLanguageFile('tl_social_feed');

        $token = $this->client->getUnifiedAccessToken(
            (string) $module['psf_instagramAppId'],
            (string) $module['psf_instagramAppSecret'],
            $code,
            $this->router->generate('instagram_auth', [], RouterInterface::ABSOLUTE_URL)
        );

        if (null === $token) {
            return $this->redirectWithError($sessionData, $GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramUnifiedTokenError'] ?? 'The Instagram access token could not be requested. Please check the system log.');
        }

        $account = $this->client->getUnifiedInstagramAccount($token['access_token']);

        if (null === $account) {
            return $this->redirectWithError($sessionData, $GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramUnifiedAccountError'] ?? 'No Instagram account was shared during login. Please select an Instagram professional account and try again.');
        }

        $set = [
            'psf_instagramAccessToken' => $token['access_token'],
            // empty = never expires (system user access token)
            'access_token_expires' => $token['expires_at'] > 0 ? $token['expires_at'] : '',
            'psf_instagramUserId' => $account['id'],
        ];

        if ('' !== $account['username']) {
            $set['instagram_account'] = $account['username'];
        }

        $this->db->update('tl_social_feed', $set, ['id' => $sessionData['socialFeedId']]);
        $this->session->remove(SocialFeedListener::SESSION_KEY);

        return new RedirectResponse($sessionData['backUrl']);
    }

    private function redirectWithError(array $sessionData, string $message): Response
    {
        $this->session->remove(SocialFeedListener::SESSION_KEY);

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
