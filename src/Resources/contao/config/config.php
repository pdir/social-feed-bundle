<?php

use Pdir\SocialFeedBundle\Module\ModuleCustomNewslist;

/**
 * Backend modules
 */
if (!isset($GLOBALS['BE_MOD']['pdir']) || !is_array($GLOBALS['BE_MOD']['pdir']))
{
    array_insert($GLOBALS['BE_MOD'], 1, array('pdir' => array()));
}

$GLOBALS['TL_HOOKS']['parseArticles'][] = array('Pdir\SocialFeedBundle\SocialFeed\SocialFeedNewsClass', 'parseNews');

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
$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\EventListener\CronListener', 'getFbPosts');
$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\EventListener\CronListener', 'getInstagramPosts');
$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\EventListener\CronListener', 'getTwitterPosts');
$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\EventListener\CronListener', 'getLinkedinPosts');

/**
 * CSS for Frontend
 */
if (TL_MODE == 'FE')
{
    $GLOBALS['TL_CSS']['social_feed'] = 'bundles/pdirsocialfeed/css/social_feed.scss|static';
}
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS'][] =  'bundles/pdirsocialfeed/css/sf_moderation.scss|static';
    $GLOBALS['TL_CSS'][] =  'bundles/pdirsocialfeed/css/backend.css|static';
}
