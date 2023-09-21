<?php

use Contao\ArrayUtil;

$table = 'tl_news_archive';

/*
 * Add global operations
 */
ArrayUtil::arrayInsert($GLOBALS['TL_DCA'][$table]['list']['global_operations'], 0, [
    'socialFeedAccounts' => [
        'label' => &$GLOBALS['TL_LANG']['MSC']['socialFeedAccounts'],
        'href' => 'do=socialFeed',
        'class' => 'header_socialFeedAccounts',
        'icon' => '/bundles/pdirsocialfeed/img/icon_fa_list-solid.svg',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
    ],
]);
