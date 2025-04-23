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

use Contao\Input;
use Contao\Message;
use Contao\System;
use Doctrine\DBAL\Connection;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedListener;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedServiceCallbackListener;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/_facebook", defaults={"_scope" = "backend", "_token_check" = false})
 */
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

    /**
     * @Route("/auth", name="facebook_auth", methods={"GET"})
     */
    public function authAction(Request $request): Response
    {
        $sessionData = $this->session->get(SocialFeedServiceCallbackListener::SESSION_KEY);

        dump($sessionData);

        $data = [
            'client_id' => $sessionData['clientId'],
            'client_secret' => $sessionData['clientSecret'],
            'redirect_uri' => $this->router->generate('facebook_auth', [], RouterInterface::ABSOLUTE_URL),
            'code' => $request->query->get('code'),
        ];

        $json = @file_get_contents('https://graph.facebook.com/v11.0/oauth/access_token?'.http_build_query($data));

        dump($json);

        if (false === $json) {
            Message::addError(Input::get('error_message'));

            return new RedirectResponse($sessionData['backUrl']);
        }

        $obj = json_decode($json);
        $userAccessToken = $obj->access_token;

        // Get accounts
        $json = @file_get_contents('https://graph.facebook.com/v20.0/me/accounts?access_token='.$userAccessToken);

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
            dump($obj);
            // Create accounts if not exists
            foreach($obj->data as $item) {
                dump($item);
                $account = SocialFeedAccountModel::findBy(['type=?', 'identifier=?'], ['Facebook', $item->id]);

                if (null === $account) {
                    $account = new SocialFeedAccountModel();
                    $account->name = $item->name;
                    $account->identifier = $sessionData['page'] = $item->id;
                    $account->type = 'Facebook';
                    $account->added = $account->tstamp = time();
                    $account->accessToken = $item->access_token;
                    $account->service = $sessionData['sfServiceId'];
                    $account->save();

                    $sessionData['page'];

                    continue;
                }

                $account->name = $item->name;
                $account->tstamp = time();
                $account->accessToken = $item->access_token;
                $account->save();

                $sessionData['page'] = $item->id;
            }
        }

        dump(json_decode($json));

        // Get token
        $json = @file_get_contents('https://graph.facebook.com/'.$sessionData['page'].'?fields=access_token&access_token='.$userAccessToken);

        dump(json_decode($json));
die();
        // set error message
        if (\is_bool($json) && false === $json) {
            if (Input::get('error_message')) {
                Message::addError(Input::get('error_message'));
            } else {
                $pageAccessToken = 'FACEBOOK GRAPH ERROR: empty return! Please check your credentials or account name.';
                Message::addError($pageAccessToken);
            }
        }

        if (!\is_bool($json)) {
            $obj = json_decode($json);
            $pageAccessToken = $obj->access_token;
        }

        // Store the access token and remove temporary session key
        $this->db->update('tl_sf_service', ['accessToken' => $pageAccessToken], ['id' => $sessionData['sfServiceId']]);
        $this->session->remove(SocialFeedServiceCallbackListener::SESSION_KEY);

        return new RedirectResponse($sessionData['backUrl']);
    }
}
