<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2026 pdir / digital agentur // pdir GmbH
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

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Dbafs;
use Contao\File;
use Contao\NewsModel;
use Contao\System;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Pdir\SocialFeedBundle\Importer\NewsImporter;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Psr\Log\LogLevel;

#[AsCronJob('minutely')]
class FacebookImportCron
{
    use ImportCronHelperTrait;

    public int $counter = 0;
    private ?object $logger;

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
        $this->logger = System::getContainer()->get('monolog.logger.contao');

        $objSocialFeed = SocialFeedAccountModel::findForImport('Facebook', $this->poorManCron);

        if (null === $objSocialFeed) {
            return;
        }

        /** @var SocialFeedAccountModel $account */
        foreach ($objSocialFeed as $account) {
            $this->counter = 0;

            $service = $account->getService();
            $accessToken = $account->getAccessToken();

            // Skip accounts without service or access token
            if (null === $service) {
                $this->logger->log(LogLevel::ERROR, 'Social Feed (ID '.$account->id.'): Facebook account has no service.', ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
                continue;
            }

            if ('' === $accessToken) {
                $this->logger->log(LogLevel::ERROR, 'Social Feed (ID '.$account->id.'): Facebook account has no access token.', ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
                continue;
            }

            // Get Facebook Feed
            $cron = (int) $account->cronjob;
            $lastImport = (int) $account->lastImport;
            $interval = time() - $lastImport;

            if ($interval >= $cron || 0 === $lastImport || false === $this->poorManCron) {
                NewsImporter::setLastImportDate($account);

                $page = $account->identifier ?: $account->name;

                $fb = new Facebook([
                    'app_id' => $service->appId,
                    'app_secret' => $service->appSecret,
                    'default_graph_version' => $service->apiVersion ?: 'v20.0',
                ]);

                if ('1' === (string) $account->importPostsOnly) {
                    $response = $this->getFbPostList($fb, $accessToken, $page);
                } else {
                    $response = $this->getFbFeed($fb, $accessToken, $page);
                }

                $imgPath = NewsImporter::createImageFolder($account->id);

                // get account picture and save it
                $responsePage = $this->getFbAccountPicture($fb, $accessToken, $page);
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
                    $img = null;
                    $objNews = null;

                    if (null !== NewsModel::findBy('social_feed_id', $post['id'])) {
                        continue;
                    }

                    if ('' !== $post['from']['name']) {
                        $image = $this->getFbAttachments($fb, $post['id'], $accessToken, $imgPath);

                        if (!empty($image)) {
                            $imageSrc = $image['src'];
                            $imageTitle = $image['title'] ?? '';
                        }
                        // set variables
                        if (null !== $post['message'] && strpos($post['message'], "\n")) {
                            $title = mb_substr($post['message'], 0, strpos($post['message'], "\n"));
                        } elseif (empty($post['message'])) {
                            $title = $GLOBALS['TL_LANG']['MSC']['pdirSocialFeedNoTitel'];
                        } else {
                            $title = mb_substr($post['message'], 0);
                        }

                        $message = $post['message'] ?? '';
                        $message = str_replace("\n", '<br>', $message);
                        $timestamp = strtotime($post['created_time']);

                        if (!empty($imageSrc) && !empty($image)) {
                            $img = $imgPath.$post['id'].'.jpg';
                        }

                        $accountImg = $imgPath.$accountId.'.jpg';
                        $objFileAccount = file_exists(System::getContainer()->getParameter('kernel.project_dir').'/'.$accountImg) ? Dbafs::addResource($accountImg) : null;

                        // add/fetch file from DBAFS
                        if (null !== $img) {
                            $objFile = Dbafs::addResource($img);
                        }

                        // create new news
                        $objNews = new NewsModel();

                        // set data
                        $objNews->pid = $account->newsArchive;
                        $objNews->author = $account->author;

                        if (!empty($imageSrc) && !empty($image)) {
                            $objNews->singleSRC = $objFile->uuid;
                            $objNews->addImage = 1;
                        }
                        $objNews->tstamp = time();
                        $objNews->headline = mb_substr($title ?? '', 0, 255);

                        if ('' === $message && !empty($imageTitle)) {
                            $objNews->teaser = $imageTitle;
                        } else {
                            $objNews->teaser = $message;
                        }

                        $objNews->date = $timestamp;
                        $objNews->time = $timestamp;
                        $objNews->published = 1;
                        $objNews->social_feed_type = $account->type;
                        $objNews->social_feed_id = $post['id'];
                        $objNews->social_feed_config = $account->id;
                        $objNews->social_feed_account = $post['from']['name'];
                        $objNews->social_feed_account_picture = $objFileAccount?->uuid;
                        $objNews->source = 'external';
                        $objNews->url = $post['permalink_url'];
                        $objNews->target = 1;
                        $objNews->save();

                        ++$this->counter;
                    }

                    if (0 < $this->counter) {
                        $this->logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): Facebook  - imported '.$this->counter.' items.', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
                    }
                }

                if (0 === $this->counter) {
                    $this->logger->log(LogLevel::INFO, 'Social Feed (ID '.$account->id.'): Facebook Import - nothing to import', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);
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
            $this->logger->log(LogLevel::ERROR, 'Graph returned an error: '.$e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            exit;
        } catch (FacebookSDKException $e) {
            $this->logger->log(LogLevel::ERROR, 'Facebook SDK returned an error: '.$e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
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
            $this->logger->log(LogLevel::ERROR, 'Graph returned an error: '.$e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            exit;
        } catch (FacebookSDKException $e) {
            $this->logger->log(LogLevel::ERROR, 'Facebook SDK returned an error: '.$e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            exit;
        }
    }

    private function getFbAttachments($fb, $id, $accessToken, $imgPath)
    {
        try {
            $resMedia = $fb->get('/'.$id.'/attachments', $accessToken);

            if (isset($resMedia->getDecodedBody()['data']['0']['subattachments']) && $resMedia->getDecodedBody()['data']['0']['subattachments']['data']['0']['media']) {
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
                    'title' => $arrMedia['title'] ?? '',
                ];
            }

            return '';
        } catch (FacebookResponseException $e) {
            $this->logger->log(LogLevel::ERROR, 'Graph returned an error: '.$e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            exit;
        } catch (FacebookSDKException $e) {
            $this->logger->log(LogLevel::ERROR, 'Facebook SDK returned an error: '.$e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
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
            $this->logger->log(LogLevel::ERROR, 'Graph returned an error: '.$e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            exit;
        } catch (FacebookSDKException $e) {
            $this->logger->log(LogLevel::ERROR, 'Facebook SDK returned an error: '.$e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            exit;
        }
    }
}
