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

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Dbafs;
use Contao\File;
use Contao\FilesModel;
use Contao\NewsModel;
use Contao\System;
use LinkedIn\Client;
use LinkedIn\Exception;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;
use Psr\Log\LogLevel;

class LinkedIn
{
    public int $counter = 0;
    public bool $poorManCron = false;
    private int $maxPosts = 100;
    private bool $debug = false;
    private bool $ignoreInterval = false;

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function import(): bool
    {
        $logger = System::getContainer()->get('monolog.logger.contao');

        if (!$this->poorManCron) {
            $objSocialFeed = SocialFeedModel::findBy('socialFeedType', 'LinkedIn');
        } else {
            $objSocialFeed = SocialFeedModel::findBy(
                ['socialFeedType = ?', 'pdir_sf_fb_news_cronjob != ?'],
                ['LinkedIn', 'no_cronjob']
            );
        }

        if ($this->debug) {
            dump($objSocialFeed);
        }

        if (null === $objSocialFeed) {
            if ($this->debug) {
                dump('--- no LinkedIn social feed account available!');
            }

            return false;
        }

        $this->counter = 0;

        foreach ($objSocialFeed as $account) {
            $this->counter = 0;
            $cron = $account->pdir_sf_fb_news_cronjob;
            $lastImport = $account->pdir_sf_fb_news_last_import_date;

            if ($this->poorManCron) {
                $this->maxPosts = $account->number_posts;
            }

            if ('' === $lastImport) {
                $lastImport = 0;
            }
            $interval = time() - $lastImport;

            if (($interval >= $cron && 'no_cronjob' !== $cron) || (0 === $lastImport && 'no_cronjob' !== $cron) || true === $this->ignoreInterval) {
                NewsImporter::setLastImportDate($account);

                $client = new Client(
                    $account->linkedin_client_id,
                    $account->linkedin_client_secret
                );

                $client->setApiHeaders([
                    'Content-Type' => 'application/json',
                    'X-Restli-Protocol-Version' => '2.0.0', // use protocol v2
                ]);

                $client->setAccessToken($account->linkedin_access_token);

                /*
                if ($this->debug) {
                    #dump('-- List organizationalEntityAcls');
                    #$profile = $client->get('organizationalEntityAcls',['q' => 'roleAssignee']);
                    #dump($profile);
                }

                if ($this->debug) {
                    dump('-- List organizations');
                    #$companyInfo = $client->get('organizations/'.$account->linkedin_company_id);
                    dump($companyInfo);
                }
                */

                // $client->setApiRoot('https://api.linkedin.com/rest/');
                $client->setApiHeaders([
                    'Content-Type' => 'application/json',
                    'X-Restli-Protocol-Version' => '2.0.0', // use protocol v2,
                    'LinkedIn-Version' => '202306',
                ]);

                // get posts
                $posts = $client->get(
                    'ugcPosts?q=authors&authors=List(urn%3Ali%3Aorganization%3A70570732)&sortBy=LAST_MODIFIED&count='.$this->maxPosts
                );

                if (!\is_array($posts['elements'])) {
                    continue;
                }

                foreach ($posts['elements'] as $element) {
                    $objFile = null;

                    if ($this->debug) {
                        dump('-- LinkedIn API: element data');
                        dump($element);
                    }

                    // @todo import shared content only if wanted by user $account->linkedinImportSharedContent

                    // continue if news exists
                    if (null !== NewsModel::findBy('social_feed_id', $element['id'])) {
                        if ($this->debug) {
                            dump('ignore existing post '.$element['id']);
                        }
                        continue;
                    }

                    $item = [];

                    // get post image
                    $media = $element['specificContent']['com.linkedin.ugc.ShareContent']['media'];

                    if (!empty($media) && \is_array($media)) {
                        $imgPath = NewsImporter::createImageFolder($account->linkedin_company_id);
                        $picturePath = $imgPath.str_replace('urn:li:share:', '', $element['id']).'.jpg';

                        // use originalUrl of media for image download
                        $firstImage = $media[0]['originalUrl'] ?? null;

                        // use first thumbnail for articles
                        if (isset($media[0]) && str_contains($media[0]['media'], 'urn:li:article:') && isset($media[0]['thumbnails'][0])) {
                            $firstImage = $media[0]['thumbnails'][0]['url'] ?? null;
                        }

                        // get first image
                        if (!file_exists($picturePath) && isset($firstImage)) {
                            // Write to filesystem
                            $file = new File($picturePath);
                            $file->write(file_get_contents($firstImage));
                            $file->close();

                            // Add the resource
                            $objFile = Dbafs::addResource($picturePath);
                        }

                        // get files model for existing image
                        if (file_exists($picturePath) && null === $objFile) {
                            $objFile = FilesModel::findByPath($picturePath);
                        }
                    }

                    $item['id'] = $element['id'];
                    $item['headline'] = NewsImporter::shortenHeadline($element['specificContent']['com.linkedin.ugc.ShareContent']['shareCommentary']['text'] ?? '');
                    $item['teaser'] = str_replace("\n", '<br>', $element['specificContent']['com.linkedin.ugc.ShareContent']['shareCommentary']['text'] ?? '');
                    $item['singleSRC'] = null !== $objFile ? $objFile->uuid : '';
                    $item['date'] = $element['firstPublishedAt'] / 1000;
                    $item['time'] = $element['firstPublishedAt'] / 1000;
                    $item['permalink'] = 'https://www.linkedin.com/feed/update/'.$item['id'].'/';

                    // @todo get organization and set account picture
                    // $item['social_feed_account'] = $organization?? $organization['localizedName'];

                    if ($this->debug) {
                        dump('-- Post data for import');
                        dump($item);
                    }

                    // write to db
                    $importer = new NewsImporter();
                    $importer->setNews($item);
                    $importer->execute($account->pdir_sf_fb_news_archive, $account);

                    ++$this->counter;
                }

                if (0 < $this->counter) {
                    $logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): LinkedIn - imported '.$this->counter.' items.', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
                }
            }

            if (0 === $this->counter) {
                $logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): LinkedIn Import - nothing to import', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
            }
        }

        return true;
    }

    public function setPoorManCronMode($flag): void
    {
        if (true === $flag) {
            $this->poorManCron = true;
        }
    }

    public function setDebugMode($flag): void
    {
        if (true === $flag) {
            $this->debug = true;
        }
    }

    public function setIgnoreInterval($flag): void
    {
        if (true === $flag) {
            $this->ignoreInterval = true;
        }
    }

    public function setMaxPosts($maxPosts): void
    {
        $this->maxPosts = $maxPosts;
    }
}
