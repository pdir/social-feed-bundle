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
 * Add palette to tl_content
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['socialfeed'] = '{type_legend},type,headline;{pdir_sf_settings_legend},pdir_sf_listTemplate,pdir_sf_itemTemplate,pdir_sf_text_length,pdir_sf_update_period,pdir_sf_show_media,pdir_sf_media_min_width,pdir_sf_date_format,pdir_sf_date_locale;{pdir_sf_facebook_settings},pdir_sf_facebook_status,pdir_sf_facebook_accounts,pdir_sf_facebook_token,pdir_sf_facebook_limit;{pdir_sf_google_plus_settings},pdir_sf_google_plus_status,pdir_sf_google_plus_accounts,pdir_sf_google_plus_token,pdir_sf_google_plus_limit;{pdir_sf_filters_legend},pdir_sf_hideFilters,pdir_sf_list_shuffle;{pdir_sf_template_legend},pdir_sf_removeModuleCss,pdir_sf_removeModuleJs;{pdir_sf_debug_legend},pdir_sf_cacheTime,pdir_sf_enableDebugMode;{expert_legend:hide},cssID,space';

/**
 * Add fields to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_listTemplate'] = array
(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_listTemplate'],
	'exclude' => true,
	'inputType' => 'select',
	'options_callback' => array('pdir_sf_content', 'getListTemplates'),
	'reference' => &$GLOBALS['TL_LANG']['tl_module'],
	'eval' => array(
		'includeBlankOption' => true,
		'tl_class' => 'w50'
	),
	'sql' => "varchar(32) NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_itemTemplate'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_itemTemplate'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('pdir_sf_content', 'getItemTemplates'),
    'save_callback' => array
    (
        array('pdir_sf_content', 'save_socialfeed_item')
    ),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => array(
        'includeBlankOption' => true,
        'tl_class' => 'w50'
    ),
    'sql' => "varchar(32) NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_text_length'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_text_length'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ),
    'sql' => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_media_min_width'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_media_min_width'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ),
    'sql' => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_show_media'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_show_media'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ),
    'sql' => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_update_period'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_update_period'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ),
    'sql' => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_date_format'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_date_format'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array(
        'mandatory'=>false,
        'tl_class' => 'w50 m12'
    ),
    'sql'                     => "varchar(14) NOT NULL default 'll'"
);


$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_date_locale'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_date_locale'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array(
        'mandatory'=>false,
        'tl_class' => 'w50 m12'
    ),
    'sql'                     => "varchar(3) NOT NULL default 'de'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_facebook_accounts'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_facebook_accounts'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ),
    'sql' => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_facebook_status'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_facebook_status'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ),
    'sql' => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_facebook_token'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_facebook_token'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ),
    'sql' => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_facebook_limit'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_facebook_limit'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50',
    ),
    'sql' => "int(3) unsigned NOT NULL default '20'",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_google_plus_accounts'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_google_plus_accounts'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ),
    'sql' => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_google_plus_status'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_google_plus_status'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50 m12',
    ),
    'sql' => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_google_plus_token'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_google_plus_token'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ),
    'sql' => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_google_plus_limit'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_google_plus_limit'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'submitOnChange' => true,
        'tl_class' => 'w50',
    ),
    'sql' => "int(3) unsigned NOT NULL default '20'",
);

// {pdir_sf_filters_legend},pdir_sf_hideFilters,pdir_sf_list_shuffle;{pdir_sf_template_legend},pdir_sf_removeModuleCss,pdir_sf_removeModuleJs;{pdir_sf_debug_legend},pdir_sf_cacheTime,pdir_sf_enableDebugMode;{expert_legend:hide},cssID,space

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_hideFilters'] = array
(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_hideFilters'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array(
		'submitOnChange' => true,
		'tl_class' => 'w50 m12',
	),
	'sql' => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_list_shuffle'] = array
(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_list_shuffle'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array(
		'submitOnChange' => true,
		'tl_class' => 'w50 m12',
	),
	'sql' => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_removeModuleJs'] = array
(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_removeModuleJs'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array(
		'submitOnChange' => true,
		'tl_class' => 'w50 m12',
	),
	'sql' => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_removeModuleCss'] = array
(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_removeModuleCss'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array(
		'submitOnChange' => true,
		'tl_class' => 'w50 m12',
	),
	'sql' => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_cacheTime'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_cacheTime'],
	'exclude' => true,
	'inputType' => 'text',
	'eval' => array(
		'submitOnChange' => true,
		'tl_class' => 'w50',
	),
	'sql' => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_enableDebugMode'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_enableDebugMode'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array(
		'submitOnChange' => true,
		'tl_class' => 'w50 m12',
	),
	'sql' => "char(1) NOT NULL default ''",
);


class pdir_sf_content extends Backend
{
    public function save_socialfeed_item($strValue, DataContainer $dc)
    {
        // Copy item template to files folder
        $strItemFilename = 'ce_socialfeed_item';
        if($dc->activeRecord->pdir_sf_itemTemplate == '')
        {
            $strItemTemplatePath = '/vendor/pdir/social-feed-bundle/src/Resources/contao/templates/elements/' . $strItemFilename . '.html5';
        }
        else
        {
            // copy from custom
            $strPath = $this->getTemplate($dc->activeRecord->pdir_sf_itemTemplate);
            $arrPath = explode("/templates", $strPath);
            $strItemTemplatePath = '/templates' . $arrPath[1];
            $strItemFilename = $dc->activeRecord->pdir_sf_itemTemplate;
        }

        $objFile = new \File($strItemTemplatePath, true);
        $objFile->copyTo('web/share/' . $strItemFilename . '.html');

        return $strValue;
    }

	public function getListTemplates(DataContainer $dc)
	{
        return $this->getTemplateGroup('ce_' . $dc->activeRecord->type . '_list');
	}

	public function getItemTemplates(DataContainer $dc)
	{
        return $this->getTemplateGroup('ce_' . $dc->activeRecord->type . '_item');
	}
}