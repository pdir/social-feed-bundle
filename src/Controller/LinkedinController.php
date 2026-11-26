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

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Doctrine\DBAL\Connection;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedServiceCallbackListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/auth', defaults: ['_scope' => 'backend', '_token_check' => false])]
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

    #[Route('/linkedin', name: 'auth_linkedin', methods: ['GET'])]
    public function authAction(Request $request): Response
    {
        $sessionData = $this->session->get(SocialFeedServiceCallbackListener::SESSION_KEY);

        // Missing code query parameter
        if (!$request->query->get('code')) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }
        //get refresh token
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $request->query->get('code'),
            'client_id' => $sessionData['clientId'],
            'client_secret' => $sessionData['clientSecret'],
            'redirect_uri' => $this->router->generate('auth_linkedin', [], RouterInterface::ABSOLUTE_URL),
        ];

        try {
            $token = json_decode(file_get_contents('https://www.linkedin.com/oauth/v2/accessToken?'.http_build_query($data)));

            // Store the access token and remove temporary session key
            if (empty($token->access_token)) {
                throw new \RuntimeException('LinkedIn access token could not be generated.');
            }

            $this->db->update('tl_sf_service', [
                'accessToken' => $token->access_token,
                'tokenExpires' => time() + (int) $token->expires_in,
                'refreshToken' => $token->refresh_token ?? '',
                'refreshTokenExpires' => isset($token->refresh_token_expires_in) ? time() + (int) $token->refresh_token_expires_in : '',
                'tstamp' => time(),
            ], ['id' => $sessionData['sfServiceId']]);
            $this->session->remove(SocialFeedServiceCallbackListener::SESSION_KEY);
        } catch (\Exception $e) {
            System::getContainer()->get('monolog.logger.contao')->error($e->getMessage(), ['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)]);
        }

        return new RedirectResponse($sessionData['backUrl']);
    }
}
