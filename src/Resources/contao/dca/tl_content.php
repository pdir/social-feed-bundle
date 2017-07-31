<?php

/**
 * mobilede for Contao Open Source CMS
 *
 * Copyright (C) 2017 pdir / digital agentur <develop@pdir.de>
 *
 * @package    mobilede
 * @link       https://pdir.de/mobilede
 * @license    pdir license - All-rights-reserved - commercial extension
 * @author     pdir GmbH <develop@pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Add palette to tl_content
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['socialFeedList'] = '{type_legend},type,headline;{sf_settings_legend},pdir_sf_facebook_username,pdir_sf_facebook_password,pdir_sf_facebook_id,pdir_sf_listTemplate,{sf_filters_legend},pdir_sf_hideFilters,pdir_sf_list_shuffle;{sf_template_legend},pdir_sf_removeModuleCss,pdir_sf_removeModuleJs;{sf_debug_legend},pdir_sf_cacheTime,pdir_sf_enableDebugMode;{expert_legend:hide},cssID,space';

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

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_facebook_id'] = array
(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_facebook_id'],
	'exclude' => true,
	'inputType' => 'text',
	'eval' => array(
		'mandatory'=>true,
		'tl_class' => 'w50',
		'decodeEntities' => true,
	),
	'sql' => "varchar(64) NOT NULL default 'demo'",
);

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
		'tl_class' => 'w50 m12',
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

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_facebook_username'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_facebook_username'],
	'exclude'                 => true,
	'search'                  => true,
	'sorting'                 => true,
	'flag'                    => 1,
	'inputType'               => 'text',
	'eval'                    => array(
		'mandatory'=>true,
		'rgxp'=>'extnd',
		'nospace'=>true,
		'maxlength'=>64,
		'tl_class' => 'w50 m12'),
	'sql'                     => "varchar(64) NOT NULL default 'demo'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['pdir_sf_facebook_password'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['pdir_sf_facebook_password'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array(
		'mandatory'=>true,
		'tl_class' => 'w50 m12'
	),
	'sql'                     => "varchar(128) NOT NULL default 'demo'"
);

class pdir_sf_content extends Backend
{

	public function getListTemplates(DataContainer $dc)
	{
		return $this->getElementsTemplates($dc);
	}

	public function getItemTemplates(DataContainer $dc)
	{
		return $this->getElementsTemplates($dc, 'item');
	}

	private function getElementsTemplates(DataContainer $dc, $strTmpl = 'list')
	{
		return $this->getTemplateGroup('ce_socialfeed_' . $strTmpl);
		if (version_compare(VERSION . BUILD, '2.9.0', '>='))
		{
			return $this->getTemplateGroup('ce_socialfeed_' . $strTmpl, $dc->activeRecord->pid);
		}
	}
}