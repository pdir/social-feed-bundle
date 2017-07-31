<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 pdir / digital agentur
 * @package social-feed-bundle
 * @author Mathias Arzberger <develop@pdir.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

/**
 * Add content element
 */
$GLOBALS['TL_CTE']['includes']['socialFeedList'] = 'Pdir\\SocialFeedBundle\\ListingElement';

/**
 * Add back end modules
 */
if (!is_array($GLOBALS['BE_MOD']['pdir']))
{
    array_insert($GLOBALS['BE_MOD'], 1, array('pdir' => array()));
}

$assetsDir = 'bundles/socialfeedbundle';

array_insert($GLOBALS['BE_MOD']['pdir'], 0, array
(
    'socialFeedSetup' => array
    (
        'callback'          => 'Pdir\SocialFeedBundle\SocialFeedSetup',
        'icon'              => $assetsDir . '/img/icon.png',
        //'javascript'        =>  $assetsDir . '/js/backend.min.js',
        'stylesheet'		=>  $assetsDir . '/css/backend.css'
    ),
));

/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 4, array
(
    'pdir' => array
    (
        'socialFeedList'   => 'Pdir\\SocialFeedBundle\\ListingElement'
    )
));