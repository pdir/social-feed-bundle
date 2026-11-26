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

use Contao\Input;
use Contao\Message;
use Contao\System;
use Doctrine\DBAL\Connection;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedServiceCallbackListener;
use Pdir\SocialFeedBundle\Importer\NewsImporter;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/_facebook', defaults: ['_scope' => 'backend', '_token_check' => false])]
class FacebookController
{
    private Connection $db;

    private RouterInterface $router;

    private SessionInterface $session;

    /**
     * FacebookController constructor.
     */
    public function __construct(Connection $db, RouterInterface $router)
    {
        $this->db = $db;
        $this->router = $router;
        $this->session = System::getContainer()->get('request_stack')->getCurrentRequest()->getSession();
    }

    #[Route('/auth', name: 'facebook_auth', methods: ['GET'])]
    public function authAction(Request $request): Response
    {
        $sessionData = $this->session->get(SocialFeedServiceCallbackListener::SESSION_KEY);

        if (null === $sessionData || !isset($sessionData['sfServiceId'])) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        $graphUrl = 'https://graph.facebook.com/'.($sessionData['apiVersion'] ?? 'v25.0');

        // Exchange the code for a short-lived user access token
        $json = @file_get_contents($graphUrl.'/oauth/access_token?'.http_build_query([
            'client_id' => $sessionData['clientId'],
            'client_secret' => $sessionData['clientSecret'],
            'redirect_uri' => $this->router->generate('facebook_auth', [], RouterInterface::ABSOLUTE_URL),
            'code' => $request->query->get('code'),
        ]));

        if (false === $json || empty(json_decode($json)->access_token)) {
            Message::addError(Input::get('error_message') ?: 'FACEBOOK GRAPH ERROR: Failed getting the user access token.');

            return new RedirectResponse($sessionData['backUrl']);
        }

        // Exchange the short-lived for a long-lived user access token, so the page access tokens do not expire
        $userAccessToken = json_decode($json)->access_token;

        $json = @file_get_contents($graphUrl.'/oauth/access_token?'.http_build_query([
            'grant_type' => 'fb_exchange_token',
            'client_id' => $sessionData['clientId'],
            'client_secret' => $sessionData['clientSecret'],
            'fb_exchange_token' => $userAccessToken,
        ]));

        if (false !== $json && !empty(json_decode($json)->access_token)) {
            $userAccessToken = json_decode($json)->access_token;
        } else {
            Message::addInfo('FACEBOOK GRAPH: Failed getting a long-lived user access token, the page access tokens may expire.');
        }

        // Get accounts (pages) of the user
        $json = @file_get_contents($graphUrl.'/me/accounts?'.http_build_query(['access_token' => $userAccessToken]));

        // Set error message
        if (\is_bool($json) && false === $json) {
            if (Input::get('error_message')) {
                Message::addError(Input::get('error_message'));
            } else {
                $pageAccessToken = 'FACEBOOK GRAPH ERROR: empty return! Failed getting accounts of user.';
                Message::addError($pageAccessToken);
            }
        }

        // Set accounts
        if (!\is_bool($json)) {
            $obj = json_decode($json);

            // Create or update the accounts (Facebook pages) of the user
            foreach ($obj->data ?? [] as $item) {
                $account = SocialFeedAccountModel::findOneBy(['type = ?', 'identifier = ?'], ['Facebook', $item->id]);

                if (null === $account) {
                    $account = new SocialFeedAccountModel();
                    $account->type = 'Facebook';
                    $account->identifier = $item->id;
                    $account->added = time();
                }

                $account->name = $item->name;
                $account->tstamp = time();
                $account->accessToken = $item->access_token;
                $account->service = (int) $sessionData['sfServiceId'];
                $account->save();

                // the avatar needs the id of the saved account
                $account->avatar = $this->getAvatar($account);
                $account->save();

                $sessionData['page'] = $item->id;
            }
        }

        // Store the user access token at the service (the page access tokens are stored at the accounts)
        $this->db->update('tl_sf_service', ['accessToken' => $userAccessToken, 'tstamp' => time()], ['id' => $sessionData['sfServiceId']]);
        $this->session->remove(SocialFeedServiceCallbackListener::SESSION_KEY);

        return new RedirectResponse($sessionData['backUrl']);
    }

    private function getAvatar(SocialFeedAccountModel $account): ?string
    {
        $imgPath = NewsImporter::createImageFolder($account->id); // create image folder if not exists

        // Get profile picture
        return NewsImporter::saveImage($imgPath.'avatar.jpg', 'https://graph.facebook.com/'.$account->identifier.'/picture?type=large');
    }
}
