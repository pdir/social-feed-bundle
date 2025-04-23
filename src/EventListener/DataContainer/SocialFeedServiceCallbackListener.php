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

namespace Pdir\SocialFeedBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

class SocialFeedServiceCallbackListener
{
    public const SESSION_KEY = 'social-feed-id';

    /**
     * @var SessionInterface
     */
    private $session;
    public function __construct(private RouterInterface $router)
    {
        $this->session = System::getContainer()->get('request_stack')->getCurrentRequest()->getSession();
    }

    #[AsCallback(table: 'tl_sf_service', target: 'fields.requestToken.save')]
    public function onRequestTokenSave()
    {
        return null;
    }

    #[AsCallback(table: 'tl_sf_service', target: 'config.onsubmit')]
    public function onsubmitCallback(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        if (!Input::post('requestToken')) {
            return;
        }

        if ('Instagram' === $dc->activeRecord->type && $dc->activeRecord->appId) {
            $this->requestInstagramAccessToken($dc->activeRecord->appId);
        }

        if ('Facebook' === $dc->activeRecord->type && $dc->activeRecord->appId && $dc->activeRecord->appSecret) {
            $this->requestFacebookAccessToken($dc->activeRecord->appId, $dc->activeRecord->appSecret, $dc->activeRecord->apiVersion);
        }

        if ('LinkedIn' === $dc->activeRecord->type && $dc->activeRecord->clientId && $dc->activeRecord->clientSecret) {
            $this->requestLinkedinAccessToken($dc->activeRecord->clientId, $dc->activeRecord->clientSecret);
        }
    }

    /**
     * Request the Instagram access token.
     *
     * @param string $clientId
     */
    private function requestInstagramAccessToken(string $clientId): void
    {
        $this->session->set(self::SESSION_KEY, [
            'sfServiceId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
        ]);

        $this->session->save();

        $data = [
            'app_id' => $clientId,
            'redirect_uri' => $this->router->generate('instagram_auth', [], RouterInterface::ABSOLUTE_URL),
            'response_type' => 'code',
            'scope' => 'user_profile,user_media',
        ];

        throw new RedirectResponseException('https://api.instagram.com/oauth/authorize/?'.http_build_query($data));
    }

    /**
     * Request the Facebook access token.
     *
     * @param string $appId
     */
    private function requestFacebookAccessToken(string $appId, string $appSecret, $apiVersion): void
    {
        $this->session->set(self::SESSION_KEY, [
            'sfServiceId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
            'clientId' => $appId,
            'clientSecret' => $appSecret,
            # 'page' => $page,
        ]);

        $this->session->save();

        $data = [
            'client_id' => $appId,
            'redirect_uri' => $this->router->generate('facebook_auth', [], RouterInterface::ABSOLUTE_URL),
            'scope' => 'pages_read_engagement',
        ];

        throw new RedirectResponseException('https://www.facebook.com/'.$apiVersion.'/dialog/oauth?'.http_build_query($data));
    }

    /**
     * Request the LinkedIn access token.
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    private function requestLinkedinAccessToken(string $clientId, string $clientSecret): void
    {
        $this->session->set(self::SESSION_KEY, [
            'sfServiceId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);

        $this->session->save();

        $data = [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $this->router->generate('auth_linkedin', [], RouterInterface::ABSOLUTE_URL),
            'scope' => 'r_organization_social,rw_organization_admin',
        ];

        throw new RedirectResponseException('https://www.linkedin.com/oauth/v2/authorization?'.http_build_query($data));
    }
}
