<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2021 pdir / digital agentur // pdir GmbH
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

namespace Pdir\SocialFeedBundle\Dca;

use Contao\BackendTemplate;
use Contao\DataContainer;

class tl_social_feed
{
    /**
     * social-feed-bundle version.
     */
    public const VERSION = '2.10.0';

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_socialfeed_setup';

    /**
     * On generate the label.
     */
    public function onGenerateLabel(array $row): string
    {
        if ('' != $row['pdir_sf_fb_account']) {
            $account = $row['pdir_sf_fb_account'];
        } elseif ('' != $row['instagram_account']) {
            $account = $row['instagram_account'];
        } elseif ('' != $row['twitter_account']) {
            $account = $row['twitter_account'];
        } elseif ('' != $row['linkedin_company_id']) {
            $account = $row['linkedin_company_id'];
        } elseif ('' != $row['search']) {
            $account = $row['search'];
        } else {
            $account = 'Kein Account/Suchbegriff angegeben';
        }

        if ('' !== $row['socialFeedType']) {
            $type = $row['socialFeedType'];
        } else {
            $type = 'Kein Typ angegeben';
        }

        return sprintf(
            '%s &rarr; %s',
            $type,
            $account
        );
    }

    public function renderFooter(DataContainer $dc)
    {
        // add setupExplanation
        return $this->setupExplanation($dc);
    }

    /**
     * Gets the setup explanation.
     *
     * @param Contao\DataContainer $dc
     *
     * @return string
     */
    public function setupExplanation(DataContainer $dc)
    {
        $template = new BackendTemplate($this->strTemplate);
        $template->version = self::VERSION;

        return $template->parse();
    }
}
