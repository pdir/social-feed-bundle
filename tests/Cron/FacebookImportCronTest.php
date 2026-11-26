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

namespace Pdir\SocialFeedBundle\Tests\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Facebook\Http\GraphRawResponse;
use Facebook\HttpClients\FacebookHttpClientInterface;
use Pdir\SocialFeedBundle\Cron\FacebookImportCron;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

/**
 * API errors must be logged and must not end the PHP process (previously exit), so the other accounts are imported.
 */
class FacebookImportCronTest extends TestCase
{
    private const ACCESS_TOKEN = 'secret-page-token';

    public function testReturnsNullAndLogsOnGraphErrors(): void
    {
        $logger = $this->createLogger();
        $cron = $this->createCron($logger);
        $fb = $this->createFacebook(400, '{"error":{"message":"Unsupported get request.","type":"GraphMethodException","code":100}}');

        $this->assertNull($this->call($cron, 'graphGet', $fb, 'pdirpage/feed?fields=id,message', 42));

        $this->assertCount(1, $logger->records);
        $this->assertStringContainsString('Social Feed (ID 42)', $logger->records[0]);
        $this->assertStringContainsString('"pdirpage/feed"', $logger->records[0]);
        $this->assertStringContainsString('Unsupported get request.', $logger->records[0]);
        $this->assertStringNotContainsString(self::ACCESS_TOKEN, $logger->records[0]);
    }

    public function testReturnsTheResponse(): void
    {
        $cron = $this->createCron($this->createLogger());
        $fb = $this->createFacebook(200, '{"data":[{"id":"1_2"}]}');

        $response = $this->call($cron, 'graphGet', $fb, 'pdirpage/feed', 42);

        $this->assertInstanceOf(FacebookResponse::class, $response);
        $this->assertSame('1_2', $response->getDecodedBody()['data'][0]['id']);
    }

    public function testAttachmentErrorsSkipTheImage(): void
    {
        $logger = $this->createLogger();
        $cron = $this->createCron($logger);
        $fb = $this->createFacebook(500, '{"error":{"message":"An unknown error has occurred.","code":1}}');

        $this->assertNull($this->call($cron, 'getFbAttachments', $fb, '1_2', 'files/social-feed/42/', 42));
        $this->assertCount(1, $logger->records);
    }

    public function testPostsWithoutImageAttachment(): void
    {
        $cron = $this->createCron($this->createLogger());
        $fb = $this->createFacebook(200, '{"data":[{"type":"share","title":"Link without image"}]}');

        $this->assertNull($this->call($cron, 'getFbAttachments', $fb, '1_2', 'files/social-feed/42/', 42));
    }

    private function createCron(FacebookImportCronTestLogger $logger): FacebookImportCron
    {
        $cron = new FacebookImportCron($this->createMock(ContaoFramework::class));

        $property = new \ReflectionProperty(FacebookImportCron::class, 'logger');
        $property->setValue($cron, $logger);

        return $cron;
    }

    private function createFacebook(int $status, string $body): Facebook
    {
        // Compatible with the interface of graph-sdk 7 (untyped) and 8 (typed)
        $client = new class($status, $body) implements FacebookHttpClientInterface {
            public function __construct(private int $status, private string $body)
            {
            }

            /**
             * @param array<string, string> $headers
             */
            public function send($url, $method, $body, array $headers, $timeOut): GraphRawResponse
            {
                return new GraphRawResponse(['Content-Type' => 'application/json'], $this->body, $this->status);
            }
        };

        return new Facebook([
            'app_id' => '123',
            'app_secret' => 'app-secret',
            'default_graph_version' => 'v25.0',
            'default_access_token' => self::ACCESS_TOKEN,
            'http_client_handler' => $client,
        ]);
    }

    private function createLogger(): FacebookImportCronTestLogger
    {
        return new FacebookImportCronTestLogger();
    }

    private function call(object $object, string $method, mixed ...$arguments): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);

        return $reflection->invoke($object, ...$arguments);
    }
}

/**
 * Collects the log messages.
 */
class FacebookImportCronTestLogger extends AbstractLogger
{
    /**
     * @var list<string>
     */
    public array $records = [];

    /**
     * @param mixed                $level
     * @param string|\Stringable   $message
     * @param array<string, mixed> $context
     */
    public function log($level, $message, array $context = []): void
    {
        $this->records[] = $level.': '.$message;
    }
}
