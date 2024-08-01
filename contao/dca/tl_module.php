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

/*
 * Add palette to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['newslist'] = str_replace('cssID', 'cssID;{pdir_sf_settings_legend},pdir_sf_text_length,pdir_sf_columns,pdir_sf_enableMasonry,pdir_sf_enableImages', $GLOBALS['TL_DCA']['tl_module']['palettes']['newslist']);

$GLOBALS['TL_DCA']['tl_module']['fields']['pdir_sf_text_length'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pdir_sf_text_length'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => [
        'submitOnChange' => true,
        'tl_class' => 'w50',
    ],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pdir_sf_enableMasonry'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pdir_sf_enableMasonry'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => [
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pdir_sf_columns'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pdir_sf_columns'],
    'exclude' => true,
    'inputType' => 'select',
    'eval' => [
        'tl_class' => 'w50',
    ],
    'options' => ['column1', 'columns2', 'columns3', 'columns4'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'sql' => "varchar(64) NOT NULL default 'columns3'",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pdir_sf_enableImages'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pdir_sf_enableImages'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => [
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ],
    'sql' => "char(1) NOT NULL default '1'",
];
