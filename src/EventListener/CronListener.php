<?php

namespace Pdir\SocialFeedBundle\EventListener;

use Pdir\SocialFeedBundle\Importer\Importer;
use Pdir\SocialFeedBundle\Model\SocialFeedModel as SocialFeedModel;
use Abraham\TwitterOAuth\TwitterOAuth;
use LinkedIn\Client;
use LinkedIn\AccessToken;
use LinkedIn\Scope;

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

                // get instagram picture # not supported
                // $picture = $objImporter->getInstagramAccountImage($obj->psf_instagramAccessToken, $obj->id);

                // get instagram posts for account
                $medias = $objImporter->getInstagramPosts($obj->psf_instagramAccessToken, $obj->id, $obj->number_posts);

                if (!is_array($medias))
                    continue;

                $counter = 1;
                foreach ($medias as $media) {

                    if($counter++ > $obj->number_posts)
                    {
                        continue;
                    }

                    $objNews = new \NewsModel();

                    if (null !== $objNews->findBy("social_feed_id", $media['id'])) {
                        continue;
                    }

                    $imgPath = $this->createImageFolder($obj->id);

                    // save pictures
                    $picturePath = $imgPath . $media['id'] . '.jpg';
                    $this->savePostPictures($picturePath, $media);

                    // Write in Database
                    $message = $this->getPostMessage($media['caption']);

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
                                $title = mb_substr($post['message'],0,strpos($post['message'],"\n"));
                            } else if($post['message'] == "") {
                                $title = "Kein Titel";
                            } else {
                                $title = mb_substr($post['message'],0);
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
                            $objNews->headline = mb_substr($title,0,255);

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

    public function getLinkedinPosts() {
        $objSocialFeed = SocialFeedModel::findAll();

        \System::log('getLinkedinPosts', __METHOD__, TL_GENERAL);

        if(NULL === $objSocialFeed)
        {
            return;
        }

        foreach($objSocialFeed as $obj) {
            if($obj->socialFeedType == "LinkedIn") {
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();
                if($lastImport == "") $lastImport = 0;
                $interval = $tstamp - $lastImport;

                if( ($interval >= $cron && $cron != "no_cronjob") || ($lastImport == 0 && $cron != "no_cronjob") ) {
                    $this->setLastImportDate($id = $obj->id);

                    $client = new Client(
                        $obj->linkedin_client_id,
                        $obj->linkedin_client_secret
                    );

                    $client->setAccessToken( $obj->linkedin_access_token);

                    $posts = $client->get(
                        'shares?q=owners&owners=urn:li:organization:'.$obj->linkedin_company_id
                    );

                    $organization = $client->get(
                        'organizations/' . $obj->linkedin_company_id
                    );

                    if (!is_array($posts['elements']))
                        continue;

                    $counter = 1;
                    foreach ($posts['elements'] as $element) {

                        if($counter++ > $obj->number_posts)
                        {
                            continue;
                        }

                        $objNews = new \NewsModel();

                        if (null !== $objNews->findBy("social_feed_id", $element['id'])) {
                            continue;
                        }

                        $imgPath = $this->createImageFolder($obj->linkedin_company_id);
                        $picturePath = $imgPath . $element['id'] . '.jpg';

                        if (!file_exists($picturePath)) {
                            $file = new \File($picturePath);
                            $file->write(file_get_contents($element['content']['contentEntities'][0]['thumbnails'][0]['resolvedUrl']));
                            $file->close();
                        }

                        $message = $this->getPostMessage($element['text']['text']);

                        // add/fetch file from DBAFS
                        $objFile = \Dbafs::addResource($imgPath . $element['id'] . '.jpg');
                        $this->saveLinkedInNews($objNews, $obj, $objFile, $message, $element, $organization);
                    }

                    \System::log('Social Feed: LinkedIn Import Account ', __METHOD__, TL_GENERAL);
                    $this->import('Automator');
                    $this->Automator->generateSymlinks();
                }

                \System::log('Social Feed: LinkedIn Import ', __METHOD__, TL_GENERAL);
                $this->import('Automator');
                $this->Automator->generateSymlinks();
            }
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

                    $posts = [];

                    if($accountName != "" && $search == "") {
                        $posts = $connection->get('statuses/user_timeline', ['screen_name' => $accountName, 'tweet_mode' => 'extended', 'count' => $obj->number_posts]);
                    } else if($search != "" && $accountName != "") {
                        $posts = $connection->get("search/tweets", ["q" => $accountName, 'tweet_mode' => 'extended', 'count' => $obj->number_posts])->statuses;
                    } else if($search != "") {
                        $posts = $connection->get("search/tweets", ["q" => $search, 'tweet_mode' => 'extended', 'count' => $obj->number_posts])->statuses;
                    }

                    foreach($posts as $post) {

                        if(!$post) {
                            continue;
                        }

                        if($search != "" && $accountName != "" && strpos($post->full_text, $search)===false) { // remove unwanted tweets
                            continue;
                        }

                        if($post->retweeted_status && $obj->show_retweets != 1) {
                            continue;
                        }

                        echo $post->in_reply_to_status_id."<br>";
                        if($post->in_reply_to_status_id != "" && $obj->show_reply != 1) {
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
                        $objNews->headline = mb_substr($post->full_text, 0, 50) . $more;

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

                        $url = "https://twitter.com/".$post->user->screen_name."/status/".$post->id;

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
        $objNews->headline = mb_substr($message, 0, 50) . $more;

        $message = str_replace("\n", "<br>", $message);
        $objNews->teaser = $message;
        $objNews->date = strtotime($media['timestamp']);
        $objNews->time = strtotime($media['timestamp']);
        $objNews->published = 1;
        $objNews->social_feed_type = $obj->socialFeedType;
        $objNews->social_feed_id = $media['id'];
        $objNews->social_feed_config = $obj->id;
        #$objNews->social_feed_account = $account;
        #$objNews->social_feed_account_picture = \Dbafs::addResource($accountPicture)->uuid;
        $objNews->source = 'external';
        $objNews->url = $media['permalink'];
        $objNews->target = 1;
        $objNews->save();
    }

    private function saveLinkedInNews($objNews, $obj, $objFile, $message, $element, $organization) {
        $objNews->pid = $obj->pdir_sf_fb_news_archive;
        $objNews->singleSRC = $objFile->uuid;
        $objNews->addImage = 1;
        $objNews->tstamp = time();

        if (strlen($message) > 50) $more = " ...";
        else $more = "";
        $objNews->headline = mb_substr($message, 0, 50) . $more;

        $message = str_replace("\n", "<br>", $message);
        $objNews->teaser = $message;
        $objNews->date = $element['created']['time'] / 1000;
        $objNews->time = $element['created']['time'] / 1000;
        $objNews->published = 1;
        $objNews->social_feed_type = $obj->socialFeedType;
        $objNews->social_feed_id = $element['id'];
        $objNews->social_feed_config = $obj->id;
        $objNews->social_feed_account = $organization['localizedName'];
        #$objNews->social_feed_account_picture = \Dbafs::addResource($accountPicture)->uuid;
        $objNews->source = 'external';
        $objNews->url = 'https://www.linkedin.com/feed/update/'.$element['activity'];
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

    public function refreshLinkedInAccessToken() {
        $objSocialFeed = SocialFeedModel::findAll();

        if(NULL === $objSocialFeed)
        {
            return;
        }

        $this->import('Database');

        foreach($objSocialFeed as $obj) {

            if ($obj->socialFeedType == "LinkedIn") {
                echo "Token läuft ab: ".date('d.m.Y H:i', $obj->access_token_expires);
                if($obj->access_token_expires <= strtotime("+1 week", time())) {
                    $data = [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $obj->linkedin_refresh_token,
                        'client_id' => $obj->linkedin_client_id,
                        'client_secret' => $obj->linkedin_client_secret
                    ];

                    $token = json_decode(file_get_contents('https://www.linkedin.com/oauth/v2/accessToken?'.http_build_query($data)));

                    // Store the access token
                    $db = \Contao\Database::getInstance();
                    $set = ['linkedin_access_token' => $token->access_token, 'access_token_expires' => time() + $token->expires_in, 'linkedin_refresh_token' => $token->refresh_token, 'linkedin_refresh_token_expires' => time() + $token->refresh_token_expires_in];
                    $db->prepare('UPDATE tl_social_feed %s WHERE id = ?')->set($set)->execute($obj->id);
                }
            }
        }
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
