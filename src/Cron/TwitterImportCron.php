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

use Abraham\TwitterOAuth\TwitterOAuth;
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
class TwitterImportCron
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
            $objSocialFeed = SocialFeedModel::findBy(['socialFeedType = ?', 'pdir_sf_fb_news_cronjob != ?'], ['Twitter', 'no_cronjob']);
        } else {
            $objSocialFeed = SocialFeedModel::findBy(['socialFeedType = ?', 'pdir_sf_fb_news_cronjob = ?'], ['Twitter', 'no_cronjob']);
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

                $api_key = $account->twitter_api_key;
                $api_secret_key = $account->twitter_api_secret_key;
                $access_token = $account->twitter_access_token;
                $access_token_secret = $account->twitter_access_token_secret;

                $accountName = $account->twitter_account;
                $search = $account->search;
                $connection = new TwitterOAuth($api_key, $api_secret_key, $access_token, $access_token_secret);

                $posts = [];

                if ('' !== $accountName && '' === $search) {
                    $posts = $connection->get('statuses/user_timeline', ['screen_name' => $accountName, 'tweet_mode' => 'extended', 'count' => $account->number_posts]);
                } elseif ('' !== $search && '' !== $accountName) {
                    $posts = $connection->get('search/tweets', ['q' => $accountName, 'tweet_mode' => 'extended', 'count' => $account->number_posts])->statuses;
                } elseif ('' !== $search) {
                    $posts = $connection->get('search/tweets', ['q' => $search, 'tweet_mode' => 'extended', 'count' => $account->number_posts])->statuses;
                }

                if ($posts->errors) {
                    $logger->log(LogLevel::ERROR, 'Social Feed (ID '.$account->id.'): X Import - '.$posts->errors[0]->message, ['contao' => new ContaoContext(__METHOD__, LogLevel::ERROR)]);
                    $this->counter = -1;
                }

                if (!$posts->errors) {
                    foreach ($posts as $post) {
                        if (!$post) {
                            continue;
                        }

                        if ('' !== $search && '' !== $accountName && false === \strpos($post->full_text, $search)) { // remove unwanted tweets
                            continue;
                        }

                        if ($post->retweeted_status && '1' !== $account->show_retweets) {
                            continue;
                        }

                        if (null !== $post->in_reply_to_status_id && '1' !== $account->show_reply) {
                            continue;
                        }

                        if (null !== $post->full_text) {
                            $post->full_text = \mb_substr($post->full_text, $post->display_text_range[0], $post->display_text_range[1]);
                        }

                        if ($post->retweeted_status && '1' === $account->show_retweets) {
                            $post->full_text = 'RT @' . $post->entities->user_mentions[0]->screen_name . ': ' . $post->retweeted_status->full_text;
                        }

                        $objNews = new NewsModel();

                        if (null !== $objNews->findBy('social_feed_id', $post->id)) {
                            continue;
                        }

                        $imgPath = NewsImporter::createImageFolder($accountName);

                        // save account picture
                        $accountPicture = $imgPath . $post->user->id . '.jpg';

                        if (!\file_exists($accountPicture)) {
                            $strImage = \file_get_contents($post->user->profile_image_url_https);
                            $file = new File($accountPicture);
                            $file->write($strImage);
                            $file->close();
                        }

                        // save post picture
                        if ($post->entities->media[0]->media_url_https) {
                            $picturePath = $imgPath . $post->id . '.jpg';

                            if (!\file_exists($picturePath)) {
                                $strImage = \file_get_contents($post->entities->media[0]->media_url_https);
                                $file = new File($picturePath);
                                $file->write($strImage);
                                $file->close();
                            }
                            $objFile = Dbafs::addResource($imgPath . $post->id . '.jpg');
                            $objNews->singleSRC = $objFile->uuid;
                            $objNews->addImage = 1;
                        }

                        // write to database
                        $objNews->pid = $account->pdir_sf_fb_news_archive;
                        $objNews->author = $account->user;
                        $objNews->tstamp = \time();

                        $more = '';
                        if (null !== $post->full_text && \strlen($post->full_text) > 50) {
                            $more = ' ...';
                        }

                        $objNews->headline = \mb_substr($post->full_text, 0, 50) . $more;

                        if ('1' === $account->hashtags_link) {
                            if ($post->retweeted_status && '1' === $account->show_retweets) {
                                $post->entities->hashtags = $post->retweeted_status->entities->hashtags;
                                $post->entities->user_mentions = $post->retweeted_status->entities->user_mentions;
                            }

                            // replace t.co links
                            $post->full_text = $this->replaceLinks($post->full_text);

                            // replace all hashtags
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
                        $objNews->social_feed_type = $account->socialFeedType;
                        $objNews->social_feed_id = $post->id;
                        $objNews->social_feed_account = $post->user->name;
                        $objNews->social_feed_account_picture = Dbafs::addResource($accountPicture)->uuid;
                        $objNews->source = 'external';

                        $url = 'https://x.com/' . $post->user->screen_name . '/status/' . $post->id;

                        $objNews->url = $url;
                        $objNews->target = 1;
                        $objNews->save();

                        $this->counter++;
                    }

                    if (0 < $this->counter) {
                        $logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): X - imported ' . $this->counter . ' items.', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
                    }
                }

                if (0 === $this->counter) {
                    $logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): X Import - nothing to import', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
                }
            }
        }
    }

    private function replaceLinks($str): array|string|null
    {
        return \preg_replace(
            '|(https?://([\d\w\.-]+\.[\w\.]{2,6})[^\s\]\[\<\>]*/?)|i',
            '<a href="$1" target="_blank" rel="noreferrer noopener">$1</a>',
            $str
        );
    }

    private function replaceHashTags($str): array|string|null
    {
        return \preg_replace(
            '/(\#)([^\s]+)/',
            '<a href="https://x.com/hashtag/$2" target="_blank" rel="noreferrer noopener">#$2</a>',
            $str
        );
    }

    private function replaceMentions($str): array|string|null
    {
        return \preg_replace(
            '/@(\w+)/',
            '<a href="https://www.x.com/$1" target="_blank" rel="noreferrer noopener">@$1</a>',
            $str
        );
    }

    private function removeTwitterLinks($str): array|string|null
    {
        return \preg_replace(
            '/\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i',
            '',
            $str
        );
    }
}
