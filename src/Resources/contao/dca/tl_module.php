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
 * Add palette to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['newslist'] = str_replace('cssID','cssID;{pdir_sf_settings_legend},pdir_sf_text_length,pdir_sf_columns,pdir_sf_enableMasonry,pdir_sf_enableImages', $GLOBALS['TL_DCA']['tl_module']['palettes']['newslist']);

$GLOBALS['TL_DCA']['tl_module']['fields']['pdir_sf_text_length'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pdir_sf_text_length'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50',
    ),
    'sql' => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['pdir_sf_enableMasonry'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pdir_sf_enableMasonry'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ),
    'sql' => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['pdir_sf_columns'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pdir_sf_columns'],
    'exclude' => true,
    'inputType' => 'select',
    'eval' => [
        'tl_class' => 'w50'
    ],
    'options' => array('column1', 'columns2', 'columns3', 'columns4'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'sql' => "varchar(64) NOT NULL default 'columns3'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['pdir_sf_enableImages'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pdir_sf_enableImages'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ),
    'sql' => "char(1) NOT NULL default '1'",
);
