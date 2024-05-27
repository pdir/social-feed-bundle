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
        $sessionData = $this->session->get(SocialFeedListener::SESSION_KEY);

        $data = [
            'client_id' => $sessionData['clientId'],
            'client_secret' => $sessionData['clientSecret'],
            'redirect_uri' => $this->router->generate('facebook_auth', [], RouterInterface::ABSOLUTE_URL),
            'code' => $request->query->get('code'),
        ];

        $json = @file_get_contents('https://graph.facebook.com/v11.0/oauth/access_token?'.http_build_query($data));

        if (false === $json) {
            Message::addError(Input::get('error_message'));
            return new RedirectResponse($sessionData['backUrl']);
        }

        $obj = json_decode($json);
        $userAccessToken = $obj->access_token;

        $json = @file_get_contents('https://graph.facebook.com/'.$sessionData['page'].'?fields=access_token&access_token='.$userAccessToken);

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
        $this->db->update('tl_social_feed', ['pdir_sf_fb_access_token' => $pageAccessToken], ['id' => $sessionData['socialFeedId']]);
        $this->session->remove(SocialFeedListener::SESSION_KEY);

        return new RedirectResponse($sessionData['backUrl']);
    }
}
