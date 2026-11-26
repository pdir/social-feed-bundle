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

namespace Pdir\SocialFeedBundle\Tests\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Pdir\SocialFeedBundle\Migration\SocialFeedServiceMigration;
use PHPUnit\Framework\TestCase;

/**
 * Runs the migration against an in-memory SQLite database (DBAL 3 and 4).
 *
 * The schema of tl_sf_service and tl_sf_account is read from the DCA files, so the test fails if the migration
 * writes a column which does not exist in the DCA.
 *
 * @requires extension pdo_sqlite
 */
class SocialFeedServiceMigrationTest extends TestCase
{
    /**
     * Columns of the old table tl_social_feed (version 2.x), the DCA file has been removed.
     */
    private const LEGACY_COLUMNS = [
        'tstamp' => 'int(10) NOT NULL default 0',
        'socialFeedType' => "varchar(255) NOT NULL default ''",
        'pdir_sf_fb_account' => "varchar(255) NOT NULL default ''",
        'pdir_sf_fb_app_id' => "varchar(255) NOT NULL default ''",
        'pdir_sf_fb_app_secret' => "varchar(255) NOT NULL default ''",
        'pdir_sf_fb_access_token' => 'text NULL',
        'pdir_sf_fb_news_archive' => 'int(10) NOT NULL default 0',
        'pdir_sf_fb_news_cronjob' => "varchar(64) NOT NULL default ''",
        'pdir_sf_fb_news_last_import_date' => "varchar(10) NOT NULL default ''",
        'pdir_sf_fb_posts' => "char(1) NOT NULL default ''",
        'user' => 'int(10) NOT NULL default 0',
        'number_posts' => 'int(10) NOT NULL default 0',
        'instagram_account' => "varchar(255) NOT NULL default ''",
        'psf_instagramAppId' => "varchar(255) NOT NULL default ''",
        'psf_instagramAppSecret' => "varchar(255) NOT NULL default ''",
        'psf_instagramAccessToken' => 'text NULL',
        'psf_instagramUserId' => "varchar(64) NOT NULL default ''",
        'psf_instagramLoginType' => "varchar(32) NOT NULL default ''",
        'psf_instagramConfigId' => "varchar(64) NOT NULL default ''",
        'psf_instagramImportMentions' => "char(1) NOT NULL default ''",
        'access_token_expires' => "varchar(10) NOT NULL default ''",
        'twitter_account' => "varchar(255) NOT NULL default ''",
        'twitter_api_key' => "varchar(255) NOT NULL default ''",
        'twitter_api_secret_key' => "varchar(255) NOT NULL default ''",
        'twitter_access_token' => "varchar(255) NOT NULL default ''",
        'twitter_access_token_secret' => "varchar(255) NOT NULL default ''",
        'search' => "varchar(255) NOT NULL default ''",
        'show_retweets' => "char(1) NOT NULL default ''",
        'show_reply' => "char(1) NOT NULL default ''",
        'hashtags_link' => "char(1) NOT NULL default ''",
        'linkedin_company_id' => "varchar(255) NOT NULL default ''",
        'linkedin_client_id' => "varchar(255) NOT NULL default ''",
        'linkedin_client_secret' => "varchar(255) NOT NULL default ''",
        'linkedin_access_token' => 'text NULL',
        'linkedin_refresh_token' => 'text NULL',
        'linkedin_refresh_token_expires' => "varchar(10) NOT NULL default ''",
        'linkedinImportSharedContent' => "char(1) NOT NULL default ''",
        'noteForRefreshTokenMail' => 'text NULL',
    ];

    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);

        $this->createTable('tl_sf_service', $this->getDcaColumns('tl_sf_service'));
        $this->createTable('tl_sf_account', $this->getDcaColumns('tl_sf_account'));
        $this->createTable('tl_social_feed', self::LEGACY_COLUMNS);
        $this->createTable('tl_news', [
            'social_feed_config' => 'int(10) NOT NULL default 0',
            'social_feed_type' => "varchar(32) NOT NULL default ''",
        ]);
    }

    public function testDoesNotRunWithoutLegacyTable(): void
    {
        $this->connection->executeStatement('DROP TABLE tl_social_feed');

        $this->assertFalse($this->createMigration()->shouldRun());
    }

    public function testDoesNotRunBeforeTheSchemaUpdate(): void
    {
        $this->insertLegacy(['id' => 1, 'socialFeedType' => 'Facebook']);
        $this->connection->executeStatement('ALTER TABLE tl_sf_account DROP COLUMN legacyId');

        $this->assertFalse($this->createMigration()->shouldRun());
    }

    public function testDoesNotRunWithoutRecords(): void
    {
        $this->assertFalse($this->createMigration()->shouldRun());
    }

    public function testMigratesAllTypes(): void
    {
        $this->insertLegacy([
            'id' => 1,
            'socialFeedType' => 'Facebook',
            'pdir_sf_fb_account' => 'pdirpage',
            'pdir_sf_fb_app_id' => 'fb-app',
            'pdir_sf_fb_app_secret' => 'fb-secret',
            'pdir_sf_fb_access_token' => 'page-token',
            'pdir_sf_fb_news_archive' => 5,
            'pdir_sf_fb_news_cronjob' => '3600',
            'number_posts' => 12,
            'user' => 7,
        ]);
        $this->insertLegacy([
            'id' => 2,
            'socialFeedType' => 'Instagram',
            'instagram_account' => 'pdir',
            'psf_instagramAppId' => 'ig-app',
            'psf_instagramAccessToken' => 'ig-token',
            'psf_instagramUserId' => '1789',
            'psf_instagramLoginType' => 'unified_login',
            'psf_instagramConfigId' => 'cfg-1',
            'access_token_expires' => '1900000000',
        ]);
        $this->insertLegacy(['id' => 3, 'socialFeedType' => 'Twitter', 'search' => 'contao', 'twitter_api_key' => 'key']);
        $this->insertLegacy(['id' => 4, 'socialFeedType' => 'LinkedIn', 'linkedin_company_id' => '42', 'linkedin_client_id' => 'li-client']);

        $migration = $this->createMigration();

        $this->assertTrue($migration->shouldRun());
        $this->assertTrue($migration->run()->isSuccessful());
        $this->assertFalse($migration->shouldRun(), 'The migration must not run twice');

        $accounts = $this->fetchById('tl_sf_account');
        $services = $this->fetchById('tl_sf_service');

        $this->assertCount(4, $accounts);
        $this->assertCount(4, $services);

        // Facebook: page token at the account, app credentials at the service
        $this->assertSame('Facebook', $accounts[1]['type']);
        $this->assertSame('pdirpage', $accounts[1]['identifier']);
        $this->assertSame('page-token', $accounts[1]['accessToken']);
        $this->assertSame(5, (int) $accounts[1]['newsArchive']);
        $this->assertSame(7, (int) $accounts[1]['author']);
        $this->assertSame(12, (int) $accounts[1]['numberPosts']);
        $this->assertSame('3600', $accounts[1]['cronjob']);
        $this->assertSame(1, (int) $accounts[1]['legacyId']);
        $service = $services[(int) $accounts[1]['service']];
        $this->assertSame('fb-app', $service['appId']);
        $this->assertSame('fb-secret', $service['appSecret']);

        // Instagram: unified login settings at the service, user ID as identifier
        $this->assertSame('1789', $accounts[2]['identifier']);
        $this->assertSame('pdir', $accounts[2]['name']);
        $this->assertSame('', (string) $accounts[2]['accessToken']);
        $this->assertSame('no_cronjob', $accounts[2]['cronjob']);
        $this->assertSame(20, (int) $accounts[2]['numberPosts']);
        $service = $services[(int) $accounts[2]['service']];
        $this->assertSame('ig-token', $service['accessToken']);
        $this->assertSame('unified_login', $service['loginType']);
        $this->assertSame('cfg-1', $service['configId']);
        $this->assertSame('1900000000', (string) $service['tokenExpires']);

        // Twitter is renamed to X, the search term is used as name
        $this->assertSame('X', $accounts[3]['type']);
        $this->assertSame('contao', $accounts[3]['name']);
        $this->assertSame('key', $services[(int) $accounts[3]['service']]['apiKey']);

        $this->assertSame('42', $accounts[4]['identifier']);
        $this->assertSame('li-client', $services[(int) $accounts[4]['service']]['clientId']);
    }

    public function testDefaultsToInstagramLogin(): void
    {
        $this->insertLegacy(['id' => 1, 'socialFeedType' => 'Instagram', 'instagram_account' => 'pdir']);

        $this->createMigration()->run();

        $this->assertSame('instagram_login', $this->connection->fetchOne('SELECT loginType FROM tl_sf_service'));
    }

    public function testSkipsUnknownTypes(): void
    {
        $this->insertLegacy(['id' => 1, 'socialFeedType' => '']);
        $this->insertLegacy(['id' => 2, 'socialFeedType' => 'Facebook']);

        $this->createMigration()->run();

        $this->assertSame([2], array_map('intval', $this->connection->fetchFirstColumn('SELECT id FROM tl_sf_account')));
    }

    public function testReassignsNewsIfTheIdIsTaken(): void
    {
        // Account 2 already exists in the new table (e.g. created by Social Feed+)
        $this->connection->insert('tl_sf_account', ['id' => 2, 'type' => 'LinkedIn', 'name' => 'existing']);

        $this->insertLegacy(['id' => 2, 'socialFeedType' => 'Twitter', 'twitter_account' => 'pdir']);
        $this->insertLegacy(['id' => 3, 'socialFeedType' => 'Facebook', 'pdir_sf_fb_account' => 'pdirpage']);

        $this->connection->insert('tl_news', ['social_feed_config' => 2, 'social_feed_type' => 'Twitter']);
        $this->connection->insert('tl_news', ['social_feed_config' => 2, 'social_feed_type' => 'LinkedIn']);
        $this->connection->insert('tl_news', ['social_feed_config' => 3, 'social_feed_type' => 'Facebook']);

        $result = $this->createMigration()->run();

        $this->assertStringContainsString('(1 with new id)', $result->getMessage());

        $accounts = $this->fetchById('tl_sf_account');

        // The existing account is untouched
        $this->assertSame('existing', $accounts[2]['name']);

        // Facebook keeps its ID, the new ID of the X account must not take it
        $this->assertSame('Facebook', $accounts[3]['type']);
        $this->assertSame('X', $accounts[4]['type']);
        $this->assertSame(2, (int) $accounts[4]['legacyId']);

        $news = $this->connection->fetchAllKeyValue('SELECT social_feed_type, social_feed_config FROM tl_news');

        $this->assertSame(4, (int) $news['Twitter']);
        $this->assertSame(2, (int) $news['LinkedIn']);
        $this->assertSame(3, (int) $news['Facebook']);
    }

    private function createMigration(): SocialFeedServiceMigration
    {
        return new SocialFeedServiceMigration($this->connection);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertLegacy(array $row): void
    {
        $this->connection->insert('tl_social_feed', $row);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchById(string $table): array
    {
        $rows = [];

        foreach ($this->connection->fetchAllAssociative("SELECT * FROM $table ORDER BY id") as $row) {
            $rows[(int) $row['id']] = $row;
        }

        return $rows;
    }

    /**
     * @param array<string, string> $columns
     */
    private function createTable(string $table, array $columns): void
    {
        $definitions = ['id INTEGER PRIMARY KEY AUTOINCREMENT'];

        foreach ($columns as $name => $sql) {
            if ('id' === $name) {
                continue;
            }

            // MySQL specific definitions which SQLite (resp. the DBAL schema manager) does not understand
            $sql = preg_replace(['/\b(unsigned|auto_increment)\b/i', '/\b(var)?binary\(\d+\)/i'], ['', 'blob'], $sql);
            $definitions[] = $name.' '.$sql;
        }

        $this->connection->executeStatement("CREATE TABLE $table (".implode(', ', $definitions).')');
    }

    /**
     * Reads the "sql" definitions of the fields from a DCA file.
     *
     * @return array<string, string>
     */
    private function getDcaColumns(string $table): array
    {
        $columns = [];
        $field = null;

        foreach (file(__DIR__.'/../../contao/dca/'.$table.'.php') ?: [] as $line) {
            // field definitions are indented with 8 spaces: 'fieldName' => [
            if (preg_match("/^\\s{8}'(\\w+)'\\s*=>\\s*\\[/", $line, $matches)) {
                $field = $matches[1];
            }

            if (null !== $field && preg_match("/'sql'\\s*=>\\s*(['\"])(.*)\\1,?\\s*$/", $line, $matches)) {
                $columns[$field] = $matches[2];
                $field = null;
            }
        }

        $this->assertGreaterThan(5, \count($columns), "No sql definitions found in the DCA of $table");

        return $columns;
    }
}
