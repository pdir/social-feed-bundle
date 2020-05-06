<?php

namespace Pdir\SocialFeedBundle\Module;

use Contao\ModuleNewsList;
use Contao\LayoutModel;

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

        $this->Template->sfMasonry = $this->pdir_sf_enableMasonry;
        $this->Template->sfColumns = " ".$this->pdir_sf_columns;

        $layout = LayoutModel::findByPk($GLOBALS['objPage']->layout);
        if( strpos($layout->scripts,"lazyload") ) {
            $this->Template->lazyload = true;
        } else {
            $this->Template->lazyload = false;
        }
    }
}