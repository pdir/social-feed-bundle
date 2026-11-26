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

namespace Pdir\SocialFeedBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * Migrates the old tl_social_feed records into tl_sf_service (credentials) and tl_sf_account (import settings).
 *
 * The accounts keep the id of the old record if possible, so tl_news.social_feed_config and the image folders
 * stay valid. If the id is already used by another account, a new id is assigned and the news are reassigned.
 * The old table is not deleted, so it can be removed manually after checking the result.
 */
class SocialFeedServiceMigration extends AbstractMigration
{
    private const REQUIRED_COLUMNS = [
        'tl_sf_service' => ['type', 'name', 'appid', 'appsecret', 'accesstoken', 'accesstokensecret', 'apikey', 'apikeysecret', 'clientid', 'clientsecret', 'apiversion', 'tokenexpires', 'refreshtoken', 'refreshtokenexpires', 'noteforrefreshtokenmail', 'logintype', 'configid'],
        'tl_sf_account' => ['type', 'service', 'name', 'identifier', 'accesstoken', 'newsarchive', 'author', 'numberposts', 'cronjob', 'lastimport', 'importpostsonly', 'importmentions', 'importsharedcontent', 'search', 'showretweets', 'showreplies', 'hashtagslink', 'avatar', 'avatarsize', 'added', 'legacyid'],
    ];

    public function __construct(private Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'Social Feed: migrate tl_social_feed to tl_sf_service and tl_sf_account';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_social_feed', 'tl_sf_service', 'tl_sf_account'])) {
            return false;
        }

        // Wait until the database schema of the new tables has been updated
        foreach (self::REQUIRED_COLUMNS as $table => $required) {
            // Use the column names instead of the array keys, their format differs between DBAL 3 and 4
            $columns = array_map(
                static fn ($column): string => strtolower($column->getName()),
                array_values($schemaManager->listTableColumns($table)),
            );

            if ([] !== array_diff($required, $columns)) {
                return false;
            }
        }

