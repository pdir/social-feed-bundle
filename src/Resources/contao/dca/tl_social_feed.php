<?php

$GLOBALS['TL_DCA']['tl_social_feed'] = [
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        /*'onsubmit_callback' => [
            ['Pdir\SocialFeedBundle\NewsListener\CronListener', 'getFbPosts'],
        ],*/
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['socialFeedType'],
            'flag' => 1,
            'panelLayout' => 'sort,search,limit',
        ],

        'label' => [
            'fields' => ['socialFeedType, pdir_sf_fb_account', 'instagram_account', 'twitter_account'],
            'label_callback' => ['Pdir\\SocialFeedBundle\\Dca\\tl_social_feed', 'onGenerateLabel'],
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
        '__selector__'  => array('socialFeedType'),
        'default' => '{pdir_sf_type_legend},socialFeedType;',
    ],

    'subpalettes' => array
    (
        'socialFeedType_Facebook' => 'pdir_sf_fb_account,pdir_sf_fb_app_id,pdir_sf_fb_app_secret,pdir_sf_fb_access_token,pdir_sf_fb_news_archive,pdir_sf_fb_news_cronjob,pdir_sf_fb_posts,pdir_sf_fb_news_last_import_date,pdir_sf_fb_news_last_import_time',
        'socialFeedType_Instagram' => 'instagram_account,number_posts,pdir_sf_fb_news_archive,pdir_sf_fb_news_cronjob,pdir_sf_fb_news_last_import_date,pdir_sf_fb_news_last_import_time',
        'socialFeedType_Twitter' => 'twitter_account,number_posts,pdir_sf_fb_news_archive,pdir_sf_fb_news_cronjob,pdir_sf_fb_news_last_import_date,pdir_sf_fb_news_last_import_time'
    ),

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],

        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'socialFeedType' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['socialFeedType'],
            'exclude'                 => true,
            'filter'                  => true,
            'sorting'                 => true,
            'inputType'               => 'select',
            'options'                 => array('Facebook','Instagram','Twitter'),
            'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50', 'submitOnChange'     => true),
            'sql'                     => "varchar(255) NOT NULL default ''"
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
                'tl_class' => 'w50'
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
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'pdir_sf_fb_access_token' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_access_token'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'clr'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'pdir_sf_fb_news_archive' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_archive'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'foreignKey' => 'tl_news_archive.title',
            'sql' => "varchar(64) NOT NULL default ''",
        ],

        'pdir_sf_fb_posts' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_posts'],
            'default' => 1,
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr'
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],

        'pdir_sf_fb_news_cronjob' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_cronjob'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => [
                'tl_class' => 'w50'
            ],
            'options' => array('no_cronjob' => $GLOBALS['TL_LANG']['tl_social_feed']['no_cronjob'],
                               '60'   => $GLOBALS['TL_LANG']['tl_social_feed']['minutely'],
                               '3600'     => $GLOBALS['TL_LANG']['tl_social_feed']['hourly'],
                               '86400'      => $GLOBALS['TL_LANG']['tl_social_feed']['daily'],
                               '604800.02'     => $GLOBALS['TL_LANG']['tl_social_feed']['weekly'],
                               '2629800'    => $GLOBALS['TL_LANG']['tl_social_feed']['monthly']),
            'sql' => "varchar(64) NOT NULL default ''",
        ],

        'pdir_sf_fb_news_last_import_date' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_last_import_date'],
            'exclude' => true,
            'inputType' => 'text',
            'sql' => "varchar(255) NOT NULL default ''",
            'eval' => array('rgxp' => 'date', 'tl_class' => 'w50')
        ],

        'pdir_sf_fb_news_last_import_time' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_last_import_time'],
            'exclude' => true,
            'inputType' => 'text',
            'sql' => "varchar(255) NOT NULL default ''",
            'eval' => array('rgxp' => 'time', 'tl_class' => 'w50')
        ],

        'instagram_account' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'number_posts' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['number_posts'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "int(10) unsigned NOT NULL default '20'",
        ],

        'twitter_account' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['twitter_account'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];