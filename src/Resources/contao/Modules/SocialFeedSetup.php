<?php

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