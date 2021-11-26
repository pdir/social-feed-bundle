<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2021 pdir / digital agentur // pdir GmbH
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

use Pdir\SocialFeedBundle\Module\ModuleCustomNewslist;

/*
 * Backend modules
 */
if (!isset($GLOBALS['BE_MOD']['pdir']) || !is_array($GLOBALS['BE_MOD']['pdir'])) {
    array_insert($GLOBALS['BE_MOD'], 1, ['pdir' => []]);
}

$GLOBALS['TL_HOOKS']['parseArticles'][] = ['Pdir\SocialFeedBundle\SocialFeed\SocialFeedNewsClass', 'parseNews'];

$assetsDir = 'bundles/pdirsocialfeed';

$GLOBALS['BE_MOD']['pdir']['socialFeed'] = [
    'tables' => ['tl_social_feed'],
];

$GLOBALS['BE_MOD']['content']['news']['moderate'] = ['pdir_social_feed_moderate.controller', 'run'];

/*
 * Models
 */

$GLOBALS['TL_MODELS']['tl_social_feed'] = 'Pdir\SocialFeedBundle\Model\SocialFeedModel';

/*
 * Frontend modules
 */
$GLOBALS['FE_MOD']['news']['newslist'] = ModuleCustomNewslist::class;

/*
 * Crons
 */
$GLOBALS['TL_CRON']['minutely'][] = ['Pdir\SocialFeedBundle\EventListener\CronListener', 'getFbPosts'];
$GLOBALS['TL_CRON']['minutely'][] = ['Pdir\SocialFeedBundle\EventListener\CronListener', 'getInstagramPosts'];
$GLOBALS['TL_CRON']['minutely'][] = ['Pdir\SocialFeedBundle\EventListener\CronListener', 'getTwitterPosts'];
$GLOBALS['TL_CRON']['minutely'][] = ['Pdir\SocialFeedBundle\EventListener\CronListener', 'getLinkedinPosts'];
$GLOBALS['TL_CRON']['weekly'][] = ['Pdir\SocialFeedBundle\EventListener\CronListener', 'refreshLinkedInAccessToken'];

/*
 * CSS for Frontend
 */
if (TL_MODE === 'FE') {
    $GLOBALS['TL_CSS']['social_feed'] = $assetsDir.'/css/social_feed.scss|static';
}

if (TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = $assetsDir.'/css/sf_moderation.scss|static';
    $GLOBALS['TL_CSS'][] = $assetsDir.'/css/backend.css|static';
}
