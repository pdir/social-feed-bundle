<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2023 pdir / digital agentur // pdir GmbH
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

use Contao\Backend;
use Contao\Config;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;

/*
 * add global operation
 */
array_insert($GLOBALS['TL_DCA']['tl_news']['list']['global_operations'], 0, [
    'sf_moderate' => [
        'label' => &$GLOBALS['TL_LANG']['tl_news']['sf_moderate'],
        'href' => 'key=moderate',
        'class' => 'header_sf_moderate',
        'icon' => '/bundles/pdirsocialfeed/img/icon_fa_download-solid.svg',
        'attributes' => 'onclick="Backend.getScrollOffset()"',
    ],
]);

/*
 * Add palette to tl_module
 */

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_id'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_id'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => [
        'mandatory' => false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ],
    'sql' => "varchar(128) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_type'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_type'],
    'exclude' => true,
    'filter' => true,
    'sorting' => true,
    'inputType' => 'select',
    'options' => ['Facebook', 'Instagram', 'Twitter'],
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_account'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_account'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => [
        'mandatory' => false,
        'tl_class' => 'w50',
        'decodeEntities' => true,
    ],
    'sql' => "varchar(128) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_account_picture'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_account_picture'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => 'w50 autoheight'],
    'load_callback' => [
        ['tl_news_socialfeed', 'setSingleSrcFlags'],
    ],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA']['tl_news']['fields']['social_feed_config'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['social_feed_config'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => [
        'mandatory' => false,
        'tl_class' => 'w50',
        'readonly' => 'readonly',
    ],
    'sql' => 'int(10) unsigned NULL',
];

class tl_news_socialfeed extends Backend
{
    /**
     * Dynamically add flags to the "singleSRC" field.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function setSingleSrcFlags($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord) {
            switch ($dc->activeRecord->type) {
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

foreach ($GLOBALS['TL_DCA']['tl_news']['palettes'] as $name => $palette) {
    if (!is_string($palette)) {
        continue;
    }

    PaletteManipulator::create()
        ->addLegend('pdir_sf_settings_legend', 'publish_legend', PaletteManipulator::POSITION_AFTER)
        ->addField('social_feed_type', 'pdir_sf_settings_legend', PaletteManipulator::POSITION_APPEND)
        ->addField('social_feed_id', 'social_feed_type', PaletteManipulator::POSITION_AFTER)
        ->addField('social_feed_account', 'social_feed_id', PaletteManipulator::POSITION_AFTER)
        ->addField('social_feed_account_picture', 'social_feed_account', PaletteManipulator::POSITION_AFTER)
        ->applyToPalette($name, 'tl_news')
    ;
}
