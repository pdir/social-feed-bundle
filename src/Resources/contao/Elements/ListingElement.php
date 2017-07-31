<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 pdir / digital agentur
 * @package social-feed-bundle
 * @author Mathias Arzberger <develop@pdir.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace Pdir\SocialFeedBundle;

class ListingElement extends \ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_socialfeed_list';

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### Social Feed LIST ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }

        // Return if there is no facebook id
        if (!$this->pdir_sf_facebook_id) {
            return '';
        }

        return parent::generate();
    }

    /**
     * Generate module
     */
    protected function compile()
    {
		$assetsDir = 'web/bundles/pdirmobilede';

        if(!$this->pdir_md_removeModuleJs)
        {
            $GLOBALS['TL_JAVASCRIPT']['sf_js_1'] = $assetsDir . '/js/jquery.socialfeed.js|static';
        }
        if(!$this->pdir_md_removeModuleCss)
        {
			$GLOBALS['TL_CSS']['md_css_1'] = $assetsDir . '/css/jquery.socialfeed.css||static';
        }

        // Filters

		// Ordering

        // Pagination

        // Limit

		// Shuffle
        $this->Template->listShuffle = ($this->pdir_sf_list_shuffle) ? 'true' : 'false';

        $this->Template->feeds = array("1", "2");

        // Debug mode
		if($this->pdir_sf_enableDebugMode)
		{
			$this->Template->debug = true;
			$this->Template->version = Helper::VERSION;
			$this->Template->customer = $this->pdir_sf_facebook_id;
		}
    }
}