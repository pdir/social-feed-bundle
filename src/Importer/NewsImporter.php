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
use Contao\NewsModel;

class NewsImporter
{
    public $accountImage;
    protected $arrNews;

    public function execute($newsArchiveId, $socialFeedType, $socialFeedAccount): void
    {
        $objNews = new NewsModel();

        // check if news exists
        if (null !== $objNews->findBy('social_feed_id', $this->arrNews['id'])) {
            return;
        }

        $objNews->pid = $newsArchiveId;

        // social feed
        $objNews->social_feed_type = $socialFeedType;
        $objNews->social_feed_id = $this->arrNews['id'];
        $objNews->social_feed_config = $socialFeedAccount;

        // images
        $imgPath = $this->createImageFolder($socialFeedAccount); // create image folder

        // account image
        // $accountPicturePath = $imgPath . $socialFeedAccount . '.jpg';
        // $accountPictureUuid = $this->saveImage($accountPicturePath, $this->accountImage);
        // $objNews->social_feed_account_picture = $accountPictureUuid;

        // post images
        if ('VIDEO' === $this->arrNews['media_type'] || 'IMAGE' === $this->arrNews['media_type'] || 'CAROUSEL_ALBUM' === $this->arrNews['media_type']) {
            $imgSrc = '';

            if (isset($this->arrNews['media_url'])) {
                $imgSrc = false !== strpos($this->arrNews['media_url'], 'jpg') ? $this->arrNews['media_url'] : $this->arrNews['thumbnail_url'];
            }

            if (!isset($this->arrNews['media_url']) && isset($this->arrNews['children']['data'][0]['media_url'])) {
                $imgSrc = $this->arrNews['children']['data'][0]['media_url'];
            }

            $picturePath = $imgPath.$objNews->social_feed_id.'.jpg';
            $pictureUuid = $this->saveImage($picturePath, $imgSrc);

            $objNews->addImage = 1;
            $objNews->singleSRC = $pictureUuid;
        }

        // message and teaser
        $message = $this->arrNews['caption']?? '';
        $more = '';

        if (\strlen($message) > 50) {
            $more = ' ...';
        }

        $objNews->headline = mb_substr($message, 0, 50).$more;

        // set headline to id if headline is not set
        if ('' === $objNews->headline) {
            $objNews->headline = $this->arrNews['id'];
        }

        $message = str_replace("\n", '<br>', $message);
        $objNews->teaser = $message;

        // author
        $objNews->author = $socialFeedAccount->user;

        // date and time
        $objNews->date = strtotime($this->arrNews['timestamp']);
        $objNews->time = strtotime($this->arrNews['timestamp']);

        // default
        $objNews->published = 1;
        $objNews->source = 'external';
        $objNews->target = 1;
        $objNews->url = $this->arrNews['permalink'];
        $objNews->tstamp = time();

        $objNews->save();
    }

    public function setNews($arr): void
    {
        $this->arrNews = $arr;
    }

    public function createImageFolder($account)
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

    private function saveImage($strPath, $strUrl)
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
}
