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

namespace Pdir\SocialFeedBundle\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Dbafs;
use Contao\File;
use Contao\NewsModel;
use Contao\System;
use Pdir\SocialFeedBundle\Importer\Importer;
use Pdir\SocialFeedBundle\Importer\NewsImporter;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;
use Psr\Log\LogLevel;

#[AsCronJob('minutely')]
class InstagramImportCron
{
    use ImportCronHelperTrait;

    public int $counter = 0;
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
        $logger = System::getContainer()->get('monolog.logger.contao');

        if ($this->poorManCron) {
            $objSocialFeed = SocialFeedModel::findBy(['socialFeedType = ?', 'pdir_sf_fb_news_cronjob != ?'], ['Instagram', 'no_cronjob']);
        } else {
            $objSocialFeed = SocialFeedModel::findBy(['socialFeedType = ?', 'pdir_sf_fb_news_cronjob = ?'], ['Instagram', 'no_cronjob']);
        }

        if (null === $objSocialFeed) {
            return;
        }

        foreach ($objSocialFeed as $account) {
            $this->counter = 0;
            $cron = $account->pdir_sf_fb_news_cronjob;
            $lastImport = $account->pdir_sf_fb_news_last_import_date;

            if ('' === $lastImport) {
                $lastImport = 0;
            }
            $interval = \time() - $lastImport;

            if ($interval >= $cron || 0 === $lastImport || false === $this->poorManCron) {
                NewsImporter::setLastImportDate($account);

                $objImporter = new Importer();

                // get instagram picture # not supported
                // $picture = $objImporter->getInstagramAccountImage($account->psf_instagramAccessToken, $account->id);

                // get instagram posts for account
                $medias = $objImporter->getInstagramPosts($account->psf_instagramAccessToken, $account->id, $account->number_posts);

                if (!\is_array($medias)) {
                    continue;
                }

                foreach ($medias as $media) {
                    $objNews = new NewsModel();

                    if (null !== $objNews->findBy('social_feed_id', $media['id'])) {
                        continue;
                    }

                    $imgPath = NewsImporter::createImageFolder($account->id);

                    // save pictures
                    $picturePath = $imgPath.$media['id'].'.jpg';
                    $this->savePostPictures($picturePath, $media);

                    // Write in Database
                    $message = $media['caption']?? '';

                    // add/fetch file from DBAFS
                    $objFile = Dbafs::addResource($imgPath.$media['id'].'.jpg');
                    $this->saveInstagramNews($objNews, $account, $objFile, $message, $media);

                    $this->counter++;
                }

                if (0 < $this->counter) {
                    $logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): Instagram - imported ' . $this->counter . ' items.', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
                }
            }

            if (0 === $this->counter) {
                $logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): Instagram Import - nothing to import', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
            }
        }
    }

    private function saveInstagramNews($objNews, $obj, $objFile, $message, $media, $account = '', $accountPicture = ''): void
    {
        $objNews->pid = $obj->pdir_sf_fb_news_archive;
        $objNews->author = $obj->user;
        $objNews->singleSRC = $objFile->uuid;
        $objNews->addImage = 1;
        $objNews->tstamp = time();

        if ('' === $message) {
            $message = $media['id'];
        }

        $more = '';
        if (null !== $message && \strlen($message) > 50) {
            $more = ' ...';
        }

        $objNews->headline = mb_substr($message, 0, 50).$more;

        $message = str_replace("\n", '<br>', $message);
        $objNews->teaser = $message;
        $objNews->date = strtotime($media['timestamp']);
        $objNews->time = strtotime($media['timestamp']);
        $objNews->published = 1;
        $objNews->social_feed_type = $obj->socialFeedType;
        $objNews->social_feed_id = $media['id'];
        $objNews->social_feed_config = $obj->id;
        //$objNews->social_feed_account = $account;
        //$objNews->social_feed_account_picture = Dbafs::addResource($accountPicture)->uuid;
        $objNews->source = 'external';
        $objNews->url = $media['permalink'];
        $objNews->target = 1;
        $objNews->save();
    }

    /**
     * @throws \Exception
     */
    private function savePostPictures($picturePath, $media): void
    {
        if (!file_exists($picturePath)) {
            $strImage = file_get_contents(false !== strpos($media['media_url'], 'jpg') ? $media['media_url'] : $media['thumbnail_url']);
            $file = new File($picturePath);
            $file->write($strImage);
            $file->close();
        }
    }
}
