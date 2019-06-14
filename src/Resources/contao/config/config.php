<?php

use Pdir\SocialFeedBundle\Module\ModuleCustomNewslist;

/**
 * Add back end modules
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

$GLOBALS['TL_MODELS']['tl_social_feed'] = 'Pdir\SocialFeedBundle\Model\SocialFeedModel';

$GLOBALS['FE_MOD']['news']['newslist'] = ModuleCustomNewslist::class;

$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\NewsListener\CronListener', 'getFbPosts');
$GLOBALS['TL_CRON']['minutely'][] = array('Pdir\SocialFeedBundle\NewsListener\CronListener', 'getInstagramPosts');

/**
 * CSS for Frontend
 */
if (TL_MODE == 'FE')
{
    $GLOBALS['TL_CSS'][] =  'bundles/pdirsocialfeed/css/social_feed.scss||static';
}