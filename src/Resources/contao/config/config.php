<?php

use Pdir\SocialFeedBundle\Module\ModuleCustomNewslist;

/**
 * Backend modules
 */
if (!is_array($GLOBALS['BE_MOD']['pdir']))
{
    array_insert($GLOBALS['BE_MOD'], 1, array('pdir' => array()));
}

$GLOBALS['TL_HOOKS']['parseArticles'][] = array('Pdir\SocialFeedBundle\SocialFeed\SocialFeedNewsClass', 'parseNews');

$assetsDir = 'bundles/pdirsocialfeed';

$GLOBALS['BE_MOD']['pdir']['socialFeed'] = [
    'tables' => ['tl_social_feed']
];

array_insert($GLOBALS['BE_MOD']['pdir'], 0, array
(
    'socialFeedSetup' => array
    (
        'callback'          => 'Pdir\\SocialFeedBundle\\SocialFeedSetup',
        'icon'              => $assetsDir . '/img/icon.png',
        //'javascript'        =>  $assetsDir . '/js/backend.min.js',
        'stylesheet'		=>  $assetsDir . '/css/backend.css'
    ),
    'socialFeed' => array
    (
        'callback'          => 'Pdir\\SocialFeedBundle\\SocialFeed'
    ),
));


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
$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\NewsListener\CronListener', 'getFbPosts');
$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\NewsListener\CronListener', 'getInstagramPosts');
$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\NewsListener\CronListener', 'getTwitterPosts');

/**
 * CSS for Frontend
 */
if (TL_MODE == 'FE')
{
    $GLOBALS['TL_CSS']['social_feed'] = 'bundles/pdirsocialfeed/css/social_feed.scss||static';
}
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/pdirsocialfeed/js/backend.js';
    $GLOBALS['TL_CSS'][] =  'bundles/pdirsocialfeed/css/sf_moderation.scss||static';
}
