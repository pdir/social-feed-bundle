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

namespace Pdir\SocialFeedBundle\SocialFeed;

use Contao\CoreBundle\Monolog\ContaoContext;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Pdir\SocialFeedBundle\Importer\InstagramClient;
use Pdir\SocialFeedBundle\Importer\NewsImporter;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Pdir\SocialFeedBundle\Model\SocialFeedServiceModel;
use Psr\Log\LoggerInterface;

/**
 * Lists the accounts (Facebook pages, Instagram accounts, LinkedIn organizations) a connected service has access to
 * and adds them as social feed accounts.
 *
 * @phpstan-type DiscoveredAccount array{identifier: string, name: string, picture: string|null, accessToken: string|null}
 */
class AccountDiscovery
{
    private const FACEBOOK_GRAPH_URL = 'https://graph.facebook.com/';

    private const LINKEDIN_ACLS_URL = 'https://api.linkedin.com/v2/organizationAcls';

    private ClientInterface $httpClient;

    public function __construct(private readonly InstagramClient $instagramClient, private readonly LoggerInterface|null $logger = null, ClientInterface|null $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new Client(['timeout' => 15]);
    }

    /**
     * Whether the accounts of the service type can be listed via the API (X: the account is a user name or search).
     */
    public function supportsDiscovery(string $type): bool
    {
        return \in_array($type, ['Facebook', 'Instagram', 'LinkedIn'], true);
    }

    /**
     * Whether the service has an access token (the login was done in the service settings).
     */
    public function isConnected(SocialFeedServiceModel $service): bool
    {
        return '' !== trim((string) $service->accessToken);
    }

    /**
     * @throws AccountDiscoveryException
     *
     * @return list<DiscoveredAccount>
     */
    public function discover(SocialFeedServiceModel $service): array
    {
        if (!$this->isConnected($service)) {
            throw new AccountDiscoveryException('The service has no access token.');
        }

        return match ((string) $service->type) {
            'Facebook' => $this->discoverFacebookPages($service),
            'Instagram' => $this->discoverInstagramAccounts($service),
            'LinkedIn' => $this->discoverLinkedInOrganizations($service),
            default => throw new AccountDiscoveryException(sprintf('The accounts of the service type "%s" cannot be listed.', $service->type)),
        };
    }

    /**
     * Creates or updates the social feed account of the discovered account.
     *
     * @param DiscoveredAccount $item
     */
    public function addAccount(SocialFeedServiceModel $service, array $item): SocialFeedAccountModel
    {
        $type = (string) $service->type;
        $account = SocialFeedAccountModel::findOneBy(['type = ?', 'identifier = ?'], [$type, $item['identifier']]);

        // Migrated Instagram accounts have no identifier yet, so assign the account of the service without identifier
        if ('Instagram' === $type) {
            $account ??= SocialFeedAccountModel::findOneBy(['type = ?', 'service = ?', "identifier = ''"], [$type, (int) $service->id]);
        }

        if (null === $account) {
            $account = new SocialFeedAccountModel();
            $account->type = $type;
            $account->added = time();
            $account->cronjob = 'no_cronjob';
        }

        $account->identifier = $item['identifier'];
        $account->name = $item['name'] ?: ($account->name ?: $item['identifier']);
        $account->service = (int) $service->id;
        $account->tstamp = time();

        // Facebook pages have their own (page) access token, the other types use the token of the service
        if (null !== $item['accessToken']) {
            $account->accessToken = $item['accessToken'];
        }

        $account->save();

        if (empty($account->avatar) && null !== $item['picture']) {
            $account->avatar = NewsImporter::saveImage(NewsImporter::createImageFolder($account->id).'avatar.jpg', $item['picture']);
            $account->save();
        }

        return $account;
    }

