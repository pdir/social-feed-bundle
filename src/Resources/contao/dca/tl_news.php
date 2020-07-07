<?php

/**
 * add global operation
 */
array_insert($GLOBALS['TL_DCA']['tl_news']['list']['global_operations'], 0, [
    'sf_moderate' => [
        'label' => &$GLOBALS['TL_LANG']['tl_news']['sf_moderate'],
        'href' => 'key=moderate',
        'class' => 'header_new header_sf_moderate',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
        #'button_callback' => ''
    ],
]);

/**
 * Add palette to tl_module
 */

$GLOBALS['TL_DCA']['tl_news']['palettes']['default'] = str_replace('stop','stop;{pdir_sf_settings_legend},social_feed_type,social_feed_id,social_feed_account,social_feed_account_picture,social_feed_config', $GLOBALS['TL_DCA']['tl_news']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_id'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_id'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ),
    'sql' => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_type'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_type'],
    'exclude'                 => true,
    'filter'                  => true,
    'sorting'                 => true,
    'inputType'               => 'select',
    'options'                 => array('Facebook','Instagram','Twitter'),
    'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_account'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_account'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ),
    'sql' => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_account_picture'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_account_picture'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => array( 'filesOnly'=>true, 'fieldType'=>'radio', 'feEditable'=>true, 'feViewable'=>true, 'feGroup'=>'personal', 'tl_class'=>'w50 autoheight' ),
    'load_callback' => array
    (
        array('tl_news_socialfeed', 'setSingleSrcFlags')
    ),
    'sql' => "binary(16) NULL"
);

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_config'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_config'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'mandatory'=>false,
        'tl_class' => 'w50',
        'readonly'=>'readonly'
    ),
    'sql' => "int(10) unsigned NULL",
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
