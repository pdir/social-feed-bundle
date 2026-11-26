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

namespace Pdir\SocialFeedBundle\Importer;

use Contao\Dbafs;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\System;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;

class NewsImporter
{
    public $accountImage;
    protected $arrNews;

    public function execute($newsArchiveId, SocialFeedAccountModel $socialFeedAccount): void
    {
        $objNews = new NewsModel();

        // check if news exists
        if (null !== $objNews->findBy('social_feed_id', $this->arrNews['id'])) {
            return;
        }

        $objNews->pid = $newsArchiveId;

        // social feed
        $objNews->social_feed_type = $socialFeedAccount->type;
        $objNews->social_feed_id = $this->arrNews['id'];
        $objNews->social_feed_config = $socialFeedAccount->id;

        // post image
        $objNews->singleSRC = $this->arrNews['singleSRC'];

        if (!empty($objNews->singleSRC)) {
            $objNews->addImage = 1;
        }

        // account image
        // $accountPicturePath = $imgPath . $socialFeedAccount->id . '.jpg';
        // $accountPictureUuid = $this->saveImage($accountPicturePath, $this->accountImage);
        // $objNews->social_feed_account_picture = $accountPictureUuid;

        // headline and teaser
        $objNews->headline = $this->arrNews['headline'];

        // set headline to id if headline is not set
        if ('' === $objNews->headline) {
            $objNews->headline = $this->arrNews['id'];
        }

        $objNews->teaser = $this->arrNews['teaser'];

        // author
        $objNews->author = $socialFeedAccount->author;

        // default
        $objNews->published = 1;
        $objNews->source = 'external';
        $objNews->target = 1;
        $objNews->url = $this->arrNews['permalink'];
        $objNews->tstamp = time();
        $objNews->date = $this->arrNews['date'];
        $objNews->time = $this->arrNews['time'];
        $objNews->alias = $this->generateAlias($objNews->headline, $objNews->pid);

        if (null !== NewsModel::findOneByAlias($objNews->alias)) {
            $objNews->alias .= '-'.$this->arrNews['id'];
        }

        // save the news
        $objNews->save();
    }

    public function setNews($arr): void
    {
        $this->arrNews = $arr;
    }

    public static function createImageFolder($account): string
    {
        // Create Public Image Folder
        $imgPath = 'files/social-feed/'.$account.'/';

        if (!self::fileExists($imgPath)) {
            new Folder($imgPath);
        }

        // only write the .public file once, rewriting it on every import is not necessary
        if (!self::fileExists('files/social-feed/.public')) {
            $file = new File('files/social-feed/.public');
            $file->write('');
            $file->close();
        }

        return $imgPath;
    }

    /**
     * Links Instagram hashtags (#tag) and mentions (@user) in a text (#179).
     */
    public static function linkInstagramHashtags(string $text): string
    {
        // hashtags, not preceded by a word character or "&" (HTML entities like &#39;)
        $text = preg_replace(
            '/(?<![\w&\/])#([\p{L}\p{N}_]+)/u',
            '<a href="https://www.instagram.com/explore/tags/$1/" target="_blank" rel="noreferrer noopener">#$1</a>',
            $text,
        ) ?? $text;

        // mentions, not part of an e-mail address
        return preg_replace(
            '/(?<![\w.\/])@([A-Za-z0-9._]{1,30})(?<!\.)/u',
            '<a href="https://www.instagram.com/$1/" target="_blank" rel="noreferrer noopener">@$1</a>',
            $text,
        ) ?? $text;
    }

    /**
     * Checks a path relative to the project directory.
     *
     * file_exists() with a relative path depends on the current working directory, which is
     * public/ in web requests (e.g. the poor man's cron), so the check always failed there.
     */
    public static function fileExists(string $path): bool
    {
        return file_exists(System::getContainer()->getParameter('kernel.project_dir').'/'.ltrim($path, '/'));
    }

    public static function saveImage($strPath, $strUrl): ?string
    {
        if (!self::fileExists($strPath)) {
            $strImage = $strUrl ? @file_get_contents($strUrl) : false;

            // posts without media_url (e.g. videos with copyrighted music) or broken images
            if (false === $strImage || '' === $strImage) {
                return null;
            }

            $file = new File($strPath);
            $file->write($strImage);
            $file->close();

            // add resource
            $objFile = Dbafs::addResource($file->path);

            return $objFile->uuid;
        }

        // use existing file
        $objFile = FilesModel::findByPath($strPath) ?? Dbafs::addResource($strPath);

        return $objFile->uuid;
    }

    public function generateAlias($headline, $newsArchiveId)
    {
        return System::getContainer()->get('contao.slug')->generate($headline, NewsArchiveModel::findById($newsArchiveId)->jumpTo);
    }

    public static function shortenHeadline($str): string
    {
        if (empty($str)) {
            return '';
        }

        $arr = explode("\n", $str);

        $message = $arr[0] ?? $str;
        $more = '';

        if (\strlen($message) > 50) {
            $more = ' ...';
        }

        return mb_substr($message, 0, 50).$more;
    }

    public static function setLastImportDate(SocialFeedAccountModel $socialFeedAccount): void
    {
        $socialFeedAccount->lastImport = time();
        $socialFeedAccount->save();
    }
}
