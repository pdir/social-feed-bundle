<?php
class SocialFeedRunonce extends \Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->import('Database');
    }
    public function run()
    {
        if ($this->Database->tableExists('tl_news')) {
            if ($this->Database->fieldExists('pdir_sf_fb_id', 'tl_news')) {
                $this->Database->query("ALTER TABLE tl_news CHANGE pdir_sf_fb_id social_feed_id VARCHAR(128) DEFAULT '' NOT NULL");
            }
            if ($this->Database->fieldExists('pdir_sf_fb_account', 'tl_news')) {
                $this->Database->query("ALTER TABLE tl_news CHANGE pdir_sf_fb_account social_feed_account VARCHAR(128) DEFAULT '' NOT NULL");
            }
            if ($this->Database->fieldExists('pdir_sf_fb_account_picture', 'tl_news')) {
                $this->Database->query("ALTER TABLE tl_news CHANGE pdir_sf_fb_account_picture social_feed_account_picture BINARY(16) DEFAULT NULL");
            }
        }
    }
}
$objRunonceJob = new SocialFeedRunonce();
$objRunonceJob->run();
