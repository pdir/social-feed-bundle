<?php

namespace Pdir\SocialFeedBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\ModuleNewsList;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('newslist', category: 'news', template: 'mod_newslist', priority: 1)]
class ModuleCustomNewslistController extends ModuleNewsList
{
    public function __construct() {}

    public function __invoke(ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        return new Response($this->generate());
    }

    protected function compile()
    {
        parent::compile();

        $this->Template->sfMasonry = $this->pdir_sf_enableMasonry;
        $this->Template->sfColumns = ' '.$this->pdir_sf_columns;

        // only used if the contao speed bundle is installed and the js_lazyload template is activated (https://github.com/heimrichhannot/contao-speed-bundle)
        $this->Template->lazyload = false;
        $layout = LayoutModel::findByPk($GLOBALS['objPage']->layout);

        if (null !== $layout->scripts && strpos($layout->scripts, 'lazyload')) {
            $this->Template->lazyload = true;
        }

        $GLOBALS['TL_CSS']['social_feed'] = 'bundles/pdirsocialfeed/css/social_feed.min.css|static';
    }
}
