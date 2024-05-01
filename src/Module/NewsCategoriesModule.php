<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
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

namespace Pdir\SocialFeedBundle\Module;

use Codefog\NewsCategoriesBundle\FrontendModule\NewsListModule;
use Contao\LayoutModel;

class NewsCategoriesModule extends NewsListModule
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_newslist';

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
    }
}
