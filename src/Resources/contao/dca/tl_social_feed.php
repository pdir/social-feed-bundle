<?php

$GLOBALS['TL_DCA']['tl_social_feed'] = [
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'onsubmit_callback' => [
            ['Pdir\SocialFeedBundle\NewsListener\CronListener', 'getFbPosts'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['pdir_sf_fb_account'],
            'flag' => 1,
            'panelLayout' => 'sort,search,limit',
        ],

        'label' => [
            'fields' => ['pdir_sf_fb_account', 'pdir_sf_fb_app_id', 'pdir_sf_fb_app_secret', 'pdir_sf_fb_news_archive'],
            'format' => '%s',
        ],

        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],

        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],

            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],

            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
                'attributes' => 'style="margin-right: 3px"',
            ],
        ],
    ],

    'palettes' => [
        'default' => '{list_legend},pdir_sf_fb_account,pdir_sf_fb_app_id,pdir_sf_fb_app_secret,pdir_sf_fb_news_archive',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],

        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'pdir_sf_fb_account' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_account'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'pdir_sf_fb_app_id' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_app_id'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'pdir_sf_fb_app_secret' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_app_secret'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'pdir_sf_fb_news_archive' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_archive'],
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'tl_news_archive.title',
            'sql' => "varchar(64) NOT NULL default ''",
        ],
    ],
];