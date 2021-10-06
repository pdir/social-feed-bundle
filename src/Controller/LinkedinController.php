<?php

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
use LinkedIn\Client;

/**
 * @Route("/auth", defaults={"_scope" = "backend", "_token_check" = false})
 */
class LinkedinController
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
     * LinkedinController constructor.
     */
    public function __construct(Connection $db, RouterInterface $router, SessionInterface $session)
    {
        $this->db = $db;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @Route("/linkedin", name="auth_linkedin", methods={"GET"})
     */
    public function authAction(Request $request): Response
    {
        $sessionData = $this->session->get(SocialFeedListener::SESSION_KEY);

        // Missing code query parameter
        if (!($code = $request->query->get('code'))) {
            return new Response(Response::$statusTexts[Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        } else {
            $client = new Client(
                $sessionData['clientId'],
                $sessionData['clientSecret']
            );

            $client->setRedirectUrl($this->router->generate('auth_linkedin', [], RouterInterface::ABSOLUTE_URL));
            $accessToken = $client->getAccessToken($request->query->get('code'));

            // Store the access token and remove temporary session key
            $this->db->update('tl_social_feed', ['linkedin_access_token' => $accessToken, 'linkedin_access_token_date' => time()], ['id' => $sessionData['socialFeedId']]);
            $this->session->remove(SocialFeedListener::SESSION_KEY);
        }

        return new RedirectResponse($sessionData['backUrl']);
    }
}
