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

namespace Pdir\SocialFeedBundle\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Pdir\SocialFeedBundle\Importer\LinkedIn;

#[AsCronJob('minutely')]
class LinkedInImportCron
{
    public function __construct(private ContaoFramework $framework)
    {
    }
    public function __invoke(): void
    {
        $this->framework->initialize();

        // run LinkedIn import
        $importer = new LinkedIn();
        $importer->setPoorManCronMode(true);
        $importer->import();
    }
}
