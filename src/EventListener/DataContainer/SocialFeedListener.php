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
use Symfony\Component\Security\Core\Security;

class SocialFeedListener
{
    public const SESSION_KEY = 'social-feed-id';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    private Security $security;
    private ImageSizes $imageSizes;

    /**
     * ModuleListener constructor.
     */
    public function __construct(RouterInterface $router, Security $security, ImageSizes $imageSizes)
    {
        $this->router = $router;
        $this->session = System::getContainer()->get('request_stack')->getCurrentRequest()->getSession();
        $this->security = $security;
        $this->imageSizes = $imageSizes;
    }

    /**
     * On submit callback.
     */
    public function onSubmitCallback(DataContainer $dc): void
    {
        if ('Instagram' === $dc->activeRecord->socialFeedType && $dc->activeRecord->psf_instagramAppId && Input::post('psf_instagramRequestToken')) {
            $this->requestAccessToken($dc->activeRecord->psf_instagramAppId);
        }

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

    /**
     * @Callback(table="tl_social_feed", target="fields.linkedin_account_picture_size.options")
     * @Callback(table="tl_social_feed", target="fields.instagram_account_picture_size.options")
     */
    public function getImageSizeOptions(): array
    {
        $user = $this->security->getUser();

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

    /**
     * Request the Instagram access token.
     *
     * @param string $clientId
     */
    private function requestAccessToken($clientId): void
    {
        $this->session->set(self::SESSION_KEY, [
            'socialFeedId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
        ]);

        $this->session->save();

        $data = [
            'app_id' => $clientId,
            'redirect_uri' => $this->router->generate('instagram_auth', [], RouterInterface::ABSOLUTE_URL),
            'response_type' => 'code',
            'scope' => 'instagram_business_basic',
        ];

        throw new RedirectResponseException('https://api.instagram.com/oauth/authorize/?'.http_build_query($data));
    }

    /**
     * Request the Facebook access token.
     *
     * @param string $appId
     */
    private function requestFbAccessToken($appId, $appSecret, $page): void
    {
        $this->session->set(self::SESSION_KEY, [
            'socialFeedId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
            'clientId' => $appId,
            'clientSecret' => $appSecret,
            'page' => $page,
        ]);

        $this->session->save();

        $data = [
            'client_id' => $appId,
            'redirect_uri' => $this->router->generate('facebook_auth', [], RouterInterface::ABSOLUTE_URL),
            'scope' => 'pages_read_engagement',
        ];

        throw new RedirectResponseException('https://www.facebook.com/v11.0/dialog/oauth?'.http_build_query($data));
    }

    /**
     * Request the LinkedIn access token.
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    private function requestLinkedinAccessToken($clientId, $clientSecret): void
    {
        $this->session->set(self::SESSION_KEY, [
            'socialFeedId' => Input::get('id'),
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
