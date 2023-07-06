<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2023 pdir / digital agentur // pdir GmbH
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

class SocialFeedRunonce extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->import('Database');
    }

    public function run(): void
    {
        if ($this->Database->tableExists('tl_news')) {
            if ($this->Database->fieldExists('pdir_sf_fb_id', 'tl_news')) {
                $this->Database->query("ALTER TABLE tl_news CHANGE pdir_sf_fb_id social_feed_id VARCHAR(128) DEFAULT '' NOT NULL");
            }

            if ($this->Database->fieldExists('pdir_sf_fb_account', 'tl_news')) {
                $this->Database->query("ALTER TABLE tl_news CHANGE pdir_sf_fb_account social_feed_account VARCHAR(128) DEFAULT '' NOT NULL");
            }

            if ($this->Database->fieldExists('pdir_sf_fb_account_picture', 'tl_news')) {
                $this->Database->query('ALTER TABLE tl_news CHANGE pdir_sf_fb_account_picture social_feed_account_picture BINARY(16) DEFAULT NULL');
            }
        }
    }
}
$objRunonceJob = new SocialFeedRunonce();
$objRunonceJob->run();
