<?php

namespace Pdir\SocialFeedBundle\Importer;

use Contao\Dbafs;
use Contao\Input;
use Contao\NewsModel;


class NewsImporter
{
    protected $objModel;

    public $accountImage;

    public function __construct($objModel)
    {
        $this->objModel = $objModel;
    }

    public function execute($newsArchiveId, $socialFeedType, $socialFeedAccount) {

        $objNews = new NewsModel();

        // check if news exists
        if (null !== $objNews->findBy("social_feed_id", $this->objModel->getId())) {
            return;
        }

        $objNews->pid = $newsArchiveId;

        // social feed
        $objNews->social_feed_type = $socialFeedType;
        $objNews->social_feed_id = $this->objModel->getId();
        $objNews->social_feed_account = $socialFeedAccount;

        // images
        $imgPath = $this->createImageFolder($socialFeedAccount); // create image folder

        // account image
        $accountPicturePath = $imgPath . $this->objModel->getOwner()->getUSername() . '.jpg';
        $accountPictureUuid = $this->saveImage($accountPicturePath, $this->accountImage);

        $objNews->social_feed_account_picture = $accountPictureUuid;

        // post images
        $picturePath = $imgPath . $objNews->social_feed_id . '.jpg';
        $pictureUuid = $this->saveImage($picturePath, $this->objModel->getImageHighResolutionUrl());

        $objNews->addImage = 1;
        $objNews->singleSRC = $pictureUuid;

        // message and teaser
        $message = $this->getPostMessage($this->objModel->getCaption());
        $more = "";
        if (strlen($message) > 50)
        {
            $more = " ...";
        }

        $objNews->headline = substr($message, 0, 50) . $more;

        $message = str_replace("\n", "<br>", $message);
        $objNews->teaser = $message;

        // date and time
        $objNews->date = $this->objModel->getCreatedTime();
        $objNews->time = $this->objModel->getCreatedTime();

        // default
        $objNews->published = 1;
        $objNews->source = 'external';
        $objNews->target = 1;
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
            new \Folder($imgPath);
            $file = new \File("files/social-feed/.public");
            $file->write("");
            $file->close();
        }
        return $imgPath;
    }

    private function saveImage($strPath, $strUrl) {
        if (!file_exists($strPath)) {
            $strImage = file_get_contents($strUrl);
            $file = new \File($strPath);
            $file->write($strImage);
            $file->close();

            return $file->uuid;
        }
        return;
    }
}
