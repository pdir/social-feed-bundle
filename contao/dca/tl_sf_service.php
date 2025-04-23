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

use Contao\BackendUser;
use Contao\DC_Table;
use Contao\System;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SetupListener;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedListener;

System::loadLanguageFile('tl_sf_service');

/*
 * add Dca
 */
$GLOBALS['TL_DCA']['tl_sf_service'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['type'],
            'flag' => 1,
            'panelLayout' => 'sort,search,limit',
        ],

        'label' => [
            'fields' => ['type', 'name', 'apiKey', 'clientId', 'appId'],
            'format' => '%s - %s: %s %s %s'
        ],

        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],

        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],

            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],

            'show' => [
                'href' => 'act=show',
                'icon' => 'show.gif',
                'attributes' => 'style="margin-right: 3px"',
            ],

            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
        ],
    ],

    'palettes' => [
        '__selector__' => ['type'],
        'default' => '{type_legend},name,type,psf_setup;',
    ],

    'subpalettes' => [
        'type_Facebook' => ';{credentials_legend},appId,appSecret,accessToken,requestToken,apiVersion;',
        'type_Instagram' => ';{credentials_legend},appId,appSecret,accessToken,requestToken,noteForRefreshTokenMail,tokenExpires;',
        'type_X' => ';{credentials_legend},apiKey,apiKeySecret,accessToken,accessTokenSecret;',
        'type_LinkedIn' => ';{credentials_legend},clientId,clientSecret,accessToken,requestToken,noteForRefreshTokenMail,tokenExpires',
    ],

    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],

        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'type' => [
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'options' => ['Facebook', 'Instagram', 'X', 'LinkedIn'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'name' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'appId' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'appSecret' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'accessToken' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'accessTokenSecret' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => 'text NULL',
        ],

        'requestToken' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'doNotSaveEmpty' => true,
                'tl_class' => 'w50 m12',
                'submitOnChange'=> true
            ],
        ],

        'apiVersion' => [
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'options' => ['v20.0', 'v19.0', 'v18.0', 'v17.0', 'v16.0'],
            'eval' => ['includeBlankOption' => false, 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],

        'apiKey' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'apiKeySecret' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'clientId' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => 'text NULL',
        ],

        'clientSecret' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => 'text NULL',
        ],

        'tokenExpires' => [
            'exclude' => true,
            'inputType' => 'text',
            'sql' => "varchar(255) NOT NULL default ''",
            'eval' => [
                'rgxp' => 'datim',
                'tl_class' => 'w50',
                'readonly' => 'readonly',
            ],
        ],

        'noteForRefreshTokenMail' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => 'text NULL',
        ],

        'psf_setup' => [
            'exclude' => true,
            'input_field_callback' => [SetupListener::class, 'setupExplanation'],
        ],
    ],
];
