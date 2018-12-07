<?php

namespace Pdir\SocialFeedBundle\Module;

use Contao\ModuleNewsList;

class ModuleCustomNewslist extends ModuleNewsList
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_newslist';

    protected function compile()
    {
        parent::compile();

        if (TL_MODE == 'FE') {
            $GLOBALS['TL_CSS'][] = "bundles/pdirsocialfeed/font-awesome/css/font-awesome.min.css";
        }
        $this->Template->sfMasonry = $this->pdir_sf_enableMasonry;
        $this->Template->sfColumns = " ".$this->pdir_sf_columns;
    }
}