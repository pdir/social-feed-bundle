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

namespace Pdir\SocialFeedBundle\Importer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\File;
use Contao\FilesModel;
use Contao\Message;
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
     * Get the data from Instagram.
     */
    public function getData(string $url, array $query = [], int $socialFeedId = null, bool $cache = true): ?array
    {
        if (!$cache) {
            $this->cache->purge($this->cache->getCacheDir($socialFeedId));
        }

        try {
            $response = $this->getCachedClient($socialFeedId)->get($url, ['query' => $query]);
        } catch (ClientException | ServerException $e) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf('Unable to fetch Instagram data from "%s": %s', $url, $e->getMessage()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
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

        $data = $json_data['data'];

        if ($query['limit'] > 100 && isset($json_data['paging']['next'])) {
            $query['limit'] -= 100;
            $json_data['paging']['next'] = str_replace('limit=100', 'limit='.$query['limit'], $json_data['paging']['next']);
            parse_str(parse_url($json_data['paging']['next'], PHP_URL_QUERY), $query);
            $next_page = $this->getData($url, $query, $socialFeedId, $cache);
            $data = array_merge($data, $next_page['data']);
        }

        return ['data' => $data];
    }

    /**
     * Get the media data.
     */
    public function getMediaData(string $accessToken, int $socialFeedId = null, int $numberPosts, bool $cache = true): ?array
    {
        return $this->getData('https://graph.instagram.com/me/media', [
            'access_token' => $accessToken,
            'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,children{media_url}',
            'limit' => $numberPosts,
        ], $socialFeedId, $cache);
    }

    /**
     * Get the user data.
     */
    public function getUserData(string $accessToken, int $socialFeedId = null, bool $cache = true): ?array
    {
        return $this->getData('https://graph.instagram.com/me', [
            'access_token' => $accessToken,
            'fields' => 'id,username',
        ], $socialFeedId, $cache);
    }

    /**
     * Get the user image.
     */
    public function getUserImage(string $accessToken, int $socialFeedId = null, bool $cache = true): ?array
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
    public function getTags(string $accessToken, int $userId = null, bool $cache = true): ?array
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
                $this->logger->error(sprintf('Unable to fetch the Instagram short-lived access token: %s', $e->getMessage()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
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
                $this->logger->error(sprintf('Unable to fetch the Instagram long-lived access token: %s', $e->getMessage()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
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
    private function getCachedClient(int $moduleId = null): Client
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
