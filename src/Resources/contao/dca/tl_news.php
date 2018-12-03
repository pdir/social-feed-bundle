<?php

/**
 * Add palette to tl_module
 */

$GLOBALS['TL_DCA']['tl_news']['palettes']['default'] = str_replace('stop','stop;{pdir_sf_settings_legend},pdir_sf_fb_id,pdir_sf_fb_account,pdir_sf_fb_account_picture', $GLOBALS['TL_DCA']['tl_news']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_news']['fields']['pdir_sf_fb_id'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_news']['pdir_sf_fb_id'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ),
    'sql' => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_news']['fields']['pdir_sf_fb_account'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_news']['pdir_sf_fb_account'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ),
    'sql' => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_news']['fields']['pdir_sf_fb_account_picture'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_news']['pdir_sf_fb_account_picture'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => array( 'filesOnly'=>true, 'fieldType'=>'radio', 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50 autoheight' ),
    'load_callback' => array
    (
        array('tl_news_socialfeed', 'setSingleSrcFlags')
    ),
    'sql' => "binary(16) NULL"
);

class tl_news_socialfeed extends Backend
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