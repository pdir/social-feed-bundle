<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 pdir / digital agentur
 * @package social-feed-bundle
 * @author Mathias Arzberger <develop@pdir.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

/**
 * Namespace
 */
namespace Pdir\SocialFeedBundle;

class SocialFeedSetup extends \BackendModule
{
    /**
     * social-feed-bundle version
     */
    const VERSION = '1.0.0';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_socialfeed_setup';

    /**
     * Generate the module
     * @throws \Exception
     */
    protected function compile()
    {
        $this->Template->version = self::VERSION;
    }
}