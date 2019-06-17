<?php

namespace Pdir\SocialFeedBundle\NewsListener;

use Pdir\SocialFeedBundle\Model\SocialFeedModel as SocialFeedModel;
use InstagramScraper\Instagram;

class CronListener extends \System
{
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

    public function getInstagramPosts() {
        $objSocialFeed = SocialFeedModel::findAll();

        foreach($objSocialFeed as $obj) {
            if($obj->socialFeedType == "Instagram") {
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();
                if($lastImport == "") $lastImport = 0;
                $interval = $tstamp - $lastImport;

                $this->setLastImportDate($id = $obj->id);

                if( ($interval >= $cron && $cron != "no_cronjob") || ($lastImport == "" && $cron != "no_cronjob") ) {
                    $accountName = $obj->instagram_account;
                    $instagram = new \InstagramScraper\Instagram();
                    $medias = $instagram->getMedias($accountName, $obj->number_posts);

                    if(is_array($medias)) {
                        foreach ($medias as $media) {
                            $objNews = new \NewsModel();
                            echo "ID: ".$media->getId();
                            if (null !== $objNews->findBy("social_feed_id", $media->getId())) {
                                continue;
                            }

                            $imgPath = $this->createImageFolder($accountName);
                            $account = $instagram->getAccount($accountName);

                            // save account picture
                            $accountPicture = $imgPath . $account->getId() . '.jpg';
                            $this->saveAccountPicture($accountPicture, $account);

                            // save pictures
                            $picturePath = $imgPath . $media->getId() . '.jpg';
                            $this->savePostPictures($picturePath, $media);

                            // Write in Database
                            $message = $this->getPostMessage($messageText = $media->getCaption());

                            // add/fetch file from DBAFS
                            $objFile = \Dbafs::addResource($imgPath . $media->getId() . '.jpg');
                            $this->saveInstagramNews($objNews, $obj, $objFile, $message, $media, $account, $accountPicture);
                        }
                    }
                    \System::log('Social Feed: Instagram Import Account '.$accountName, __METHOD__, TL_GENERAL);
                    $this->import('Automator');
                    $this->Automator->generateSymlinks();
                }
            }
        }
    }

    public function getFbPosts()
    {
        $objSocialFeed = SocialFeedModel::findAll();
        foreach($objSocialFeed as $obj) {
            if($obj->socialFeedType == "" || $obj->socialFeedType == "Facebook") {
                // Get Facebook Feed
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();
                if($lastImport == "") $lastImport = 0;
                $interval = $tstamp - $lastImport;

                $this->setLastImportDate($id = $obj->id);

                if( ($interval >= $cron && $cron != "no_cronjob") || ($lastImport == "" && $cron != "no_cronjob") ) {
                    $appId = $obj->pdir_sf_fb_app_id;
                    $appSecret = $obj->pdir_sf_fb_app_secret;
                    $accessToken = $obj->pdir_sf_fb_access_token;
                    $account = $obj->pdir_sf_fb_account;
                    $fb = new \Facebook\Facebook([
                        'app_id' => $appId,
                        'app_secret' => $appSecret,
                        'default_graph_version' => 'v2.10',
                    ]);
                    if($obj->pdir_sf_fb_posts == 1) {
                        $response = $this->getFbPostList($fb, $accessToken, $account);
                    } else {
                        $response = $this->getFbFeed($fb, $accessToken, $account);
                    }

                    $imgPath = $this->createImageFolder($account);

                    // get account picture and save it
                    $responsePage = $this->getFbAccountPicture($fb, $accessToken, $account);
                    $accountId = $responsePage->getDecodedBody()['id'];
                    $imageSrc = $responsePage->getDecodedBody()['picture']['data']['url'];
                    $strImage = file_get_contents($imageSrc);
                    $file = new \File($imgPath . $accountId . '.jpg');
                    $file->write($strImage);
                    $file->close();
                    // Write in Database
                    foreach($response->getDecodedBody()['data'] as $post) {
                        $objNews = new \NewsModel();
                        if (null !== $objNews->findBy("social_feed_id", $post['id']) ) {
                            continue;
                        }
                        if($post['from']['name'] != "") {
                            $imageSrc = $this->getFbAttachments($fb, $id = $post['id'], $accessToken, $imgPath);
                            // set variables
                            if(strpos($post['message'],"\n")) {
                                $title = substr($post['message'],0,strpos($post['message'],"\n"));
                            } else if($post['message'] == "") {
                                $title = "Kein Titel";
                            } else {
                                $title = substr($post['message'],0);
                            }

                            $message = $this->getPostMessage($messageText = $post['message']);

                            $message = str_replace("\n","<br>",$message);
                            $timestamp = strtotime($post['created_time']);
                            if($imageSrc != "") {
                                $img = $imgPath . $post['id'] . ".jpg";
                            }
                            $accountImg = $imgPath . $accountId . ".jpg";
                            // add/fetch file from DBAFS
                            if( !is_null($img) ) {
                                $objFile = \Dbafs::addResource($img);
                                $objFileAccount = \Dbafs::addResource($accountImg);
                            }
                            // create new news
                            $objNews = new \NewsModel();
                            // set data
                            $objNews->pid = $obj->pdir_sf_fb_news_archive;
                            if($imageSrc != "") {
                                $objNews->singleSRC = $objFile->uuid;
                                $objNews->addImage = 1;
                            }
                            $objNews->tstamp = time();
                            $objNews->headline = $title;
                            $objNews->teaser = $message;
                            $objNews->date = $timestamp;
                            $objNews->time = $timestamp;
                            $objNews->published = 1;
                            $objNews->social_feed_type = $obj->socialFeedType;
                            $objNews->social_feed_id = $post['id'];
                            $objNews->social_feed_account = $post['from']['name'];
                            $objNews->social_feed_account_picture = $objFileAccount->uuid;
                            $objNews->source = 'external';
                            $objNews->url = $post['permalink_url'];
                            $objNews->target = 1;
                            $objNews->save();
                        }
                    }
                    \System::log('Social Feed: Facebook Import Account '.$account, __METHOD__, TL_GENERAL);
                    $this->import('Automator');
                    $this->Automator->generateSymlinks();
                }
            }
        }
        //echo "<pre>"; print_r($objSocialFeed); echo "</pre>";
    }

