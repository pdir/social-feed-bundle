<?php

/*
 * Instagram Bundle for Contao Open Source CMS.
 *
 * Copyright (C) 2011-2019 Codefog
 *
 * @author  Codefog <https://codefog.pl>
 * @author  Kamil Kuzminski <https://github.com/qzminski>
 * @license MIT
 *
 * https://github.com/codefog/contao-instagram
 *
 */

namespace Pdir\SocialFeedBundle\Controller;

use Pdir\SocialFeedBundle\EventListener\SocialFeedListener;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Facebook\Facebook;
use Contao\CoreBundle\Exception\RedirectResponseException;

/**
 * @Route("/_facebook", defaults={"_scope" = "backend", "_token_check" = false})
 */
class FacebookController
{
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
     * FacebookController constructor.
     */
    public function __construct(Connection $db, RouterInterface $router, SessionInterface $session)
    {
        $this->db = $db;
        $this->router = $router;
        $this->session = $session;
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
            'code' => $request->query->get('code')
        ];

        $json = file_get_contents('https://graph.facebook.com/v11.0/oauth/access_token?'.http_build_query($data));
        $obj = json_decode($json);
        $userAccessToken = $obj->access_token;

        $json = file_get_contents('https://graph.facebook.com/'.$sessionData['page'].'?fields=access_token&access_token='.$userAccessToken);
        $obj = json_decode($json);
        $pageAccessToken = $obj->access_token;

        // Store the access token and remove temporary session key
        $this->db->update('tl_social_feed', ['pdir_sf_fb_access_token' => $pageAccessToken], ['id' => $sessionData['socialFeedId']]);
        $this->session->remove(SocialFeedListener::SESSION_KEY);

        return new RedirectResponse($sessionData['backUrl']);
    }
}
