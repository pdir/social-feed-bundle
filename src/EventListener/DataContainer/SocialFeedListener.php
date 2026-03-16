<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2025 pdir / digital agentur // pdir GmbH
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

use Contao\BackendUser;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Image\ImageSizes;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use Pdir\SocialFeedBundle\EventListener\Config;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SocialFeedListener
{
    public const SESSION_KEY = 'social-feed-id';

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * ModuleListener constructor.
     */
    public function __construct(
        private readonly RouterInterface $router,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ImageSizes $imageSizes
    )
    {
        $this->session = System::getContainer()->get('request_stack')->getCurrentRequest()->getSession();
    }

    /**
     * On submit callback.
     */
    public function onSubmitCallback(DataContainer $dc): void
    {
        if ('Facebook' === $dc->activeRecord->socialFeedType && $dc->activeRecord->pdir_sf_fb_app_id && $dc->activeRecord->pdir_sf_fb_app_secret && Input::post('psf_facebookRequestToken')) {
            $this->requestFbAccessToken($dc->activeRecord->pdir_sf_fb_app_id, $dc->activeRecord->pdir_sf_fb_app_secret, $dc->activeRecord->pdir_sf_fb_account);
        }

        if ('LinkedIn' === $dc->activeRecord->socialFeedType && $dc->activeRecord->linkedin_client_id && $dc->activeRecord->linkedin_client_secret && Input::post('linkedin_request_token')) {
            $this->requestLinkedinAccessToken($dc->activeRecord->linkedin_client_id, $dc->activeRecord->linkedin_client_secret);
        }
    }

    /**
     * On the request token save.
     */
    public function onRequestTokenSave()
    {
        return null;
    }

    public function instagramAuthWizard(DataContainer $dc): string
    {
        return $this->renderAuthWizard(
            $dc,
            'Instagram',
            $GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramRequestToken'][0] ?? 'Request token',
            [
                'psf_instagramAppId',
                'psf_instagramAppSecret',
            ],
            fn ($r) => [
                'client_id' => (string) $r->psf_instagramAppId,
                'redirect_uri' => $this->router->generate('instagram_auth', [], RouterInterface::ABSOLUTE_URL),
                'response_type' => 'code',
                'scope' => 'instagram_business_basic',
            ],
            fn ($r) => 'https://www.instagram.com/oauth/authorize/?',
            fn ($r) => [
                'socialFeedId' => Input::get('id'),
                'backUrl' => Environment::get('uri'),
            ]
        );
    }

    public function facebookAuthWizard(DataContainer $dc): string
    {
        return $this->renderAuthWizard(
            $dc,
            'Facebook',
            $GLOBALS['TL_LANG']['tl_social_feed']['psf_facebookRequestToken'][0] ?? 'Request token',
            [
                'pdir_sf_fb_app_id',
                'pdir_sf_fb_app_secret',
                'pdir_sf_fb_account',
            ],
            fn ($r) => [
                'client_id' => (string) $r->pdir_sf_fb_app_id,
                'redirect_uri' => $this->router->generate('facebook_auth', [], RouterInterface::ABSOLUTE_URL),
                'scope' => 'pages_read_engagement,pages_read_user_content',
            ],
            fn ($r) => 'https://www.facebook.com/v11.0/dialog/oauth?',
            fn ($r) => [
                'socialFeedId' => Input::get('id'),
                'backUrl' => Environment::get('uri'),
                'clientId' => (string) $r->pdir_sf_fb_app_id,
                'clientSecret' => (string) $r->pdir_sf_fb_app_secret,
                'page' => (string) $r->pdir_sf_fb_account,
            ]
        );
    }

    public function linkedinAuthWizard(DataContainer $dc): string
    {
        return $this->renderAuthWizard(
            $dc,
            'LinkedIn',
            $GLOBALS['TL_LANG']['tl_social_feed']['linkedin_request_token'][0] ?? 'Request token',
            [
                'linkedin_client_id',
                'linkedin_client_secret',
                'linkedin_company_id',
            ],
            fn ($r) => [
                'response_type' => 'code',
                'client_id' => (string) $r->linkedin_client_id,
                'redirect_uri' => $this->router->generate('auth_linkedin', [], RouterInterface::ABSOLUTE_URL),
                'scope' => 'r_organization_social,rw_organization_admin',
            ],
            fn ($r) => 'https://www.linkedin.com/oauth/v2/authorization?',
            fn ($r) => [
                'socialFeedId' => Input::get('id'),
                'backUrl' => Environment::get('uri'),
                'clientId' => (string) $r->linkedin_client_id,
                'clientSecret' => (string) $r->linkedin_client_secret,
            ]
        );
    }

    /**
     * @param list<string> $requiredFields
     * @param \Closure(object):array<string,string> $queryBuilder
     * @param \Closure(object):string $baseUrlBuilder
     * @param \Closure(object):array<string,mixed> $sessionBuilder
     */
    private function renderAuthWizard(
        DataContainer $dc,
        string $type,
        string $label,
        array $requiredFields,
        \Closure $queryBuilder,
        \Closure $baseUrlBuilder,
        \Closure $sessionBuilder
    ): string {
        if (!$dc->activeRecord) {
            return '';
        }

        if ($type !== ($dc->activeRecord->socialFeedType ?? null)) {
            return '';
        }

        // Required fields check
        foreach ($requiredFields as $field) {
            $val = (string) ($dc->activeRecord->{$field} ?? '');
            if ('' === trim($val)) {
                return sprintf(
                    ' <a href="#" class="tl_submit disabled" style="margin-top:5px; opacity:.5; pointer-events:none;" aria-disabled="true">%s</a>',
                    htmlspecialchars($label, ENT_QUOTES)
                );
            }
        }

        // Session context (needed for callback)
        $this->session->set(self::SESSION_KEY, $sessionBuilder($dc->activeRecord));
        $this->session->save();

        $baseUrl = $baseUrlBuilder($dc->activeRecord);
        $query = $queryBuilder($dc->activeRecord);

        $url = $baseUrl.http_build_query($query);

        return sprintf(
            ' <a href="%s" class="tl_submit" style="margin-top:5px" target="_blank" rel="noopener noreferrer">%s</a>',
            htmlspecialchars($url, ENT_QUOTES),
            htmlspecialchars($label, ENT_QUOTES)
        );
    }

    /**
     * @Callback(table="tl_social_feed", target="fields.linkedin_account_picture_size.options")
     * @Callback(table="tl_social_feed", target="fields.instagram_account_picture_size.options")
     */
    public function getImageSizeOptions(): array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof BackendUser) {
            return [];
        }

        return $this->imageSizes->getOptionsForUser($user);
    }

    /**
     * Dynamically add flags to the "singleSRC" field.
     *
     * @Callback(table="tl_social_feed", target="fields.linkedin_account_picture.load")
     * @Callback(table="tl_social_feed", target="fields.instagram_account_picture.load")
     */
    public function setSingleSrcFlags(mixed $varValue, DataContainer $dc): mixed
    {
        if ($dc->activeRecord && isset($dc->activeRecord->type)) {
            switch ($dc->activeRecord->type) {
                case 'text':
                case 'hyperlink':
                case 'image':
                case 'accordionSingle':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('validImageTypes');
                    break;

                case 'download':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('allowedDownload');
                    break;
            }
        }

        return $varValue;
    }
}