    /**
     * @return list<DiscoveredAccount>
     */
    private function discoverFacebookPages(SocialFeedServiceModel $service): array
    {
        $data = $this->requestJson(
            self::FACEBOOK_GRAPH_URL.((string) $service->apiVersion ?: 'v25.0').'/me/accounts',
            ['query' => ['access_token' => (string) $service->accessToken, 'fields' => 'id,name,access_token', 'limit' => 100]],
            'Facebook pages',
        );

        $accounts = [];

        foreach ($data['data'] ?? [] as $page) {
            if (empty($page['id'])) {
                continue;
            }

            $accounts[] = [
                'identifier' => (string) $page['id'],
                'name' => (string) ($page['name'] ?? ''),
                'picture' => self::FACEBOOK_GRAPH_URL.rawurlencode((string) $page['id']).'/picture?type=large',
                'accessToken' => isset($page['access_token']) ? (string) $page['access_token'] : null,
            ];
        }

        return $accounts;
    }

    /**
     * @return list<DiscoveredAccount>
     */
    private function discoverInstagramAccounts(SocialFeedServiceModel $service): array
    {
        $accessToken = (string) $service->accessToken;

        // Unified login: all Instagram accounts shared during the login
        if (InstagramClient::isUnifiedLogin((string) $service->loginType)) {
            return array_map(
                static fn (array $account): array => ['identifier' => $account['id'], 'name' => $account['username'], 'picture' => null, 'accessToken' => null],
                $this->instagramClient->getUnifiedInstagramAccounts($accessToken),
            );
        }

        // Instagram login: the token belongs to exactly one account
        $userData = $this->instagramClient->getUserData($accessToken, (int) $service->id, false);

        if (empty($userData['data']['id'])) {
            throw new AccountDiscoveryException('The Instagram account could not be fetched.');
        }

        return [[
            'identifier' => (string) $userData['data']['id'],
            'name' => (string) ($userData['data']['username'] ?? ''),
            'picture' => null,
            'accessToken' => null,
        ]];
    }

    /**
     * Organizations the user administers (scope rw_organization_admin).
     *
     * @return list<DiscoveredAccount>
     */
    private function discoverLinkedInOrganizations(SocialFeedServiceModel $service): array
    {
        $data = $this->requestJson(
            self::LINKEDIN_ACLS_URL,
            [
                'query' => [
                    'q' => 'roleAssignee',
                    'role' => 'ADMINISTRATOR',
                    'state' => 'APPROVED',
                    'projection' => '(elements*(organization~(id,localizedName)))',
                ],
                'headers' => [
                    'Authorization' => 'Bearer '.$service->accessToken,
                    'X-Restli-Protocol-Version' => '2.0.0',
                ],
            ],
            'LinkedIn organizations',
        );

        $accounts = [];

        foreach ($data['elements'] ?? [] as $element) {
            // urn:li:organization:123456
            $id = (string) ($element['organization~']['id'] ?? preg_replace('/^urn:li:organization:/', '', (string) ($element['organization'] ?? '')));

            if ('' === $id || isset($accounts[$id])) {
                continue;
            }

            $accounts[$id] = [
                'identifier' => $id,
                'name' => (string) ($element['organization~']['localizedName'] ?? ''),
                'picture' => null,
                'accessToken' => null,
            ];
        }

        return array_values($accounts);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws AccountDiscoveryException
     *
     * @return array<string, mixed>
     */
    private function requestJson(string $url, array $options, string $context): array
    {
        try {
            $response = $this->httpClient->request('GET', $url, $options);
        } catch (GuzzleException $e) {
            // Log the response body instead of the exception message, which may contain the access token
            $body = $e instanceof RequestException && null !== $e->getResponse() ? (string) $e->getResponse()->getBody() : $e::class;

            $this->logger?->error(sprintf('Social Feed: Unable to fetch the %s: %s', $context, $body), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);

            throw new AccountDiscoveryException(sprintf('The %s could not be fetched.', $context), 0, $e);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!\is_array($data)) {
            throw new AccountDiscoveryException(sprintf('The %s could not be decoded.', $context));
        }

        return $data;
    }
}
