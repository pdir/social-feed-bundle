<?php

\Contao\System::loadLanguageFile('tl_social_feed');

/**
 * add Dca
 */
$GLOBALS['TL_DCA']['tl_social_feed'] = [
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'onsubmit_callback' => [
            ['Pdir\SocialFeedBundle\EventListener\SocialFeedListener', 'onSubmitCallback'],
        ],
        /*'onload_callback' => [
            ['Pdir\\SocialFeedBundle\\Dca\\tl_social_feed', 'renderFooter'],
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
            'fields' => ['socialFeedType, pdir_sf_fb_account', 'instagram_account', 'twitter_account', 'search', 'linkedin_company_id'],
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
        'default' => '{pdir_sf_type_legend},socialFeedType,psf_setup;',
    ],

    'subpalettes' => array
    (
        'socialFeedType_Facebook' => 'pdir_sf_fb_account,pdir_sf_fb_app_id,pdir_sf_fb_app_secret,pdir_sf_fb_access_token,psf_facebookRequestToken,pdir_sf_fb_news_archive,pdir_sf_fb_news_cronjob,pdir_sf_fb_posts,pdir_sf_fb_news_last_import_date,pdir_sf_fb_news_last_import_time',
        'socialFeedType_Instagram' => 'psf_instagramAppId,psf_instagramAppSecret,psf_instagramAccessToken,psf_instagramRequestToken,instagram_account,number_posts,pdir_sf_fb_news_archive,pdir_sf_fb_news_cronjob,pdir_sf_fb_news_last_import_date,pdir_sf_fb_news_last_import_time;{pdir_sf_account_image_legend},instagram_account_picture,instagram_account_picture_size',
        'socialFeedType_Twitter' => 'twitter_api_key,twitter_api_secret_key,twitter_access_token,twitter_access_token_secret,twitter_account,search,number_posts,pdir_sf_fb_news_archive,pdir_sf_fb_news_cronjob,show_retweets,hashtags_link,show_reply,pdir_sf_fb_news_last_import_date,pdir_sf_fb_news_last_import_time',
        'socialFeedType_LinkedIn' => 'linkedin_client_id,linkedin_client_secret,linkedin_company_id,linkedin_request_token,linkedin_access_token,number_posts,pdir_sf_fb_news_archive,pdir_sf_fb_news_cronjob,pdir_sf_fb_news_last_import_date,pdir_sf_fb_news_last_import_time,linkedin_account_picture,linkedin_account_picture_size'
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
            'options'                 => array('Facebook','Instagram','Twitter','LinkedIn'),
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
                'maxlength' => 255,
                'tl_class' => 'w50'
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
                               '604800'     => $GLOBALS['TL_LANG']['tl_social_feed']['weekly'],
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
                'maxlength' => 255,
                'tl_class' => 'w50 clr'
            ],
            'sql' => "text NULL",
        ],

        'instagram_account_picture' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news']['instagram_account_picture'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => array( 'filesOnly'=>true, 'fieldType'=>'radio', 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50 autoheight' ),
            'load_callback' => array
            (
                array('tl_social_feed', 'setSingleSrcFlags')
            ),
            'sql' => "binary(16) NULL"
        ],

        'instagram_account_picture_size' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news']['instagram_account_picture_size'],
            'exclude' => true,
            'inputType'  => 'imageSize',
            'options_callback' => static function ()
            {
                return \Contao\System::getContainer()->get('contao.image.image_sizes')->getAllOptions();
            },
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['rgxp'=>'natural', 'includeBlankOption'=>true, 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'],
            'sql' => "varchar(64) NOT NULL default ''"
        ],

        'psf_instagramAppId' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAppId'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "text NULL",
        ],

        'psf_instagramAppSecret' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAppSecret'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "text NULL",
        ],

        'psf_instagramAccessToken' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAccessToken'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "text NULL",
        ],

        'psf_instagramRequestToken' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramRequestToken'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'doNotSaveEmpty' => true,
                'tl_class' => 'w50 m12'
            ],
            'save_callback' => [
                [\Pdir\SocialFeedBundle\EventListener\SocialFeedListener::class, 'onRequestTokenSave'],
            ],
        ],

        'psf_facebookRequestToken' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['psf_facebookRequestToken'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'doNotSaveEmpty' => true,
                'tl_class' => 'w50 m12'
            ],
            'save_callback' => [
                [\Pdir\SocialFeedBundle\EventListener\SocialFeedListener::class, 'onRequestTokenSave'],
            ],
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
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'search' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['search'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'show_retweets' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['show_retweets'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr'
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],

        'show_reply' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['show_reply'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr'
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],

        'hashtags_link' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['hashtags_link'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr'
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],

        'twitter_api_key' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['twitter_api_key'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'twitter_api_secret_key' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['twitter_api_secret_key'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'twitter_access_token' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['twitter_access_token'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'twitter_access_token_secret' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['twitter_access_token_secret'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'psf_setup' => [
            'exclude' => true,
            'input_field_callback' => ['Pdir\\SocialFeedBundle\\Dca\\tl_social_feed', 'setupExplanation']
        ],

        // LinkedIn
        'linkedin_client_id' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_client_id'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "text NULL",
        ],

        'linkedin_client_secret' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_client_secret'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "text NULL",
        ],

        'linkedin_company_id' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_company_id'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 255,
                'tl_class' => 'w50'
            ],
            'sql' => "text NULL",
        ],

        'linkedin_access_token' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_access_token'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'clr'
            ],
            'sql' => "text NULL",
        ],

        'linkedin_request_token' => [
            'label' => &$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_request_token'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'doNotSaveEmpty' => true,
                'tl_class' => 'clr'
            ],
            'save_callback' => [
                [\Pdir\SocialFeedBundle\EventListener\SocialFeedListener::class, 'onRequestTokenSave'],
            ],
        ],

        'linkedin_account_picture' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news']['linkedin_account_picture'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => array( 'filesOnly'=>true, 'fieldType'=>'radio', 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'clr w50 autoheight' ),
            'load_callback' => array
            (
                array('tl_social_feed', 'setSingleSrcFlags')
            ),
            'sql' => "binary(16) NULL"
        ],

        'linkedin_account_picture_size' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news']['linkedin_account_picture_size'],
            'exclude' => true,
            'inputType'  => 'imageSize',
            'options_callback' => static function ()
            {
                return \Contao\System::getContainer()->get('contao.image.image_sizes')->getAllOptions();
            },
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval' => ['rgxp'=>'natural', 'includeBlankOption'=>true, 'nospace'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'],
            'sql' => "varchar(64) NOT NULL default ''"
        ],
    ],
];

class tl_social_feed extends Backend
{
    /**
     * Dynamically add flags to the "singleSRC" field
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function setSingleSrcFlags($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord)
        {
            switch ($dc->activeRecord->type)
            {
                case 'text':
                case 'hyperlink':
                case 'image':
                case 'accordionSingle':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('validImageTypes');
                    break;
                case 'download':
                    $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['extensions'] = Config::get('allowedDownload');
                    break;
            }
        }
        return $varValue;
    }
}