    private function getFbPostList($fb, $accessToken, $account) {
        try {
            $response = $fb->get(
                $account.'/posts?access_token='.$accessToken.'&fields=id,from,created_time,message,permalink_url'
            );
            return $response;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    private function getFbFeed($fb, $accessToken, $account) {
        try {
            $response = $fb->get(
                $account.'/feed?access_token='.$accessToken.'&fields=id,from,created_time,message,permalink_url'
            );
            return $response;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    private function getFbAttachments($fb,$id,$accessToken, $imgPath) {
        try {
            $resMedia = $fb->get('/'. $id .'/attachments', $accessToken);
            $imageSrc = "";
            if($resMedia->getDecodedBody()['data']['0']['subattachments']['data']['0']['media']) {
                $arrMedia = $resMedia->getDecodedBody()['data']['0']['subattachments']['data']['0'];
            } else if($resMedia->getDecodedBody()['data']['0']['media']) {
                $arrMedia = $resMedia->getDecodedBody()['data']['0'];
            }
            if(is_array($arrMedia)) {
                $imageSrc = $arrMedia['media']['image']['src'];
                $strImage = file_get_contents($imageSrc);
                $file = new \File($imgPath . $id . '.jpg');
                $file->write($strImage);
                $file->close();
            }
            return $imageSrc;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    private function getFbAccountPicture($fb, $accessToken, $account) {
        try {
            $responsePage = $fb->get(
                $account.'?access_token='.$accessToken.'&fields=picture'
            );
            return $responsePage;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
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

    private function setLastImportDate($id) {
        $this->import('Database');
        $this->Database->prepare("UPDATE tl_social_feed SET pdir_sf_fb_news_last_import_date = ".time().", pdir_sf_fb_news_last_import_time = ".time()." WHERE id=?")->execute($id);
    }

    private function saveAccountPicture($accountPicture, $account) {
        if (!file_exists($accountPicture)) {
            $strImage = file_get_contents($account->getProfilePicUrl());
            $file = new \File($accountPicture);
            $file->write($strImage);
            $file->close();
        }
    }

    private function saveInstagramNews($objNews, $obj, $objFile, $message, $media, $account, $accountPicture) {
        $objNews->pid = $obj->pdir_sf_fb_news_archive;
        $objNews->singleSRC = $objFile->uuid;
        $objNews->addImage = 1;
        $objNews->tstamp = time();

        if (strlen($message) > 50) $more = " ...";
        else $more = "";
        $objNews->headline = substr($message, 0, 50) . $more;

        $message = str_replace("\n", "<br>", $message);
        $objNews->teaser = $message;
        $objNews->date = $media->getCreatedTime();
        $objNews->time = $media->getCreatedTime();
        $objNews->published = 1;
        $objNews->social_feed_type = $obj->socialFeedType;
        $objNews->social_feed_id = $media->getId();
        $objNews->social_feed_account = $account->getUsername();
        $objNews->social_feed_account_picture = \Dbafs::addResource($accountPicture)->uuid;
        $objNews->source = 'external';
        $objNews->url = $media->getLink();
        $objNews->target = 1;
        $objNews->save();
    }

    private function savePostPictures($picturePath, $media) {
        if (!file_exists($picturePath)) {
            $strImage = file_get_contents($media->getImageHighResolutionUrl());
            $file = new \File($picturePath);
            $file->write($strImage);
            $file->close();
        }
    }
}