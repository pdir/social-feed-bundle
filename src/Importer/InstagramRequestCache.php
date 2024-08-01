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

namespace Pdir\SocialFeedBundle\Importer;

use Symfony\Component\Filesystem\Filesystem;

class InstagramRequestCache
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var int
     */
    private $cacheTtl;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * InstagramRequestCache constructor.
     */
    public function __construct(Filesystem $fs, int $cacheTtl, string $projectDir)
    {
        $this->fs = $fs;
        $this->cacheTtl = $cacheTtl;
        $this->projectDir = $projectDir;
    }

    /**
     * Get the cache dir.
     */
    public function getCacheDir(int $socialFeedId = null): ?string
    {
        return $this->projectDir.'/var/cache/instagram/'.($socialFeedId ?? '_');
    }

    /**
     * Get the cache TTL.
     */
    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    /**
     * Purge the cache dir.
     */
    public function purge(string $dir): void
    {
        $this->fs->remove($dir);
    }
}
