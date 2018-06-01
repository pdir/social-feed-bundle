<?php

namespace Pdir\SocialFeedBundle\Module;

use Contao\ModuleNewsList;

class ModuleSocialFeedNews extends ModuleNewsList
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_newslist';

    protected function compile()
    {
        parent::compile();

        $this->Template->textLength = $this->pdir_sf_text_length;
    }
}