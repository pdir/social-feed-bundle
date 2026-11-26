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

namespace Pdir\SocialFeedBundle\Importer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\File;
use Contao\FilesModel;
use Contao\StringUtil;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class InstagramClient
{
    /**
     * Legacy login: "API setup with Instagram login" (graph.instagram.com).
     */
    public const LOGIN_INSTAGRAM = 'instagram_login';

    /**
     * Unified login: Facebook Login for Business with config_id (graph.facebook.com).
     */
    public const LOGIN_UNIFIED = 'unified_login';

    /**
     * Graph API version used for the unified login flow.
     */
    public const GRAPH_VERSION = 'v26.0';

    public const GRAPH_FACEBOOK_URL = 'https://graph.facebook.com/'.self::GRAPH_VERSION;

    /**
     * @var InstagramRequestCache
     */
    private $cache;

    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * InstagramClient constructor.
     */
    public function __construct(InstagramRequestCache $cache, ContaoFramework $framework, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->framework = $framework;
        $this->logger = $logger;
    }

    /**
     * Check if the given login type uses the unified login (Facebook Login for Business).
     */
    public static function isUnifiedLogin(?string $loginType): bool
    {
        return self::LOGIN_UNIFIED === $loginType;
    }

    /**
     * Get the data from Instagram.
     */
    public function getData(string $url, array $query = [], ?int $socialFeedId = null, bool $cache = true): ?array
    {
        if (!$cache) {
            $this->cache->purge($this->cache->getCacheDir($socialFeedId));
        }

        try {
            $response = $this->getCachedClient($socialFeedId)->get($url, ['query' => $query]);
        } catch (ClientException | ServerException $e) {
            if (null !== $this->logger) {
                // Log the response body instead of the exception message, which contains the access token
                $this->logger->error(sprintf('Social Feed (ID %s): Unable to fetch Instagram data from "%s": %s', $socialFeedId ?? '-', $url, (string) $e->getResponse()->getBody()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }

            return null;
        }

        $json_data = json_decode((string) $response->getBody(), true);

        if (!\is_array($json_data) || JSON_ERROR_NONE !== json_last_error()) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf('Unable to decode Instagram data from "%s": %s', $url, json_last_error_msg()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }

            return null;
        }

        // Node requests (e.g. /me or /{ig-user-id}) do not return a "data" key
        $data = $json_data['data'] ?? $json_data;

        // Instagram returns at most 25 (or 100) items per page, so follow the paging until the requested number of
        // items is reached (#131). Each recursive call fetches the next page with the remaining limit.
        if (isset($query['limit'], $json_data['data'], $json_data['paging']['next']) && \is_array($data)) {
            $remaining = (int) $query['limit'] - \count($data);

            if ($remaining > 0 && \count($data) > 0) {
                parse_str((string) parse_url($json_data['paging']['next'], PHP_URL_QUERY), $nextQuery);

                if (!empty($nextQuery['after'])) {
                    $nextPage = $this->getData($url, array_merge($query, ['after' => $nextQuery['after'], 'limit' => $remaining]), $socialFeedId, $cache);
                    $data = array_merge($data, $nextPage['data'] ?? []);
                }
            }
        }

        return ['data' => $data];
    }

    /**
     * Get the media data.
     */
    public function getMediaData(string $accessToken, ?int $socialFeedId, int $numberPosts, bool $cache = true, ?string $instagramUserId = null): ?array
    {
        // Unified login: the token belongs to a (system) user, so the Instagram account must be addressed by its ID
        $url = null !== $instagramUserId && '' !== $instagramUserId
            ? self::GRAPH_FACEBOOK_URL.'/'.rawurlencode($instagramUserId).'/media'
            : 'https://graph.instagram.com/me/media';

        return $this->getData($url, [
            'access_token' => $accessToken,
            'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,children{media_url},is_shared_to_feed',
            'limit' => $numberPosts,
        ], $socialFeedId, $cache);
    }

    /**
     * Get the user data.
     */
    public function getUserData(string $accessToken, ?int $socialFeedId = null, bool $cache = true, ?string $instagramUserId = null): ?array
    {
        $url = null !== $instagramUserId && '' !== $instagramUserId
            ? self::GRAPH_FACEBOOK_URL.'/'.rawurlencode($instagramUserId)
            : 'https://graph.instagram.com/me';

        return $this->getData($url, [
            'access_token' => $accessToken,
            'fields' => 'id,username',
        ], $socialFeedId, $cache);
    }

    /**
     * Get the user image.
     */
    public function getUserImage(string $accessToken, ?int $socialFeedId = null, bool $cache = true): ?array
    {
        // not supported by instagram
        return $this->getData('https://graph.instagram.com/me/picture', [
            'access_token' => $accessToken,
        ], $socialFeedId, $cache);
    }

    /**
     * Store the media files locally.
     *
     * @throws \RuntimeException
     */
    public function storeMediaFiles(string $targetUuid, array $data): array
    {
        $this->framework->initialize();

        if (null === ($folderModel = FilesModel::findByPk($targetUuid)) || !is_dir(TL_ROOT.'/'.$folderModel->path)) {
            throw new \RuntimeException('The target folder does not exist');
        }

        // Support raw responses as well
        if (isset($data['data'])) {
            $data = $data['data'];
        }

        foreach ($data as &$item) {
            if ('IMAGE' !== $item['media_type']) {
                continue;
            }

            $extension = pathinfo(explode('?', $item['media_url'])[0], PATHINFO_EXTENSION);
            $file = new File(sprintf('%s/%s.%s', $folderModel->path, $item['id'], $extension));

            // Download the image
            if (!$file->exists()) {
                try {
                    $response = $this->getClient()->get($item['media_url']);
                } catch (ClientException | ServerException $e) {
                    if (null !== $this->logger) {
                        $this->logger->error(sprintf('Unable to fetch Instagram image from "%s": %s', $item['media_url'], $e->getMessage()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
                    }

                    continue;
                }

                // Save the image and add sync the database
                $file->write($response->getBody());
                $file->close();
            }

            // Store the UUID in cache
            if ($file->exists()) {
                $item['contao']['uuid'] = StringUtil::binToUuid($file->getModel()->uuid);
            }
        }

        return $data;
    }

    /**
     * Get the tags (mentions).
     */
    public function getTags(string $accessToken, ?int $userId = null, bool $cache = true): ?array
    {
        return $this->getData('https://graph.instagram.com/'.$userId.'/tags', [
            'access_token' => $accessToken,
            'fields' => 'id,username',
        ], $userId, $cache);
    }

    /**
     * Get the access token.
     */
    public function getAccessToken(string $clientId, string $clientSecret, string $code, string $redirectUri): ?array
    {
        if (($token = $this->getShortLivedAccessToken($clientId, $clientSecret, $code, $redirectUri)) === null) {
            return null;
        }

        return $this->getLongLivedAccessToken($token, $clientSecret);
    }

    /**
     * Exchange the authorization code of the unified login (Facebook Login for Business) for an access token.
     *
     * Returns the access token and its expiry timestamp (0 = never expires, e.g. system user access tokens).
     *
     * @return array{access_token: string, expires_at: int}|null
     */
    public function getUnifiedAccessToken(string $clientId, string $clientSecret, string $code, string $redirectUri): ?array
    {
        $data = $this->requestGraph(self::GRAPH_FACEBOOK_URL.'/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ], 'access token (unified login)');

        if (null === $data || empty($data['access_token'])) {
            return null;
        }

        $expiresAt = isset($data['expires_in']) ? time() + (int) $data['expires_in'] : 0;

        // The debug endpoint returns the real expiry; 0 means the token never expires (system user access token)
        $debug = $this->requestGraph(self::GRAPH_FACEBOOK_URL.'/debug_token', [
            'input_token' => $data['access_token'],
            'access_token' => $clientId.'|'.$clientSecret,
        ], 'token debug info (unified login)');

        if (isset($debug['data']['expires_at'])) {
            $expiresAt = (int) $debug['data']['expires_at'];
        }

        return [
            'access_token' => (string) $data['access_token'],
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Resolve the Instagram professional accounts that were shared via the unified login.
     *
     * @return list<array{id: string, username: string}>
     */
    public function getUnifiedInstagramAccounts(string $accessToken): array
    {
        // Instagram accounts assigned as business asset (system user access token)
        $data = $this->requestGraph(self::GRAPH_FACEBOOK_URL.'/me/assigned_instagram_accounts', [
            'access_token' => $accessToken,
            'fields' => 'id,username',
        ], 'assigned Instagram accounts (unified login)');

        $accounts = [];

        foreach ($data['data'] ?? [] as $account) {
            if (!empty($account['id'])) {
                $accounts[$account['id']] = ['id' => (string) $account['id'], 'username' => (string) ($account['username'] ?? '')];
            }
        }

        // Fallback: Instagram accounts linked to Facebook pages (user access token)
        if ([] === $accounts) {
            $data = $this->requestGraph(self::GRAPH_FACEBOOK_URL.'/me/accounts', [
                'access_token' => $accessToken,
                'fields' => 'instagram_business_account{id,username}',
            ], 'Facebook pages (unified login)');

            foreach ($data['data'] ?? [] as $page) {
                if (!empty($page['instagram_business_account']['id'])) {
                    $id = (string) $page['instagram_business_account']['id'];
                    $accounts[$id] = ['id' => $id, 'username' => (string) ($page['instagram_business_account']['username'] ?? '')];
                }
            }
        }

        return array_values($accounts);
    }

    /**
     * Get the short-lived access token.
     *
     * @return string
     */
    private function getShortLivedAccessToken(string $clientId, string $clientSecret, string $code, string $redirectUri): ?string
    {
        try {
            $response = $this->getClient()->post('https://api.instagram.com/oauth/access_token', [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ],
            ]);
        } catch (ClientException | ServerException $e) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf('Unable to fetch the Instagram short-lived access token: %s', (string) $e->getResponse()->getBody()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }

            return null;
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!\is_array($data) || JSON_ERROR_NONE !== json_last_error()) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf('Unable to fetch the Instagram short-lived access token: %s', json_last_error_msg()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }

            return null;
        }

        return $data['access_token'];
    }

    /**
     * Get the long-lived access token.
     */
    private function getLongLivedAccessToken(string $token, string $clientSecret): ?array
    {
        try {
            $response = $this->getClient()->get('https://graph.instagram.com/access_token', [
                'query' => [
                    'grant_type' => 'ig_exchange_token',
                    'client_secret' => $clientSecret,
                    'access_token' => $token,
                ],
            ]);
        } catch (ClientException | ServerException $e) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf('Unable to fetch the Instagram long-lived access token: %s', (string) $e->getResponse()->getBody()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }

            return null;
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!\is_array($data) || JSON_ERROR_NONE !== json_last_error()) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf('Unable to fetch the Instagram long-lived access token: %s', json_last_error_msg()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }

            return null;
        }

        return $data;
    }

    /**
     * Send an uncached GET request to the Graph API and return the decoded response.
     *
     * @param array<string, string> $query
     *
     * @return array<string, mixed>|null
     */
    private function requestGraph(string $url, array $query, string $context): ?array
    {
        try {
            $response = $this->getClient()->get($url, ['query' => $query]);
        } catch (ClientException|ServerException $e) {
            if (null !== $this->logger) {
                // Log the response body instead of the exception message, which contains the access token
                $this->logger->error(
                    sprintf('Unable to fetch the Instagram %s: %s', $context, (string) $e->getResponse()->getBody()),
                    ['contao' => new ContaoContext(__METHOD__, 'ERROR')]
                );
            }

            return null;
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!\is_array($data)) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf('Unable to decode the Instagram %s: %s', $context, json_last_error_msg()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }

            return null;
        }

        return $data;
    }

    /**
     * Get the client.
     */
    private function getClient(): Client
    {
        static $client;

        if (null === $client) {
            $client = new Client();
        }

        return $client;
    }

    /**
     * Get the cache client.
     */
    private function getCachedClient(?int $moduleId = null): Client
    {
        static $clients = [];

        $key = $moduleId ?? '_';

        if (!isset($clients[$key])) {
            $stack = HandlerStack::create();

            $cachePool = new FilesystemAdapter('', 0, $this->cache->getCacheDir($moduleId));
            $cache = DoctrineProvider::wrap($cachePool);

            $stack->push(
                new CacheMiddleware(
                    new GreedyCacheStrategy(
                        new DoctrineCacheStorage($cache),
                        $this->cache->getCacheTtl()
                    )
                ),
                'cache'
            );

            $clients[$key] = (new Client(['handler' => $stack]));
        }

        return $clients[$key];
    }
}
