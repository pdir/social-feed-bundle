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

use Contao\Dbafs;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\System;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;

class NewsImporter
{
    public $accountImage;
    protected $arrNews;

    public function execute($newsArchiveId, SocialFeedModel $socialFeedAccount): void
    {
        $objNews = new NewsModel();

        // check if news exists
        if (null !== $objNews->findBy('social_feed_id', $this->arrNews['id'])) {
            return;
        }

        $objNews->pid = $newsArchiveId;

        // social feed
        $objNews->social_feed_type = $socialFeedAccount->socialFeedType;
        $objNews->social_feed_id = $this->arrNews['id'];
        $objNews->social_feed_config = $socialFeedAccount->id;

        // post image
        $objNews->singleSRC = $this->arrNews['singleSRC'];

        if (!empty($objNews->singleSRC)) {
            $objNews->addImage = 1;
        }

        // account image
        // $accountPicturePath = $imgPath . $socialFeedAccount->id . '.jpg';
        // $accountPictureUuid = $this->saveImage($accountPicturePath, $this->accountImage);
        // $objNews->social_feed_account_picture = $accountPictureUuid;

        // headline and teaser
        $objNews->headline = $this->arrNews['headline'];

        // set headline to id if headline is not set
        if ('' === $objNews->headline) {
            $objNews->headline = $this->arrNews['id'];
        }

        $objNews->teaser = $this->arrNews['teaser'];

        // author
        $objNews->author = $socialFeedAccount->user;

        // default
        $objNews->published = 1;
        $objNews->source = 'external';
        $objNews->target = 1;
        $objNews->url = $this->arrNews['permalink'];
        $objNews->tstamp = time();
        $objNews->date = $this->arrNews['date'];
        $objNews->time = $this->arrNews['time'];
        $objNews->alias = $this->generateAlias($objNews->headline, $objNews->pid);

        if (null !== NewsModel::findOneByAlias($objNews->alias)) {
            $objNews->alias .= '-'.$this->arrNews['id'];
        }

        // save the news
        $objNews->save();
    }

    public function setNews($arr): void
    {
        $this->arrNews = $arr;
    }

    public static function createImageFolder($account): string
    {
        // Create Public Image Folder
        $imgPath = 'files/social-feed/'.$account.'/';

        if (!file_exists($imgPath)) {
            new Folder($imgPath);
            $file = new File('files/social-feed/.public');
            $file->write('');
            $file->close();
        }

        return $imgPath;
    }

    public static function saveImage($strPath, $strUrl): ?string
    {
        if (!file_exists($strPath)) {
            $strImage = file_get_contents($strUrl);
            $file = new File($strPath);
            $file->write($strImage);
            $file->close();

            // add resource
            $objFile = Dbafs::addResource($file->path);

            return $objFile->uuid;
        }

        // use existing file
        $objFile = FilesModel::findByPath($strPath);

        return $objFile->uuid;
    }

    public function generateAlias($headline, $newsArchiveId)
    {
        return System::getContainer()->get('contao.slug')->generate($headline, NewsArchiveModel::findById($newsArchiveId)->jumpTo);
    }

    public static function shortenHeadline($str): string
    {
        $arr = explode("\n", $str);

        $message = $arr[0] ?? $str;
        $more = '';

        if (\strlen($message) > 50) {
            $more = ' ...';
        }

        return mb_substr($message, 0, 50).$more;
    }

    public static function setLastImportDate(SocialFeedModel $socialFeedModel): void
    {
        $socialFeedModel->pdir_sf_fb_news_last_import_date = time();
        $socialFeedModel->save();
    }
}
