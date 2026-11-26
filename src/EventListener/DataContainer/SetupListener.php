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

namespace Pdir\SocialFeedBundle\EventListener\DataContainer;

use Contao\BackendTemplate;
use Contao\DataContainer;
use Safe\Exceptions\StringsException;

class SetupListener
{
    /**
     * social-feed-bundle version.
     */
    public const VERSION = '2.13.2';

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
        $account = $row['name'] ?? '';

        // no account is selected
        if (empty($account)) {
            $account = $GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoService'];
        }

        if (!empty($row['search'])) {
            $row['search'] = sprintf($GLOBALS['TL_LANG']['tl_sf_service']['psfLabelSearchTerm'], $row['search']);
        }

        return sprintf(
            '%s &rarr; %s',
            $row['type'] ?: $GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoType'],
            $account,
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
