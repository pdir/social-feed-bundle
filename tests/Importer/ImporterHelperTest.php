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

namespace Pdir\SocialFeedBundle\Tests\Importer;

use Pdir\SocialFeedBundle\Importer\LinkedIn;
use Pdir\SocialFeedBundle\Importer\NewsImporter;
use PHPUnit\Framework\TestCase;

class ImporterHelperTest extends TestCase
{
    public function testLinksInstagramHashtagsAndMentions(): void
    {
        $text = NewsImporter::linkInstagramHashtags('Sommer #urlaub2026 mit @pdir.de<br>#Ärger');

        $this->assertStringContainsString('<a href="https://www.instagram.com/explore/tags/urlaub2026/" target="_blank" rel="noreferrer noopener">#urlaub2026</a>', $text);
        $this->assertStringContainsString('<a href="https://www.instagram.com/pdir.de/" target="_blank" rel="noreferrer noopener">@pdir.de</a>', $text);
        $this->assertStringContainsString('<br><a href="https://www.instagram.com/explore/tags/Ärger/"', $text);
    }

    public function testDoesNotLinkEntitiesUrlsAndEmails(): void
    {
        $text = 'It&#39;s info@pdir.de https://pdir.de/#anchor';

        $this->assertSame($text, NewsImporter::linkInstagramHashtags($text));
    }

    public function testLinkedInPublishedTimeFallbacks(): void
    {
        $this->assertSame(1700000000, LinkedIn::getPublishedTime(['firstPublishedAt' => 1700000000000]));
        $this->assertSame(1600000000, LinkedIn::getPublishedTime(['created' => ['time' => 1600000000000]]));
        $this->assertSame(1500000000, LinkedIn::getPublishedTime(['firstPublishedAt' => 0, 'lastModified' => ['time' => 1500000000000]]));
        $this->assertEqualsWithDelta(time(), LinkedIn::getPublishedTime([]), 5);
    }
}