        return [] !== $this->getRecordsToMigrate();
    }

    public function run(): MigrationResult
    {
        $records = $this->getRecordsToMigrate();
        $count = 0;
        $reassigned = 0;

        foreach ($records as $row) {
            $type = $this->mapType((string) ($row['socialFeedType'] ?? ''));

            if ('' === $type) {
                continue;
            }

            $time = time();
            $identifier = $this->getIdentifier($type, $row);
            $name = $this->getAccountName($type, $row) ?: $type.' (ID '.$row['id'].')';

            $this->connection->insert('tl_sf_service', array_merge(
                [
                    'tstamp' => $time,
                    'type' => $type,
                    'name' => $name,
                ],
                $this->getServiceData($type, $row),
            ));

            $serviceId = (int) $this->connection->lastInsertId();

            $avatar = $row['instagram_account_picture'] ?? null ?: ($row['linkedin_account_picture'] ?? null);
            $avatarSize = $row['instagram_account_picture_size'] ?? '' ?: ($row['linkedin_account_picture_size'] ?? '');
            $cronjob = (string) ($row['pdir_sf_fb_news_cronjob'] ?? '');

            $oldId = (int) $row['id'];
            $idIsFree = false === $this->connection->fetchOne('SELECT id FROM tl_sf_account WHERE id = ?', [$oldId]);

            $accountData = [
                'legacyId' => $oldId,
                'tstamp' => $time,
                'added' => (int) ($row['tstamp'] ?? 0) ?: $time,
                'type' => $type,
                'service' => $serviceId,
                'name' => $name,
                'identifier' => $identifier,
                // Facebook: the page access token belongs to the account
                'accessToken' => 'Facebook' === $type ? (string) ($row['pdir_sf_fb_access_token'] ?? '') : '',
                'newsArchive' => (int) ($row['pdir_sf_fb_news_archive'] ?? 0),
                'author' => (int) ($row['user'] ?? 0),
                'numberPosts' => (int) ($row['number_posts'] ?? 0) ?: 20,
                'cronjob' => '' !== $cronjob ? $cronjob : 'no_cronjob',
                'lastImport' => (string) ($row['pdir_sf_fb_news_last_import_date'] ?? ''),
                'importPostsOnly' => (string) ($row['pdir_sf_fb_posts'] ?? ''),
                'importMentions' => (string) ($row['psf_instagramImportMentions'] ?? ''),
                'importSharedContent' => (string) ($row['linkedinImportSharedContent'] ?? ''),
                'search' => (string) ($row['search'] ?? ''),
                'showRetweets' => (string) ($row['show_retweets'] ?? ''),
                'showReplies' => (string) ($row['show_reply'] ?? ''),
                'hashtagsLink' => (string) ($row['hashtags_link'] ?? ''),
                'avatar' => $avatar ?: null,
                'avatarSize' => (string) $avatarSize,
            ];

            // Keep the old ID if possible. Otherwise use an ID above all old and new IDs, so the new account does not
            // take the ID of a record that is migrated later (which would then need a new ID, too).
            $accountData['id'] = $idIsFree ? $oldId : $this->getNextFreeId();

            $this->connection->insert('tl_sf_account', $accountData);

            if (!$idIsFree) {
                $newId = $accountData['id'];
                $types = 'X' === $type ? ['Twitter', 'X'] : [$type];

                // Reassign the news of the old record, the image folder files/social-feed/<old id> is kept
                $this->connection->executeStatement(
                    'UPDATE tl_news SET social_feed_config = ? WHERE social_feed_config = ? AND social_feed_type IN ('.implode(',', array_fill(0, \count($types), '?')).')',
                    [$newId, $oldId, ...$types],
                );
                ++$reassigned;
            }

            ++$count;
        }

        return $this->createResult(true, sprintf('Social Feed: migrated %d of %d accounts from tl_social_feed (%d with new id).', $count, \count($records), $reassigned));
    }

    /**
     * Returns the old records which have not been migrated yet.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRecordsToMigrate(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_social_feed WHERE id NOT IN (SELECT legacyId FROM tl_sf_account WHERE legacyId > 0) ORDER BY id'
        );
    }

    private function getNextFreeId(): int
    {
        return 1 + max(
            (int) $this->connection->fetchOne('SELECT MAX(id) FROM tl_sf_account'),
            (int) $this->connection->fetchOne('SELECT MAX(id) FROM tl_social_feed'),
        );
    }

    private function mapType(string $type): string
    {
        return match ($type) {
            'Facebook', 'Instagram', 'LinkedIn' => $type,
            'Twitter', 'X' => 'X',
            default => '',
        };
    }

    private function getIdentifier(string $type, array $row): string
    {
        return (string) match ($type) {
            'Facebook' => $row['pdir_sf_fb_account'] ?? '',
            // Instagram user ID (unified login, version 2.15+), the legacy login addresses the account via /me
            'Instagram' => $row['psf_instagramUserId'] ?? '',
            'X' => $row['twitter_account'] ?? '',
            'LinkedIn' => $row['linkedin_company_id'] ?? '',
            default => '',
        };
    }

    /**
     * @param array<string, mixed> $row
     */
    private function getAccountName(string $type, array $row): string
    {
        return (string) match ($type) {
            'Instagram' => $row['instagram_account'] ?? '',
            'X' => $row['twitter_account'] ?? '' ?: ($row['search'] ?? ''),
            default => $this->getIdentifier($type, $row),
        };
    }

    private function getServiceData(string $type, array $row): array
    {
        return match ($type) {
            'Facebook' => [
                'appId' => (string) ($row['pdir_sf_fb_app_id'] ?? ''),
                'appSecret' => (string) ($row['pdir_sf_fb_app_secret'] ?? ''),
                'accessToken' => (string) ($row['pdir_sf_fb_access_token'] ?? ''),
                'apiVersion' => 'v25.0',
            ],
            'Instagram' => [
                'appId' => (string) ($row['psf_instagramAppId'] ?? ''),
                'appSecret' => (string) ($row['psf_instagramAppSecret'] ?? ''),
                'accessToken' => (string) ($row['psf_instagramAccessToken'] ?? ''),
                'tokenExpires' => (string) ($row['access_token_expires'] ?? ''),
                'loginType' => (string) ($row['psf_instagramLoginType'] ?? '' ?: 'instagram_login'),
                'configId' => (string) ($row['psf_instagramConfigId'] ?? ''),
            ],
            'X' => [
                'apiKey' => (string) ($row['twitter_api_key'] ?? ''),
                'apiKeySecret' => (string) ($row['twitter_api_secret_key'] ?? ''),
                'accessToken' => (string) ($row['twitter_access_token'] ?? ''),
                'accessTokenSecret' => (string) ($row['twitter_access_token_secret'] ?? ''),
            ],
            'LinkedIn' => [
                'clientId' => (string) ($row['linkedin_client_id'] ?? ''),
                'clientSecret' => (string) ($row['linkedin_client_secret'] ?? ''),
                'accessToken' => (string) ($row['linkedin_access_token'] ?? ''),
                'tokenExpires' => (string) ($row['access_token_expires'] ?? ''),
                'refreshToken' => (string) ($row['linkedin_refresh_token'] ?? ''),
                'refreshTokenExpires' => (string) ($row['linkedin_refresh_token_expires'] ?? ''),
                'noteForRefreshTokenMail' => (string) ($row['noteForRefreshTokenMail'] ?? ''),
            ],
            default => [],
        };
    }
}
