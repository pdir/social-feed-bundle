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

/*
 * add Dca
 */
$GLOBALS['TL_DCA']['tl_sf_account'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'tstamp' => 'index'
            ]
        ]
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['tstamp', 'id'],
            'panelLayout' => 'filter;sort,search,limit',
            'defaultSearchField' => 'name'
        ],
        'label' => [
            'fields' => ['tstamp', 'name', 'type', 'service', 'identifier'],
            'format' => '<span style="color:#999;padding-right:3px">[%s]</span> <span style="padding-right:10px">%s</span>',
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},name,type,account,avatar'
    ],

    // Fields
    'fields' => [
        'id' => [
            'flag' => 12,
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_DAY_DESC,
            'sql' => "int(10) unsigned NOT NULL default 0"
        ],
        'identifier' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'service' => [
            'inputType' => 'select',
            'foreignKey' => 'tl_sf_service.id',
            'eval' => ['tl_class' => 'w50 clr', 'readonly' => 'readonly'],
            'sql' => "int(10) NOT NULL default 0"
        ],
        'type' => [
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'inputType' => 'select',
            'options' => ['facebook', 'instagram', 'x', 'linkedin'],
            'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'submitOnChange' => true],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'avatar' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true],
            'sql' => "binary(16) NULL"
        ],
        'added' => [
            'filter' => true,
            'sorting' => true,
            'sql' => "int(10) unsigned NOT NULL default 0"
        ],
    ]
];
