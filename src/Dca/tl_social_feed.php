<?php

namespace Pdir\SocialFeedBundle\Dca;

use Contao\BackendTemplate;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;

class tl_social_feed
{
    /**
     * social-feed-bundle version
     */
    const VERSION = '2.9.1';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_socialfeed_setup';
    /**
     * On generate the label.
     *
     * @param array $row
     *
     * @return string
     */
    public function onGenerateLabel(array $row): string
    {
        if($row['pdir_sf_fb_account'] != "") {
            $account = $row['pdir_sf_fb_account'];
        } else if($row['instagram_account'] != "") {
            $account = $row['instagram_account'];
        } else if($row['twitter_account'] != "") {
            $account = $row['twitter_account'];
        } else if($row['search'] != "") {
            $account = $row['search'];
        } else {
            $account = "Kein Account/Suchbegriff angegeben";
        }

        if($row['socialFeedType'] != "") {
            $type = $row['socialFeedType'];
        } else {
            $type = "Kein Typ angegeben";
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
     * Gets the setup explanation
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
