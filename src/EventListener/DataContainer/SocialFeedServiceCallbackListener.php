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
     * Renders the "request access token" button next to the access token field.
     *
     * The OAuth flow is started via a link instead of a redirect on submit, which does not work reliably with the
     * AJAX requests of the back end in Contao 5.4+.
     */
    public function accessTokenWizard(DataContainer $dc): string
    {
        $record = $dc->activeRecord;

        if (!$record) {
            return '';
        }

        return match ((string) $record->type) {
            'Instagram' => $this->instagramAuthWizard($record),
            'Facebook' => $this->facebookAuthWizard($record),
            'LinkedIn' => $this->linkedinAuthWizard($record),
            default => '',
        };
    }

    private function instagramAuthWizard(object $record): string
    {
        $state = bin2hex(random_bytes(16));
        $redirectUri = $this->router->generate('instagram_auth', [], RouterInterface::ABSOLUTE_URL);
        $session = [
            'sfServiceId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
            'state' => $state,
        ];

        // Unified login: Facebook Login for Business, permissions are defined by the configuration (config_id)
        if (InstagramClient::isUnifiedLogin((string) $record->loginType)) {
            return $this->renderAuthWizard(
                $record,
                ['appId', 'appSecret', 'configId'],
                'https://www.facebook.com/'.InstagramClient::GRAPH_VERSION.'/dialog/oauth?',
                [
                    'client_id' => (string) $record->appId,
                    'config_id' => (string) $record->configId,
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'override_default_response_type' => 'true',
                    'state' => $state,
                ],
                $session,
            );
        }

        // Legacy: API setup with Instagram login
        return $this->renderAuthWizard(
            $record,
            ['appId', 'appSecret'],
            'https://www.instagram.com/oauth/authorize/?',
            [
                'client_id' => (string) $record->appId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'instagram_business_basic',
                'state' => $state,
            ],
            $session,
        );
    }

    private function facebookAuthWizard(object $record): string
    {
        $apiVersion = (string) $record->apiVersion ?: 'v25.0';

        return $this->renderAuthWizard(
            $record,
            ['appId', 'appSecret'],
            'https://www.facebook.com/'.$apiVersion.'/dialog/oauth?',
            [
                'client_id' => (string) $record->appId,
                'redirect_uri' => $this->router->generate('facebook_auth', [], RouterInterface::ABSOLUTE_URL),
                'scope' => 'pages_show_list,pages_read_engagement,pages_read_user_content',
            ],
            [
                'sfServiceId' => Input::get('id'),
                'backUrl' => Environment::get('uri'),
                'clientId' => (string) $record->appId,
                'clientSecret' => (string) $record->appSecret,
                'apiVersion' => $apiVersion,
            ],
        );
    }

    private function linkedinAuthWizard(object $record): string
    {
        return $this->renderAuthWizard(
            $record,
            ['clientId', 'clientSecret'],
            'https://www.linkedin.com/oauth/v2/authorization?',
            [
                'response_type' => 'code',
                'client_id' => (string) $record->clientId,
                'redirect_uri' => $this->router->generate('auth_linkedin', [], RouterInterface::ABSOLUTE_URL),
                'scope' => 'r_organization_social,rw_organization_admin',
            ],
            [
                'sfServiceId' => Input::get('id'),
                'backUrl' => Environment::get('uri'),
                'clientId' => (string) $record->clientId,
                'clientSecret' => (string) $record->clientSecret,
            ],
        );
    }

    /**
     * @param list<string>          $requiredFields
     * @param array<string, string> $query
     * @param array<string, mixed>  $sessionData
     */
    private function renderAuthWizard(object $record, array $requiredFields, string $baseUrl, array $query, array $sessionData): string
    {
        $label = $GLOBALS['TL_LANG']['tl_sf_service']['requestToken'][0] ?? 'Request access token';

        // The required fields must be saved before the token can be requested
        foreach ($requiredFields as $field) {
            if ('' === trim((string) ($record->{$field} ?? ''))) {
                return sprintf(
                    ' <a href="#" class="tl_submit disabled" style="margin-top:5px; opacity:.5; pointer-events:none;" aria-disabled="true" title="%s">%s</a>',
                    htmlspecialchars($GLOBALS['TL_LANG']['tl_sf_service']['requestToken'][1] ?? '', ENT_QUOTES),
                    htmlspecialchars($label, ENT_QUOTES)
                );
            }
        }

        // Session context for the callback controller
        $this->session->set(self::SESSION_KEY, $sessionData);
        $this->session->save();

        return sprintf(
            ' <a href="%s" class="tl_submit" style="margin-top:5px" target="_blank" rel="noopener noreferrer">%s</a>',
            htmlspecialchars($baseUrl.http_build_query($query), ENT_QUOTES),
            htmlspecialchars($label, ENT_QUOTES)
        );
    }
}
