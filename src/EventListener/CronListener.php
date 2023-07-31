<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2023 pdir / digital agentur // pdir GmbH
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

namespace Pdir\SocialFeedBundle\EventListener;

use Abraham\TwitterOAuth\TwitterOAuth;
use Contao\Database;
use Contao\Dbafs;
use Contao\Email;
use Contao\File;
use Contao\Folder;
use Contao\NewsModel;
use Contao\System;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use LinkedIn\Client;
use Pdir\SocialFeedBundle\Importer\Importer;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;

class CronListener extends System
{
    /**
     * @throws \Exception
     */
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

    public function getInstagramPosts(): void
    {
        $objSocialFeed = SocialFeedModel::findBy('socialFeedType', 'Instagram');

        if (null === $objSocialFeed) {
            return;
        }

        foreach ($objSocialFeed as $obj) {
            $cron = $obj->pdir_sf_fb_news_cronjob;
            $lastImport = $obj->pdir_sf_fb_news_last_import_date;
            $tstamp = time();

            if ('' === $lastImport) {
                $lastImport = 0;
            }
            $interval = $tstamp - $lastImport;

            // set account name for log an import
            $accountName = $obj->instagram_account;

            if (($interval >= $cron && 'no_cronjob' !== $cron) || (0 === $lastImport && 'no_cronjob' !== $cron)) {
                $this->setLastImportDate($id = $obj->id);

                $objImporter = new Importer();

                // get instagram picture # not supported
                // $picture = $objImporter->getInstagramAccountImage($obj->psf_instagramAccessToken, $obj->id);

                // get instagram posts for account
                $medias = $objImporter->getInstagramPosts($obj->psf_instagramAccessToken, $obj->id, $obj->number_posts);

                if (!\is_array($medias)) {
                    continue;
                }

                $counter = 1;

                foreach ($medias as $media) {
                    if ($counter++ > $obj->number_posts) {
                        continue;
                    }

                    $objNews = new NewsModel();

                    if (null !== $objNews->findBy('social_feed_id', $media['id'])) {
                        continue;
                    }

                    $imgPath = $this->createImageFolder($obj->id);

                    // save pictures
                    $picturePath = $imgPath.$media['id'].'.jpg';
                    $this->savePostPictures($picturePath, $media);

                    // Write in Database
                    $message = $this->getPostMessage($media['caption']);

                    // add/fetch file from DBAFS
                    $objFile = Dbafs::addResource($imgPath.$media['id'].'.jpg');
                    $this->saveInstagramNews($objNews, $obj, $objFile, $message, $media); //, $account, $accountPicture);
                }

                System::log('Social Feed: Instagram Import Account '.$accountName, __METHOD__, TL_GENERAL);
                $this->import('Automator');
                $this->Automator->generateSymlinks();
            }
        }
    }

