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

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;

System::loadLanguageFile('tl_sf_account');

/*
 * add Dca
 */
$GLOBALS['TL_DCA']['tl_sf_account'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'tstamp' => 'index',
                'service' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['type', 'name'],
            'panelLayout' => 'filter;sort,search,limit',
            'defaultSearchField' => 'name',
        ],
        'label' => [
            'fields' => ['type', 'name', 'identifier'],
            'format' => '<span style="color:#999;padding-right:3px">[%s]</span> %s <span style="color:#999">(%s)</span>',
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{title_legend},type,service,name,identifier',
        'Facebook' => '{title_legend},type,service,name,identifier,accessToken;{import_legend},newsArchive,author,importPostsOnly,cronjob,lastImport;{avatar_legend},avatar,avatarSize',
        'Instagram' => '{title_legend},type,service,name,identifier,accessToken;{import_legend},newsArchive,author,numberPosts,importMentions,hashtagsLink,cronjob,lastImport;{avatar_legend},avatar,avatarSize',
        'X' => '{title_legend},type,service,name,identifier;{import_legend},newsArchive,author,search,numberPosts,showRetweets,showReplies,hashtagsLink,cronjob,lastImport;{avatar_legend},avatar,avatarSize',
        'LinkedIn' => '{title_legend},type,service,name,identifier;{import_legend},newsArchive,author,numberPosts,importSharedContent,cronjob,lastImport;{avatar_legend},avatar,avatarSize',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sorting' => true,
            'flag' => DataContainer::SORT_DAY_DESC,
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        // id of the migrated tl_social_feed record
        'legacyId' => [
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'added' => [
            'sorting' => true,
            'flag' => DataContainer::SORT_DAY_DESC,
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'type' => [
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'options' => ['Facebook', 'Instagram', 'X', 'LinkedIn'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "varchar(16) NOT NULL default ''",
        ],
        'service' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => "tl_sf_service.CONCAT(type, ' – ', name)",
            'relation' => ['type' => 'belongsTo', 'load' => 'lazy'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'name' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'identifier' => [
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'accessToken' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'clr long'],
            'sql' => 'text NULL',
        ],
        'newsArchive' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_news_archive.title',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'author' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => "tl_user.CONCAT(name, ' (', id, ')')",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NOT NULL default 0',
        ],
        'numberPosts' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'natural', 'maxlength' => 5, 'tl_class' => 'w50'],
            'sql' => 'int(10) unsigned NOT NULL default 20',
        ],
        'cronjob' => [
            'exclude' => true,
            'inputType' => 'select',
            'options' => ['no_cronjob', '60', '3600', '86400', '604800', '2629800'],
            'reference' => &$GLOBALS['TL_LANG']['tl_sf_account']['cronjobOptions'],
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default 'no_cronjob'",
        ],
        'lastImport' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'importPostsOnly' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'importMentions' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'importSharedContent' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50 m12'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'search' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'showRetweets' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showReplies' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'hashtagsLink' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'avatar' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr w50 autoheight'],
            'sql' => 'binary(16) NULL',
        ],
        'avatarSize' => [
            'exclude' => true,
            'inputType' => 'imageSize',
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
            'options_callback' => static fn () => System::getContainer()->get('contao.image.sizes')->getAllOptions(),
            'sql' => "varchar(128) NOT NULL default ''",
        ],
    ],
];
