<?php

/*
 * Instagram Bundle for Contao Open Source CMS.
 *
 * Copyright (C) 2011-2019 Codefog
 *
 * @author  Codefog <https://codefog.pl>
 * @author  Kamil Kuzminski <https://github.com/qzminski>
 * @license MIT
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
     * @param Filesystem $fs
     * @param int $cacheTtl
     * @param string $projectDir
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
