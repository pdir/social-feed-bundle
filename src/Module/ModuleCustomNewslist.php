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

        $this->Template->sfMasonry = $this->pdir_sf_enableMasonry;
    }
}