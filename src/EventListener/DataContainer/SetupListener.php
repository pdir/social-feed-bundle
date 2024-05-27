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

namespace Pdir\SocialFeedBundle\EventListener\DataContainer;

use Contao\BackendTemplate;
use Contao\DataContainer;
use Safe\Exceptions\StringsException;

class SetupListener
{
    /**
     * social-feed-bundle version.
     */
    public const VERSION = '2.13.0';

    /**
     * Template.
     */
    protected string $strTemplate = 'be_socialfeed_setup';

    /**
     * On generate the label.
     *
     * @throws StringsException
     */
    public function onGenerateLabel(array $row): string
    {
        $account = '';

        // set account type
        switch ($row['socialFeedType']) {
            case 'Facebook':
                $account = $row['pdir_sf_fb_account'];
                break;

            case 'Instagram':
                $account = $row['instagram_account'];
                break;

            case 'Twitter':
                $account = $row['twitter_account'];
                break;

            case 'LinkedIn':
                $account = $row['linkedin_company_id'];
                break;
        }

        // no account is selected
        if (empty($account)) {
            $account = $GLOBALS['TL_LANG']['tl_social_feed']['psfLabelNoAccount'];
        }

        if (!empty($row['search'])) {
            $row['search'] = sprintf($GLOBALS['TL_LANG']['tl_social_feed']['psfLabelSearchTerm'], $row['search']);
        }

        return sprintf(
            '%s &rarr; %s %s',
            $row['socialFeedType'] ?: $GLOBALS['TL_LANG']['tl_social_feed']['psfLabelNoType'],
            $account,
            $row['search']
        );
    }

    public function renderFooter(DataContainer $dc): string
    {
        // add setupExplanation
        return $this->setupExplanation($dc);
    }

    /**
     * Gets the setup explanation.
     */
    public function setupExplanation(DataContainer $dc): string
    {
        $template = new BackendTemplate($this->strTemplate);
        $template->version = self::VERSION;

        return $template->parse();
    }
}
