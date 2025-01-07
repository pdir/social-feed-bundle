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

use Contao\System;
use Doctrine\DBAL\Connection;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/auth', name: ExampleController::class, defaults: ['_scope' => 'backend', '_token_check' => false])]
class LinkedinController
{
    private Connection $db;

    private RouterInterface $router;

    private SessionInterface $session;

    /**
     * LinkedinController constructor.
     */
    public function __construct(Connection $db, RouterInterface $router)
    {
        $this->db = $db;
        $this->router = $router;
        $this->session = System::getContainer()->get('request_stack')->getCurrentRequest()->getSession();
    }

    /**
     * @Route("/linkedin", name="auth_linkedin", methods={"GET"})
     */
    public function authAction(Request $request): Response
    {
        $sessionData = $this->session->get(SocialFeedListener::SESSION_KEY);

        // Missing code query parameter
        if (!$request->query->get('code')) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }
        //get refresh token
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $request->query->get('code'),
            'client_id' => $sessionData['clientId'],
            'client_secret' => \str_replace('&#61;', '=', $sessionData['clientSecret']),
            'redirect_uri' => $this->router->generate('auth_linkedin', [], RouterInterface::ABSOLUTE_URL),
        ];

        try {
            $token = json_decode(file_get_contents('https://www.linkedin.com/oauth/v2/accessToken?'.http_build_query($data)));

            // Store the access token and remove temporary session key
            $this->db->update('tl_social_feed', ['linkedin_access_token' => $token->access_token, 'access_token_expires' => time() + $token->expires_in, 'linkedin_refresh_token' => $token->refresh_token, 'linkedin_refresh_token_expires' => time() + $token->refresh_token_expires_in], ['id' => $sessionData['socialFeedId']]);
            $this->session->remove(SocialFeedListener::SESSION_KEY);
        } catch (\Exception $e) {
            System::log($e->getMessage(), __METHOD__, TL_GENERAL);
        }

        return new RedirectResponse($sessionData['backUrl']);
    }
}
