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

namespace Pdir\SocialFeedBundle\Tests\SocialFeed;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Pdir\SocialFeedBundle\Importer\InstagramClient;
use Pdir\SocialFeedBundle\Model\SocialFeedServiceModel;
use Pdir\SocialFeedBundle\SocialFeed\AccountDiscovery;
use Pdir\SocialFeedBundle\SocialFeed\AccountDiscoveryException;
use PHPUnit\Framework\TestCase;

class AccountDiscoveryTest extends TestCase
{
    /**
     * @var list<array{request: \Psr\Http\Message\RequestInterface}>
     */
    private array $history = [];

    public function testListsTheFacebookPages(): void
    {
        $discovery = $this->createDiscovery(new Response(200, [], json_encode([
            'data' => [
                ['id' => '111', 'name' => 'Page A', 'access_token' => 'page-token'],
                ['name' => 'without id'],
            ],
        ])));

        $accounts = $discovery->discover($this->createService('Facebook', 'user-token', ['apiVersion' => 'v24.0']));

        $this->assertSame([[
            'identifier' => '111',
            'name' => 'Page A',
            'picture' => 'https://graph.facebook.com/111/picture?type=large',
            'accessToken' => 'page-token',
        ]], $accounts);

        $uri = $this->history[0]['request']->getUri();
        $this->assertSame('/v24.0/me/accounts', $uri->getPath());
        $this->assertStringContainsString('access_token=user-token', $uri->getQuery());
    }

    public function testListsTheLinkedInOrganizations(): void
    {
        $discovery = $this->createDiscovery(new Response(200, [], json_encode([
            'elements' => [
                ['organization' => 'urn:li:organization:42', 'organization~' => ['id' => 42, 'localizedName' => 'pdir GmbH']],
                ['organization' => 'urn:li:organization:43'],
                ['organization' => 'urn:li:organization:42'],
            ],
        ])));

        $accounts = $discovery->discover($this->createService('LinkedIn', 'li-token'));

        $this->assertSame(['42', '43'], array_column($accounts, 'identifier'));
        $this->assertSame(['pdir GmbH', ''], array_column($accounts, 'name'));
        $this->assertSame('Bearer li-token', $this->history[0]['request']->getHeaderLine('Authorization'));
    }

    public function testThrowsOnApiErrors(): void
    {
        $discovery = $this->createDiscovery(new Response(400, [], '{"error":{"message":"Invalid OAuth access token"}}'));

        $this->expectException(AccountDiscoveryException::class);

        $discovery->discover($this->createService('Facebook', 'expired'));
    }

    public function testThrowsWithoutAccessToken(): void
    {
        $discovery = $this->createDiscovery();

        $this->assertFalse($discovery->isConnected($this->createService('Facebook', '')));

        $this->expectException(AccountDiscoveryException::class);

        $discovery->discover($this->createService('Facebook', ''));
    }

    public function testListsTheSharedInstagramAccountsOfTheUnifiedLogin(): void
    {
        $instagramClient = $this->createMock(InstagramClient::class);
        $instagramClient
            ->expects($this->once())
            ->method('getUnifiedInstagramAccounts')
            ->with('ig-token')
            ->willReturn([['id' => '17841', 'username' => 'pdir']])
        ;

        $discovery = new AccountDiscovery($instagramClient, null, new Client());
        $accounts = $discovery->discover($this->createService('Instagram', 'ig-token', ['loginType' => 'unified_login']));

        $this->assertSame([['identifier' => '17841', 'name' => 'pdir', 'picture' => null, 'accessToken' => null]], $accounts);
    }

    public function testSupportsDiscovery(): void
    {
        $discovery = $this->createDiscovery();

        $this->assertTrue($discovery->supportsDiscovery('Facebook'));
        $this->assertTrue($discovery->supportsDiscovery('Instagram'));
        $this->assertTrue($discovery->supportsDiscovery('LinkedIn'));
        $this->assertFalse($discovery->supportsDiscovery('X'));
    }

    private function createDiscovery(Response ...$responses): AccountDiscovery
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new AccountDiscovery($this->createMock(InstagramClient::class), null, new Client(['handler' => $stack]));
    }

    /**
     * @param array<string, string> $data
     */
    private function createService(string $type, string $accessToken, array $data = []): SocialFeedServiceModel
    {
        $service = $this->getMockBuilder(SocialFeedServiceModel::class)->disableOriginalConstructor()->onlyMethods(['__get'])->getMock();
        $values = ['id' => 1, 'type' => $type, 'accessToken' => $accessToken] + $data;
        $service->method('__get')->willReturnCallback(static fn (string $key) => $values[$key] ?? null);

        return $service;
    }
}
