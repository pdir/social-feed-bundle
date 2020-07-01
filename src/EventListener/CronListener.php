<?php

namespace Pdir\SocialFeedBundle\EventListener;

use Pdir\SocialFeedBundle\Importer\Importer;
use Pdir\SocialFeedBundle\Model\SocialFeedModel as SocialFeedModel;
use Abraham\TwitterOAuth\TwitterOAuth;

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
        $objSocialFeed = SocialFeedModel::findBy('socialFeedType', 'Instagram');

        if(NULL === $objSocialFeed)
        {
            return;
        }

        foreach ($objSocialFeed as $obj) {

            $cron = $obj->pdir_sf_fb_news_cronjob;
            $lastImport = $obj->pdir_sf_fb_news_last_import_date;
            $tstamp = time();
            if ($lastImport == "") $lastImport = 0;
            $interval = $tstamp - $lastImport;

            // set account name for log an import
            $accountName = $obj->instagram_account;

            if (($interval >= $cron && $cron != "no_cronjob") || ($lastImport == 0 && $cron != "no_cronjob")) {

                $this->setLastImportDate($id = $obj->id);

                $objImporter = new Importer();

                // get instagram account data
                $account = $objImporter->getInstagramAccount($obj->id, $obj->psf_instagramAppId, $obj->psf_instagramAccessToken);


                // get instagram posts for account
                $medias = $objImporter->getInstagramPosts($obj->id, $obj->psf_instagramAppId, $obj->psf_instagramAccessToken, $obj->number_posts);

                if (!is_array($medias))
                    continue;

                foreach ($medias as $media) {
                    $objNews = new \NewsModel();

                    if (null !== $objNews->findBy("social_feed_id", $media['id'])) {
                        continue;
                    }

                    $imgPath = $this->createImageFolder($obj->id);

                    // save account picture
                    // $accountPicture = $imgPath . $account->getId() . '.jpg';
                    // $this->saveAccountPicture($accountPicture, $account);

                    // save pictures
                    $picturePath = $imgPath . $obj->id . '.jpg';
                    $this->savePostPictures($picturePath, $media);

                    // Write in Database
                    $message = $this->getPostMessage($messageText = $media['caption']);

                    // add/fetch file from DBAFS
                    $objFile = \Dbafs::addResource($imgPath . $media['id'] . '.jpg');
                    $this->saveInstagramNews($objNews, $obj, $objFile, $message, $media); //, $account, $accountPicture);
                }

                \System::log('Social Feed: Instagram Import Account ' . $accountName, __METHOD__, TL_GENERAL);
                $this->import('Automator');
                $this->Automator->generateSymlinks();
            }
        }
    }

    public function getFbPosts()
    {
        $objSocialFeed = SocialFeedModel::findAll();

        if(NULL == $objSocialFeed)
        {
            return;
        }

        foreach($objSocialFeed as $obj) {
            if($obj->socialFeedType == "" || $obj->socialFeedType == "Facebook") {
                // Get Facebook Feed
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();
                if($lastImport == "") $lastImport = 0;
                $interval = $tstamp - $lastImport;

                if( ($interval >= $cron && $cron != "no_cronjob") || ($lastImport == 0 && $cron != "no_cronjob") ) {
                    $this->setLastImportDate($id = $obj->id);

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
                            $image = $this->getFbAttachments($fb, $id = $post['id'], $accessToken, $imgPath);
                            $imageSrc = $image['src'];
                            $imageTitle = $image['title'];
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
                            $objNews->headline = substr($title,0,255);

                            if($message == "" && $imageTitle != "") {
                                $objNews->teaser = $imageTitle;
                            } else {
                                $objNews->teaser = $message;
                            }

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
            $image = [
                'src' => $arrMedia['media']['image']['src'],
                'title' => $arrMedia['title']
            ];
            return $image;
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

    public function getTwitterPosts() {
        $objSocialFeed = SocialFeedModel::findAll();

        if(NULL === $objSocialFeed)
        {
            return;
        }

        foreach($objSocialFeed as $obj) {

            if($obj->socialFeedType == "Twitter") {
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();
                if($lastImport == "") $lastImport = 0;
                $interval = $tstamp - $lastImport;

                if( ($interval >= $cron && $cron != "no_cronjob") || ($lastImport == 0 && $cron != "no_cronjob") ) {
                    $this->setLastImportDate($id = $obj->id);

                    $api_key = $obj->twitter_api_key;
                    $api_secret_key = $obj->twitter_api_secret_key;
                    $access_token = $obj->twitter_access_token;
                    $access_token_secret = $obj->twitter_access_token_secret;

                    $accountName = $obj->twitter_account;
                    $search = $obj->search;
                    $connection = new TwitterOAuth($api_key, $api_secret_key, $access_token, $access_token_secret);

                    if($accountName != "") {
                        $posts = $connection->get("statuses/user_timeline", ["screen_name" => $accountName, "tweet_mode" => 'extended', "count" => $obj->number_posts]);
                    } else if($search != "") {
                        $posts = $connection->get("search/tweets", ["q" => $search, "tweet_mode" => 'extended', "count" => $obj->number_posts])->statuses;
                    } else {
                        $posts = [];
                    }

                    foreach($posts as $post) {
                        if($post->retweeted_status && $obj->show_retweets != 1) {
                            continue;
                        }

                        if($post->retweeted_status && $obj->show_retweets == 1) {
                            $post->full_text = "RT @".$post->entities->user_mentions[0]->screen_name.": ".$post->retweeted_status->full_text;
                        }

                        $objNews = new \NewsModel();

                        if (null !== $objNews->findBy("social_feed_id", $post->id)) {
                            continue;
                        }

                        $imgPath = $this->createImageFolder($accountName);

                        // save account picture
                        $accountPicture = $imgPath . $post->user->id . '.jpg';
                        if (!file_exists($accountPicture)) {
                            $strImage = file_get_contents($post->user->profile_image_url_https);
                            $file = new \File($accountPicture);
                            $file->write($strImage);
                            $file->close();
                        }

                        // save post picture
                        if($post->entities->media[0]->media_url_https) {
                            $picturePath = $imgPath . $post->id . '.jpg';
                            if (!file_exists($picturePath)) {
                                $strImage = file_get_contents($post->entities->media[0]->media_url_https);
                                $file = new \File($picturePath);
                                $file->write($strImage);
                                $file->close();
                            }
                            $objFile = \Dbafs::addResource($imgPath . $post->id . '.jpg');
                            $objNews->singleSRC = $objFile->uuid;
                            $objNews->addImage = 1;
                        }

                        // write in database
                        $objNews->pid = $obj->pdir_sf_fb_news_archive;
                        $objNews->tstamp = time();
                        if (strlen($post->full_text) > 50) $more = " ...";
                        else $more = "";
                        $objNews->headline = substr($post->full_text, 0, 50) . $more;

                        if($obj->hashtags_link == 1) {
                            if($post->retweeted_status && $obj->show_retweets == 1) {
                                $post->entities->hashtags = $post->retweeted_status->entities->hashtags;
                                $post->entities->user_mentions = $post->retweeted_status->entities->user_mentions;
                            }

                            // remove all t.co links
                            $post->full_text = $this->removeTwitterLinks($post->full_text);

                            // replace all hash tags
                            $post->full_text = $this->replaceHashTags($post->full_text);

                            // replace mentions
                            $post->full_text = $this->replaceMentions($post->full_text);
                        }

                        $objNews->teaser = str_replace("\n", "<br>", $post->full_text);
                        $objNews->date = strtotime($post->created_at);
                        $objNews->time = strtotime($post->created_at);
                        $objNews->published = 1;
                        $objNews->social_feed_type = $obj->socialFeedType;
                        $objNews->social_feed_id = $post->id;
                        $objNews->social_feed_account = $post->user->name;
                        $objNews->social_feed_account_picture = \Dbafs::addResource($accountPicture)->uuid;
                        $objNews->source = 'external';

                        if($post->entities->urls[0]->url) {
                            $url = $post->entities->urls[0]->url;
                        } else {
                            $url = "https://twitter.com/".$post->user->screen_name."/status/".$post->id;
                        }

                        $objNews->url = $url;
                        $objNews->target = 1;
                        $objNews->save();
                    }

                    \System::log('Social Feed: Twitter Import Account '.$accountName, __METHOD__, TL_GENERAL);
                    $this->import('Automator');
                    $this->Automator->generateSymlinks();
                }
            }
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

    private function saveInstagramNews($objNews, $obj, $objFile, $message, $media, $account = '', $accountPicture = '') {
        $objNews->pid = $obj->pdir_sf_fb_news_archive;
        $objNews->singleSRC = $objFile->uuid;
        $objNews->addImage = 1;
        $objNews->tstamp = time();

        if('' == $message)
        {
            $message = $media['id'];
        }

        if (strlen($message) > 50) $more = " ...";
        else $more = "";
        $objNews->headline = substr($message, 0, 50) . $more;

        $message = str_replace("\n", "<br>", $message);
        $objNews->teaser = $message;
        $objNews->date = $media['timestamp'];
        $objNews->time = $media['timestamp'];
        $objNews->published = 1;
        $objNews->social_feed_type = $obj->socialFeedType;
        $objNews->social_feed_id = $media['id'];
        #$objNews->social_feed_account = $account;
        #$objNews->social_feed_account_picture = \Dbafs::addResource($accountPicture)->uuid;
        $objNews->source = 'external';
        $objNews->url = $media['link'];
        $objNews->target = 1;
        $objNews->save();
    }

    private function savePostPictures($picturePath, $media) {
        if (!file_exists($picturePath)) {
            $strImage = file_get_contents(strpos($media['media_url'],"jpg")!==false ? $media['media_url'] : $media['thumbnail_url']);
            $file = new \File($picturePath);
            $file->write($strImage);
            $file->close();
        }
    }

    private function replaceHashTags($str) {
        return preg_replace(
            '/(\#)([^\s]+)/',
            '<a href="https://twitter.com/hashtag/$2" target="_blank" rel="noreferrer noopener">#$2</a>',
            $str
        );
    }

    private function removeTwitterLinks($str) {
        return preg_replace('/\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i',
            '',
            $str
        );
    }

    private function replaceLinks($str) {
        return preg_replace(
            '/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t&lt;]*)/is',
            '<a href="$3" target="_blank" rel="noreferrer noopener">$3</a>',
            $str
        );
    }

    private function replaceMentions($str) {
        return preg_replace(
            '/@(\w+)/',
            '<a href="https://www.twitter.com/$1" target="_blank" rel="noreferrer noopener">@$1</a>',
            $str
        );
    }
}
