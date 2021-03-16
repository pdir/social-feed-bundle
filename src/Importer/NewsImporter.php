<?php

namespace Pdir\SocialFeedBundle\Importer;

use Contao\Dbafs;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\Input;
use Contao\NewsModel;


class NewsImporter
{
    protected $arrNews;

    public $accountImage;

    public function __construct($arrNews)
    {
        $this->arrNews = $arrNews;
    }

    public function execute($newsArchiveId, $socialFeedType, $socialFeedAccount) {

        $objNews = new NewsModel();

        // check if news exists
        if (null !== $objNews->findBy("social_feed_id", $this->arrNews['id'])) {
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
        if('VIDEO' == $this->arrNews['media_type'] || 'IMAGE' == $this->arrNews['media_type'] || 'CAROUSEL_ALBUM' == $this->arrNews['media_type']) {
            $imgSrc = strpos($this->arrNews['media_url'],"jpg")!==false ? $this->arrNews['media_url'] : $this->arrNews['thumbnail_url'];

            $picturePath = $imgPath . $objNews->social_feed_id . '.jpg';
            $pictureUuid = $this->saveImage($picturePath, $imgSrc);

            $objNews->addImage = 1;
            $objNews->singleSRC = $pictureUuid;
        }

        // message and teaser
        $message = $this->getPostMessage($this->arrNews['caption']);
        $more = "";
        if (strlen($message) > 50)
        {
            $more = " ...";
        }

        $objNews->headline = mb_substr($message, 0, 50) . $more;

        // set headline to id if headline is not set
        if('' == $objNews->headline)
        {
            $objNews->headline = $this->arrNews['id'];
        }

        $message = str_replace("\n", "<br>", $message);
        $objNews->teaser = $message;

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

    private function getPostMessage($messageText) {
        if (version_compare(VERSION, '4.5', '<')) {
            //reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ?
            $message = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
                '|[\x00-\x7F][\x80-\xBF]+' .
                '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
                '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
                '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
                '', $messageText);
        } else {
            $message = $messageText;
        }
        return $message;
    }

    public function createImageFolder($account) {
        // Create Public Image Folder
        $imgPath = "files/social-feed/".$account."/";
        if( !file_exists($imgPath) ) {
            new Folder($imgPath);
            $file = new File("files/social-feed/.public");
            $file->write("");
            $file->close();
        }
        return $imgPath;
    }

    private function saveImage($strPath, $strUrl) {
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
