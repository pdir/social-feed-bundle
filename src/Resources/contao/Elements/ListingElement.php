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
            // return '';
        }

        return parent::generate();
    }

    /**
     * Generate module
     */
    protected function compile()
    {
        if (version_compare(VERSION, '4', '>='))
        {
            $assetsDir = 'web/bundles/pdirsocialfeed';
        } else {
            $assetsDir = 'system/modules/socialFeed/assets';
        }

        if(!$this->pdir_md_removeModuleJs)
        {
            $GLOBALS['TL_FOOTER']['sf_js_1'] = $assetsDir . '/vendor/social-feed-gh-pages/js/jquery.socialfeed.js|static';
        }
        if(!$this->pdir_md_removeModuleCss)
        {
			$GLOBALS['TL_CSS']['md_css_1'] = $assetsDir . '/vendor/social-feed-gh-pages/css/jquery.socialfeed.css||static';
        }

		// Shuffle
        $this->Template->listShuffle = ($this->pdir_sf_list_shuffle) ? 'true' : 'false';

        $this->Template->fbStatus = $this->pdir_sf_facebook_status;
        $this->Template->fbToken = $this->pdir_sf_facebook_token;
        $this->Template->fbAccounts = $this->pdir_sf_facebook_accounts;
        $this->Template->fbLimit = $this->pdir_sf_facebook_limit;

        $this->Template->gpStatus = $this->pdir_sf_google_plus_status;
        $this->Template->gpToken = $this->pdir_sf_google_plus_token;
        $this->Template->gpAccounts = $this->pdir_sf_google_plus_accounts;
        $this->Template->gpLimit = $this->pdir_sf_google_plus_limit;

        // Debug mode
		if($this->pdir_sf_enableDebugMode)
		{
			$this->Template->debug = true;
			$this->Template->version = SocialFeedSetup::VERSION;
			$this->Template->customer = $this->pdir_sf_facebook_id;
		}
    }
}