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
