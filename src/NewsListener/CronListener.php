<?php

namespace Pdir\SocialFeedBundle\NewsListener;

use Pdir\SocialFeedBundle\Model\SocialFeedModel as SocialFeedModel;

class CronListener extends \System
{
    public function getFbPosts()
    {
        $objSocialFeed = SocialFeedModel::findAll();

        foreach($objSocialFeed as $obj) {
            // Get Facebook Feed
            $cron = $obj->pdir_sf_fb_news_cronjob;
            $lastImport = $obj->pdir_sf_fb_news_last_import_date;
            $tstamp = time();
            $interval = $tstamp - $lastImport;
            if( ($interval >= $cron && $cron != "no_cronjob") || ($lastImport == "" && $cron != "no_cronjob") ) {
                echo "Cron ausgeführt<br>";

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
                //echo "<pre>"; print_r($response); echo "</pre>";

                // Create Public Image Folder
                $imgPath = "files/social-feed/".$account."/";
                if( !file_exists($imgPath) ) {
                    new \Folder($imgPath);
                    $file = new \File("files/social-feed/.public");
                    $file->write("");
                    $file->close();
                }

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
                    if (null !== $objNews->findBy("pdir_sf_fb_id", $post['id']) ) {
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
                        $message = str_replace("\n","<br>",$post['message']);
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
                        $objNews->pdir_sf_fb_id = $post['id'];
                        $objNews->pdir_sf_fb_account = $post['from']['name'];
                        $objNews->pdir_sf_fb_account_picture = $objFileAccount->uuid;
                        $objNews->pdir_sf_fb_link = $post['permalink_url'];

                        $objNews->save();

                    }
                }

                \System::log('Social Feed: Import Account '.$account, __METHOD__, TL_GENERAL);
                // set timestamp
                $this->import('Database');
                $this->Database->prepare("UPDATE tl_social_feed SET pdir_sf_fb_news_last_import_date = ".time().", pdir_sf_fb_news_last_import_time = ".time()." WHERE pdir_sf_fb_account=?")->execute($account);

                $this->import('Automator');
                $this->Automator->generateSymlinks();
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
}