<?php

namespace Pdir\SocialFeedBundle\EventListener;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;


class SocialFeedListener
{
    const SESSION_KEY = 'social-feed-id';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * ModuleListener constructor.
     */
    public function __construct(RouterInterface $router, SessionInterface $session)
    {
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * On submit callback.
     */
    public function onSubmitCallback(DataContainer $dc)
    {
        if ('Instagram' === $dc->activeRecord->socialFeedType && $dc->activeRecord->psf_instagramAppId && Input::post('psf_instagramRequestToken')) {
            $this->requestAccessToken($dc->activeRecord->psf_instagramAppId);
        }

        if ('Facebook' === $dc->activeRecord->socialFeedType && $dc->activeRecord->pdir_sf_fb_app_id && $dc->activeRecord->pdir_sf_fb_app_secret && Input::post('psf_facebookRequestToken')) {
            $this->requestFbAccessToken($dc->activeRecord->pdir_sf_fb_app_id, $dc->activeRecord->pdir_sf_fb_app_secret, $dc->activeRecord->pdir_sf_fb_account);
        }
    }

    /**
     * On the request token save.
     *
     * @return null
     */
    public function onRequestTokenSave()
    {
        return null;
    }

    /**
     * Request the Instagram access token.
     *
     * @param string $clientId
     */
    private function requestAccessToken($clientId)
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
            'scope' => 'user_profile,user_media',
        ];

        throw new RedirectResponseException('https://api.instagram.com/oauth/authorize/?'.http_build_query($data));
    }

    /**
     * Request the Facebook access token.
     *
     * @param string $clientId
     */
    private function requestFbAccessToken($appId, $appSecret, $page)
    {
        $this->session->set(self::SESSION_KEY, [
            'socialFeedId' => Input::get('id'),
            'backUrl' => Environment::get('uri'),
            'clientId' => $appId,
            'clientSecret' => $appSecret,
            'page' => $page
        ]);

        $this->session->save();

        $data = [
            'client_id' => $appId,
            'redirect_uri' => $this->router->generate('facebook_auth', [], RouterInterface::ABSOLUTE_URL),
            'scope' => 'pages_read_engagement'
        ];

        throw new RedirectResponseException('https://www.facebook.com/v11.0/dialog/oauth?'.http_build_query($data));
    }
}
