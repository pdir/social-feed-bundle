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

namespace Pdir\SocialFeedBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Dbafs;
use Contao\File;
use Contao\NewsModel;
use Contao\System;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Pdir\SocialFeedBundle\Importer\NewsImporter;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Psr\Log\LogLevel;

#[AsCronJob('minutely')]
class FacebookImportCron
{
    use ImportCronHelperTrait;

    public int $counter = 0;
    private ?object $logger;

    public function __construct(private ContaoFramework $framework)
    {
    }

    /**
     * @throws FacebookSDKException
     * @throws \Exception
     */
    public function __invoke(): void
    {
        $this->framework->initialize();
        $this->logger = System::getContainer()->get('monolog.logger.contao');

        $objSocialFeed = SocialFeedAccountModel::findForImport('Facebook', $this->poorManCron);

        if (null === $objSocialFeed) {
            return;
        }

        $this->counter = 0;

        /** @var SocialFeedAccountModel $account */
        foreach ($objSocialFeed as $account) {
            $service = $account->getService();
            $accessToken = $account->getAccessToken();

            // Skip accounts without service or access token
            if (null === $service) {
                $this->logger->log(LogLevel::ERROR, 'Social Feed (ID '.$account->id.'): Facebook account has no service.', ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
                continue;
            }

            if ('' === $accessToken) {
                $this->logger->log(LogLevel::ERROR, 'Social Feed (ID '.$account->id.'): Facebook account has no access token.', ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
                continue;
            }

            // Get Facebook Feed
            $cron = (int) $account->cronjob;
            $lastImport = (int) $account->lastImport;
            $interval = time() - $lastImport;

            if ($interval < $cron && 0 !== $lastImport && false !== $this->poorManCron) {
                continue;
            }

            NewsImporter::setLastImportDate($account);

            $accountId = (int) $account->id;
            $page = $account->identifier ?: $account->name;

            $fb = new Facebook([
                'app_id' => $service->appId,
                'app_secret' => $service->appSecret,
                'default_graph_version' => $service->apiVersion ?: 'v25.0',
                'default_access_token' => $accessToken,
            ]);

            $fields = 'fields=id,from,created_time,message,permalink_url';
            $response = '1' === (string) $account->importPostsOnly
                ? $this->graphGet($fb, $page.'/posts?'.$fields, $accountId)
                : $this->graphGet($fb, $page.'/feed?'.$fields, $accountId);

            // An API error only skips this account, the other accounts are still imported
            if (null === $response) {
                continue;
            }

            $imgPath = NewsImporter::createImageFolder($account->id);

            // get account picture and save it
            $pageData = $this->graphGet($fb, $page.'?fields=picture', $accountId)?->getDecodedBody() ?? [];
            $pageId = $pageData['id'] ?? null;
            $pictureUrl = $pageData['picture']['data']['url'] ?? null;
            $accountImg = null !== $pageId ? $imgPath.$pageId.'.jpg' : null;

            if (null !== $accountImg && null !== $pictureUrl && false !== ($strImage = @file_get_contents($pictureUrl))) {
                $file = new File($accountImg);
                $file->write($strImage);
                $file->close();
            }

            $objFileAccount = null !== $accountImg && NewsImporter::fileExists($accountImg) ? Dbafs::addResource($accountImg) : null;
            $imported = 0;

            // Write in Database
            foreach ($response->getDecodedBody()['data'] ?? [] as $post) {
                if (null !== NewsModel::findBy('social_feed_id', $post['id'])) {
                    continue;
                }

                if ('' === ($post['from']['name'] ?? '')) {
                    continue;
                }

                $image = $this->getFbAttachments($fb, $post['id'], $imgPath, $accountId);
                $message = $post['message'] ?? '';

                // set variables
                if ('' === $message) {
                    $title = $GLOBALS['TL_LANG']['MSC']['pdirSocialFeedNoTitel'] ?? '';
                } elseif (false !== ($pos = strpos($message, "\n")) && $pos > 0) {
                    $title = mb_substr($message, 0, $pos);
                } else {
                    $title = $message;
                }

                $timestamp = strtotime($post['created_time']);

                // create new news
                $objNews = new NewsModel();
                $objNews->pid = $account->newsArchive;
                $objNews->author = $account->author;

                if (null !== $image && NewsImporter::fileExists($image['path'])) {
                    $objNews->singleSRC = Dbafs::addResource($image['path'])->uuid;
                    $objNews->addImage = 1;
                }

                $objNews->tstamp = time();
                $objNews->headline = mb_substr($title, 0, 255);
                $objNews->teaser = '' === $message && '' !== ($image['title'] ?? '') ? $image['title'] : str_replace("\n", '<br>', $message);
                $objNews->date = $timestamp;
                $objNews->time = $timestamp;
                $objNews->published = 1;
                $objNews->social_feed_type = $account->type;
                $objNews->social_feed_id = $post['id'];
                $objNews->social_feed_config = $account->id;
                $objNews->social_feed_account = $post['from']['name'];
                $objNews->social_feed_account_picture = $objFileAccount?->uuid;
                $objNews->source = 'external';
                $objNews->url = $post['permalink_url'] ?? '';
                $objNews->target = 1;
                $objNews->save();

                ++$imported;
            }

            $this->counter += $imported;

            if (0 < $imported) {
                $this->logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): Facebook - imported '.$imported.' items.', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
            } else {
                $this->logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): Facebook Import - nothing to import', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
            }
        }
    }

    /**
     * Requests the Graph API. Errors are logged and return null instead of aborting the whole import (previously exit).
     */
    private function graphGet(Facebook $fb, string $endpoint, int $accountId): ?FacebookResponse
    {
        try {
            return $fb->get($endpoint);
        } catch (FacebookResponseException $e) {
            $this->logger->log(LogLevel::ERROR, sprintf('Social Feed (ID %s): Facebook Graph returned an error for "%s": %s', $accountId, strtok($endpoint, '?'), $e->getMessage()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
        } catch (FacebookSDKException $e) {
            $this->logger->log(LogLevel::ERROR, sprintf('Social Feed (ID %s): Facebook SDK returned an error for "%s": %s', $accountId, strtok($endpoint, '?'), $e->getMessage()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
        }

        return null;
    }

    /**
     * Downloads the first image of a post.
     *
     * @return array{path: string, title: string}|null
     */
    private function getFbAttachments(Facebook $fb, string $id, string $imgPath, int $accountId): ?array
    {
        $data = $this->graphGet($fb, '/'.$id.'/attachments', $accountId)?->getDecodedBody()['data'][0] ?? null;

        if (null === $data) {
            return null;
        }

        // use the first sub attachment of albums
        $media = isset($data['subattachments']['data'][0]['media']) ? $data['subattachments']['data'][0] : $data;
        $src = $media['media']['image']['src'] ?? null;

        if (null === $src) {
            return null;
        }

        $path = $imgPath.$id.'.jpg';

        if (!NewsImporter::fileExists($path)) {
            $strImage = @file_get_contents($src);

            if (false === $strImage || '' === $strImage) {
                return null;
            }

            $file = new File($path);
            $file->write($strImage);
            $file->close();
        }

        return [
            'path' => $path,
            'title' => (string) ($media['title'] ?? ''),
        ];
    }
}