    public function getFbPosts(): void
    {
        $objSocialFeed = SocialFeedModel::findAll();

        if (null === $objSocialFeed) {
            return;
        }

        foreach ($objSocialFeed as $obj) {
            if (('' === $obj->socialFeedType || 'Facebook' === $obj->socialFeedType) && '' !== $obj->pdir_sf_fb_access_token) {
                // Get Facebook Feed
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();

                if ('' === $lastImport) {
                    $lastImport = 0;
                }
                $interval = $tstamp - $lastImport;

                if (($interval >= $cron && 'no_cronjob' !== $cron) || (0 === $lastImport && 'no_cronjob' !== $cron)) {
                    $this->setLastImportDate($id = $obj->id);

                    $appId = $obj->pdir_sf_fb_app_id;
                    $appSecret = $obj->pdir_sf_fb_app_secret;
                    $accessToken = $obj->pdir_sf_fb_access_token;
                    $account = $obj->pdir_sf_fb_account;
                    $fb = new Facebook([
                        'app_id' => $appId,
                        'app_secret' => $appSecret,
                        'default_graph_version' => 'v2.10',
                    ]);

                    if (1 === $obj->pdir_sf_fb_posts) {
                        $response = $this->getFbPostList($fb, $accessToken, $account);
                    } else {
                        $response = $this->getFbFeed($fb, $accessToken, $account);
                    }

                    $imgPath = $this->createImageFolder($account);

                    // get account picture and save it
                    $responsePage = $this->getFbAccountPicture($fb, $accessToken, $account);
                    $accountId = $responsePage->getDecodedBody()['id'];
                    $imageSrc = $responsePage->getDecodedBody()['picture']['data']['url'];

                    if (null !== $imageSrc) {
                        $strImage = file_get_contents($imageSrc);
                        $file = new File($imgPath.$accountId.'.jpg');
                        $file->write($strImage);
                        $file->close();
                    }

                    // Write in Database
                    foreach ($response->getDecodedBody()['data'] as $post) {
                        $objNews = new NewsModel();

                        if (null !== $objNews->findBy('social_feed_id', $post['id'])) {
                            continue;
                        }

                        if ('' !== $post['from']['name']) {
                            $image = $this->getFbAttachments($fb, $id = $post['id'], $accessToken, $imgPath);

                            if (!empty($image)) {
                                $imageSrc = $image['src'];
                                $imageTitle = $image['title'];
                            }
                            // set variables
                            if (null !== $post['message'] && strpos($post['message'], "\n")) {
                                $title = mb_substr($post['message'], 0, strpos($post['message'], "\n"));
                            } elseif (empty($post['message'])) {
                                $title = $GLOBALS['TL_LANG']['MSC']['pdirSocialFeedNoTitel'];
                            } else {
                                $title = mb_substr($post['message'], 0);
                            }

                            $message = $this->getPostMessage((string) $post['message']);

                            $message = str_replace("\n", '<br>', $message);
                            $timestamp = strtotime($post['created_time']);

                            if (!empty($imageSrc) && !empty($image)) {
                                $img = $imgPath.$post['id'].'.jpg';
                            }

                            $accountImg = $imgPath.$accountId.'.jpg';
                            // add/fetch file from DBAFS
                            if (null !== $img) {
                                $objFile = Dbafs::addResource($img);
                                $objFileAccount = Dbafs::addResource($accountImg);
                            }
                            // create new news
                            $objNews = new NewsModel();
                            // set data
                            $objNews->pid = $obj->pdir_sf_fb_news_archive;
                            $objNews->author = $obj->user;

                            if (!empty($imageSrc) && !empty($image)) {
                                $objNews->singleSRC = $objFile->uuid;
                                $objNews->addImage = 1;
                            }
                            $objNews->tstamp = time();
                            $objNews->headline = mb_substr($title, 0, 255);

                            if ('' === $message && !empty($imageTitle)) {
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
                    System::log('Social Feed: Facebook Import Account '.$account, __METHOD__, TL_GENERAL);
                    $this->import('Automator');
                    $this->Automator->generateSymlinks();
                }
            }
        }
    }

    public function getLinkedinPosts(): void
    {
        $objSocialFeed = SocialFeedModel::findAll();

        if (null === $objSocialFeed) {
            return;
        }

        foreach ($objSocialFeed as $obj) {
            if ('LinkedIn' === $obj->socialFeedType) {
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();

                if ('' === $lastImport) {
                    $lastImport = 0;
                }
                $interval = $tstamp - $lastImport;

                if (($interval >= $cron && 'no_cronjob' !== $cron) || (0 === $lastImport && 'no_cronjob' !== $cron)) {
                    $this->setLastImportDate($id = $obj->id);

                    $client = new Client(
                        $obj->linkedin_client_id,
                        $obj->linkedin_client_secret
                    );

                    $client->setAccessToken($obj->linkedin_access_token);

                    $posts = $client->get(
                        'shares?q=owners&owners=urn:li:organization:'.$obj->linkedin_company_id.'&sharesPerOwner='.$obj->number_posts
                    );

                    $organization = $client->get(
                        'organizations/'.$obj->linkedin_company_id
                    );

                    if (!\is_array($posts['elements'])) {
                        continue;
                    }

                    $counter = 1;

                    foreach ($posts['elements'] as $element) {
                        if ($counter++ > $obj->number_posts) {
                            continue;
                        }

                        $objNews = new NewsModel();

                        if (null !== $objNews->findBy('social_feed_id', $element['id'])) {
                            continue;
                        }

                        $objFile = '';

                        if (null !== $element['content']['contentEntities'][0]['thumbnails'][0]['resolvedUrl']) {
                            $imgPath = $this->createImageFolder($obj->linkedin_company_id);
                            $picturePath = $imgPath.$element['id'].'.jpg';

                            if (!file_exists($picturePath)) {
                                $file = new File($picturePath);
                                $file->write(file_get_contents($element['content']['contentEntities'][0]['thumbnails'][0]['resolvedUrl']));
                                $file->close();
                            }

                            $objFile = Dbafs::addResource($imgPath.$element['id'].'.jpg');
                        }

                        $message = $this->getPostMessage($element['text']['text']);
                        $this->saveLinkedInNews($objNews, $obj, $objFile, $message, $element, $organization);
                    }

                    System::log('Social Feed: LinkedIn Import Account ', __METHOD__, TL_GENERAL);
                    $this->import('Automator');
                    $this->Automator->generateSymlinks();
                }

                System::log('Social Feed: LinkedIn Import ', __METHOD__, TL_GENERAL);
                $this->import('Automator');
                $this->Automator->generateSymlinks();
            }
        }
    }

    public function getTwitterPosts(): void
    {
        $objSocialFeed = SocialFeedModel::findAll();

        if (null === $objSocialFeed) {
            return;
        }

        foreach ($objSocialFeed as $obj) {
            if ('Twitter' === $obj->socialFeedType) {
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();

                if ('' === $lastImport) {
                    $lastImport = 0;
                }
                $interval = $tstamp - $lastImport;

                if (($interval >= $cron && 'no_cronjob' !== $cron) || (0 === $lastImport && 'no_cronjob' !== $cron)) {
                    $this->setLastImportDate($id = $obj->id);

                    $api_key = $obj->twitter_api_key;
                    $api_secret_key = $obj->twitter_api_secret_key;
                    $access_token = $obj->twitter_access_token;
                    $access_token_secret = $obj->twitter_access_token_secret;

                    $accountName = $obj->twitter_account;
                    $search = $obj->search;
                    $connection = new TwitterOAuth($api_key, $api_secret_key, $access_token, $access_token_secret);

                    $posts = [];

                    if ('' !== $accountName && '' === $search) {
                        $posts = $connection->get('statuses/user_timeline', ['screen_name' => $accountName, 'tweet_mode' => 'extended', 'count' => $obj->number_posts]);
                    } elseif ('' !== $search && '' !== $accountName) {
                        $posts = $connection->get('search/tweets', ['q' => $accountName, 'tweet_mode' => 'extended', 'count' => $obj->number_posts])->statuses;
                    } elseif ('' !== $search) {
                        $posts = $connection->get('search/tweets', ['q' => $search, 'tweet_mode' => 'extended', 'count' => $obj->number_posts])->statuses;
                    }

                    if ($posts->errors) {
                        System::log($posts->errors[0]->message.' (Social Feed, Twitter, '.$accountName.')', __METHOD__, TL_ERROR);
                    }

                    if (!$posts->errors) {
                        foreach ($posts as $post) {
                            if (!$post) {
                                continue;
                            }

                            if ('' !== $search && '' !== $accountName && false === strpos($post->full_text, $search)) { // remove unwanted tweets
                                continue;
                            }

                            if ($post->retweeted_status && '1' !== $obj->show_retweets) {
                                continue;
                            }

                            if (null !== $post->in_reply_to_status_id && '1' !== $obj->show_reply) {
                                continue;
                            }

                            if (null !== $post->full_text) {
                                $post->full_text = mb_substr($post->full_text, $post->display_text_range[0], $post->display_text_range[1]);
                            }

                            if ($post->retweeted_status && '1' === $obj->show_retweets) {
                                $post->full_text = 'RT @'.$post->entities->user_mentions[0]->screen_name.': '.$post->retweeted_status->full_text;
                            }

                            $objNews = new NewsModel();

                            if (null !== $objNews->findBy('social_feed_id', $post->id)) {
                                continue;
                            }

                            $imgPath = $this->createImageFolder($accountName);

                            // save account picture
                            $accountPicture = $imgPath.$post->user->id.'.jpg';

                            if (!file_exists($accountPicture)) {
                                $strImage = file_get_contents($post->user->profile_image_url_https);
                                $file = new File($accountPicture);
                                $file->write($strImage);
                                $file->close();
                            }

                            // save post picture
                            if ($post->entities->media[0]->media_url_https) {
                                $picturePath = $imgPath.$post->id.'.jpg';

                                if (!file_exists($picturePath)) {
                                    $strImage = file_get_contents($post->entities->media[0]->media_url_https);
                                    $file = new File($picturePath);
                                    $file->write($strImage);
                                    $file->close();
                                }
                                $objFile = Dbafs::addResource($imgPath.$post->id.'.jpg');
                                $objNews->singleSRC = $objFile->uuid;
                                $objNews->addImage = 1;
                            }

                            // write in database
                            $objNews->pid = $obj->pdir_sf_fb_news_archive;
                            $objNews->author = $obj->user;
                            $objNews->tstamp = time();

                            if (\strlen($post->full_text) > 50) {
                                $more = ' ...';
                            } else {
                                $more = '';
                            }
                            $objNews->headline = mb_substr($post->full_text, 0, 50).$more;

                            if ('1' === $obj->hashtags_link) {
                                if ($post->retweeted_status && '1' === $obj->show_retweets) {
                                    $post->entities->hashtags = $post->retweeted_status->entities->hashtags;
                                    $post->entities->user_mentions = $post->retweeted_status->entities->user_mentions;
                                }

                                // replace t.co links
                                $post->full_text = $this->replaceLinks($post->full_text);

                                // replace all hash tags
                                $post->full_text = $this->replaceHashTags($post->full_text);

                                // replace mentions
                                $post->full_text = $this->replaceMentions($post->full_text);
                            } else {
                                // remove all t.co links
                                $post->full_text = $this->removeTwitterLinks($post->full_text);
                            }

                            $objNews->teaser = str_replace("\n", '<br>', $post->full_text);
                            $objNews->date = strtotime($post->created_at);
                            $objNews->time = strtotime($post->created_at);
                            $objNews->published = 1;
                            $objNews->social_feed_type = $obj->socialFeedType;
                            $objNews->social_feed_id = $post->id;
                            $objNews->social_feed_account = $post->user->name;
                            $objNews->social_feed_account_picture = Dbafs::addResource($accountPicture)->uuid;
                            $objNews->source = 'external';

                            $url = 'https://twitter.com/'.$post->user->screen_name.'/status/'.$post->id;

                            $objNews->url = $url;
                            $objNews->target = 1;
                            $objNews->save();
                        }

                        System::log('Social Feed: Twitter Import Account '.$accountName, __METHOD__, TL_GENERAL);
                        $this->import('Automator');
                        $this->Automator->generateSymlinks();
                    }
                }
            }
        }
    }

    public function refreshInstagramAccessToken(): void
    {
        $objSocialFeed = SocialFeedModel::findAll();

        if (null === $objSocialFeed) {
            return;
        }

        foreach ($objSocialFeed as $obj) {
            if ('Instagram' === $obj->socialFeedType && '' !== $obj->psf_instagramAccessToken) {
                if (strtotime('+1 week', time()) >= $obj->access_token_expires || '' === $obj->access_token_expires) {
                    $client = new \GuzzleHttp\Client();
                    $response = $client->get('https://graph.instagram.com/refresh_access_token', [
                        'query' => [
                            'grant_type' => 'ig_refresh_token',
                            'access_token' => $obj->psf_instagramAccessToken,
                        ],
                    ]);

                    try {
                        $data = json_decode((string) $response->getBody(), true);

                        // Store the access token
                        $db = Database::getInstance();
                        $set = ['psf_instagramAccessToken' => $data['access_token'], 'access_token_expires' => time() + $data['expires_in']];
                        $db->prepare('UPDATE tl_social_feed %s WHERE id = ?')->set($set)->execute($obj->id);
                    } catch (\Exception $e) {
                        System::log($e->getMessage(), __METHOD__, TL_GENERAL);
                    }
                }
            }
        }
    }

    public function refreshLinkedInAccessToken(): void
    {
        $objSocialFeed = SocialFeedModel::findAll();

        if (null === $objSocialFeed) {
            return;
        }

        foreach ($objSocialFeed as $obj) {
            if ('LinkedIn' === $obj->socialFeedType && '' !== $obj->linkedin_access_token && '' !== $obj->linkedin_refresh_token) {
                if ($obj->linkedin_refresh_token_expires <= strtotime('+1 week', time())) {
                    $objMail = new Email();
                    $objMail->subject = $GLOBALS['TL_LANG']['BE_MOD']['emailLinkedInSubject'];
                    $objMail->html = sprintf($GLOBALS['TL_LANG']['BE_MOD']['emailLinkedInHtml'], $this->Environment->httpHost, $obj->linkedin_company_id);
                    $objMail->from = $GLOBALS['TL_CONFIG']['adminEmail'];
                    $objMail->fromName = $this->Environment->httpHost;
                    $objMail->sendTo($GLOBALS['TL_CONFIG']['adminEmail']);
                }

                if ($obj->access_token_expires <= strtotime('+1 week', time())) {
                    $data = [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $obj->linkedin_refresh_token,
                        'client_id' => $obj->linkedin_client_id,
                        'client_secret' => $obj->linkedin_client_secret,
                    ];

                    try {
                        $token = json_decode(file_get_contents('https://www.linkedin.com/oauth/v2/accessToken?'.http_build_query($data)));

                        // Store the access token
                        $db = Database::getInstance();
                        $set = ['linkedin_access_token' => $token->access_token, 'access_token_expires' => time() + $token->expires_in, 'linkedin_refresh_token' => $token->refresh_token, 'linkedin_refresh_token_expires' => time() + $token->refresh_token_expires_in];
                        $db->prepare('UPDATE tl_social_feed %s WHERE id = ?')->set($set)->execute($obj->id);
                    } catch (\Exception $e) {
                        System::log($e->getMessage(), __METHOD__, TL_GENERAL);
                    }
                }
            }
        }
    }

    private function getFbPostList($fb, $accessToken, $account)
    {
        try {
            return $fb->get(
                $account.'/posts?access_token='.$accessToken.'&fields=id,from,created_time,message,permalink_url'
            );
        } catch (FacebookResponseException $e) {
            System::log('Graph returned an error: '.$e->getMessage(), __METHOD__, TL_ERROR);
            exit;
        } catch (FacebookSDKException $e) {
            System::log('Facebook SDK returned an error: '.$e->getMessage(), __METHOD__, TL_ERROR);
            exit;
        }
    }

    private function getFbFeed($fb, $accessToken, $account)
    {
        try {
            return $fb->get(
                $account.'/feed?access_token='.$accessToken.'&fields=id,from,created_time,message,permalink_url'
            );
        } catch (FacebookResponseException $e) {
            System::log('Graph returned an error: '.$e->getMessage(), __METHOD__, TL_ERROR);
            exit;
        } catch (FacebookSDKException $e) {
            System::log('Facebook SDK returned an error: '.$e->getMessage(), __METHOD__, TL_ERROR);
            exit;
        }
    }

    private function getFbAttachments($fb, $id, $accessToken, $imgPath)
    {
        try {
            $resMedia = $fb->get('/'.$id.'/attachments', $accessToken);

            if ($resMedia->getDecodedBody()['data']['0']['subattachments']['data']['0']['media']) {
                $arrMedia = $resMedia->getDecodedBody()['data']['0']['subattachments']['data']['0'];
            } elseif ($resMedia->getDecodedBody()['data']['0']['media']) {
                $arrMedia = $resMedia->getDecodedBody()['data']['0'];
            }

            if (\is_array($arrMedia)) {
                $imageSrc = $arrMedia['media']['image']['src'];
                $strImage = file_get_contents($imageSrc);
                $file = new File($imgPath.$id.'.jpg');
                $file->write($strImage);
                $file->close();
            }

            if (null !== $arrMedia['media']['image']['src']) {
                return [
                    'src' => $arrMedia['media']['image']['src'],
                    'title' => $arrMedia['title'],
                ];
            }

            return '';
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: '.$e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }
    }

    private function getFbAccountPicture($fb, $accessToken, $account)
    {
        try {
            return $fb->get(
                $account.'?access_token='.$accessToken.'&fields=picture'
            );
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: '.$e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }
    }

    private function getPostMessage($messageText)
    {
        if (version_compare(VERSION, '4.5', '<')) {
            //reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ?
            $message = preg_replace(
                '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
                '|[\x00-\x7F][\x80-\xBF]+'.
                '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
                '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
                '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
                '',
                $messageText
            );
        } else {
            $message = $messageText;
        }

        return $message;
    }

    private function setLastImportDate($id): void
    {
        $this->import('Database');
        $this->Database->prepare('UPDATE tl_social_feed SET pdir_sf_fb_news_last_import_date = '.time().' WHERE id=?')->execute($id);
    }

    /**
     * @throws \Exception
     */
    private function saveAccountPicture($accountPicture, $account): void
    {
        if (!file_exists($accountPicture)) {
            $strImage = file_get_contents($account->getProfilePicUrl());
            $file = new File($accountPicture);
            $file->write($strImage);
            $file->close();
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

        if (\strlen($message) > 50) {
            $more = ' ...';
        } else {
            $more = '';
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

    private function saveLinkedInNews($objNews, $obj, $objFile, $message, $element, $organization): void
    {
        $objNews->pid = $obj->pdir_sf_fb_news_archive;
        $objNews->author = $obj->user;
        $objNews->singleSRC = $objFile->uuid;
        $objNews->addImage = 1;
        $objNews->tstamp = time();

        if (\strlen($message) > 50) {
            $more = ' ...';
        } else {
            $more = '';
        }
        $objNews->headline = mb_substr($message, 0, 50).$more;

        $message = str_replace("\n", '<br>', $message);
        $objNews->teaser = $message;
        $objNews->date = $element['created']['time'] / 1000;
        $objNews->time = $element['created']['time'] / 1000;
        $objNews->published = 1;
        $objNews->social_feed_type = $obj->socialFeedType;
        $objNews->social_feed_id = $element['id'];
        $objNews->social_feed_config = $obj->id;
        $objNews->social_feed_account = $organization['localizedName'];
        //$objNews->social_feed_account_picture = Dbafs::addResource($accountPicture)->uuid;
        $objNews->source = 'external';
        $objNews->url = 'https://www.linkedin.com/feed/update/'.$element['activity'];
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

    private function replaceHashTags($str)
    {
        return preg_replace(
            '/(\#)([^\s]+)/',
            '<a href="https://twitter.com/hashtag/$2" target="_blank" rel="noreferrer noopener">#$2</a>',
            $str
        );
    }

    private function removeTwitterLinks($str)
    {
        return preg_replace(
            '/\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i',
            '',
            $str
        );
    }

    private function replaceLinks($str)
    {
        return preg_replace(
            '|(https?://([\d\w\.-]+\.[\w\.]{2,6})[^\s\]\[\<\>]*/?)|i',
            '<a href="$1" target="_blank" rel="noreferrer noopener">$1</a>',
            $str
        );
    }

    private function replaceMentions($str)
    {
        return preg_replace(
            '/@(\w+)/',
            '<a href="https://www.twitter.com/$1" target="_blank" rel="noreferrer noopener">@$1</a>',
            $str
        );
    }
}
