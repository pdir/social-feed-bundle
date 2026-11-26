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

namespace Pdir\SocialFeedBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use Pdir\SocialFeedBundle\Importer\InstagramClient;
use Pdir\SocialFeedBundle\Model\SocialFeedServiceModel;
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
            $this->requestInstagramAccessToken($dc->activeRecord->appId, (string) $dc->activeRecord->loginType, (string) $dc->activeRecord->configId);
        }

        if ('Facebook' === $dc->activeRecord->type && $dc->activeRecord->appId && $dc->activeRecord->appSecret) {
            $this->requestFacebookAccessToken($dc->activeRecord->appId, $dc->activeRecord->appSecret, $dc->activeRecord->apiVersion ?: 'v20.0');
        }

        if ('LinkedIn' === $dc->activeRecord->type && $dc->activeRecord->clientId && $dc->activeRecord->clientSecret) {
            $this->requestLinkedinAccessToken($dc->activeRecord->clientId, $dc->activeRecord->clientSecret);
        }
    }

    /**
     * Hide the access token expiry date for the unified login, if the token never expires (system user access token).
     */
    #[AsCallback(table: 'tl_sf_service', target: 'config.onload')]
    public function adjustInstagramPalette(?DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id || 'edit' !== Input::get('act')) {
            return;
        }

        $service = SocialFeedServiceModel::findByPk($dc->id);

        if (
            null === $service
            || 'Instagram' !== $service->type
            || !InstagramClient::isUnifiedLogin($service->loginType)
            || '' !== (string) $service->tokenExpires
        ) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_sf_service']['subpalettes']['type_Instagram'] = str_replace(
            ',tokenExpires;',
            ';',
            $GLOBALS['TL_DCA']['tl_sf_service']['subpalettes']['type_Instagram']
        );
    }

    /**
     * Request the Instagram access token (legacy Instagram login or unified login).
     */
    private function requestInstagramAccessToken(string $clientId, string $loginType, string $configId): void
    {
        $state = bin2hex(random_bytes(16));

        $this->session->set(self::SESSION_KEY, [
            'sfServiceId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
            'state' => $state,
        ]);

        $this->session->save();

        $redirectUri = $this->router->generate('instagram_auth', [], RouterInterface::ABSOLUTE_URL);

        // Unified login: Facebook Login for Business, permissions are defined by the configuration (config_id)
        if (InstagramClient::isUnifiedLogin($loginType)) {
            $data = [
                'client_id' => $clientId,
                'config_id' => $configId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'override_default_response_type' => 'true',
                'state' => $state,
            ];

            throw new RedirectResponseException('https://www.facebook.com/'.InstagramClient::GRAPH_VERSION.'/dialog/oauth?'.http_build_query($data));
        }

        // Legacy: API setup with Instagram login
        $data = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'instagram_business_basic',
            'state' => $state,
        ];

        throw new RedirectResponseException('https://www.instagram.com/oauth/authorize/?'.http_build_query($data));
    }

    /**
     * Request the Facebook access token.
     */
    private function requestFacebookAccessToken(string $appId, string $appSecret, string $apiVersion): void
    {
        $this->session->set(self::SESSION_KEY, [
            'sfServiceId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
            'clientId' => $appId,
            'clientSecret' => $appSecret,
            'apiVersion' => $apiVersion,
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
