<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2026 pdir / digital agentur // pdir GmbH
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

namespace Pdir\SocialFeedBundle\Controller\FrontendModule;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\ModuleNewsList;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('newslist', category: 'news', template: 'mod_newslist', priority: 1)]
class ModuleCustomNewslistController extends ModuleNewsList
{
    public function __construct()
    {
    }

    public function __invoke(ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        return new Response($this->generate());
    }

    protected function compile(): void
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
