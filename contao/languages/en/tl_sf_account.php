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

$GLOBALS['TL_LANG']['tl_sf_account']['edit'] = 'Edit account ID %s';
$GLOBALS['TL_LANG']['tl_sf_account']['copy'] = 'Duplicate account ID %s';
$GLOBALS['TL_LANG']['tl_sf_account']['delete'] = 'Delete account ID %s';
$GLOBALS['TL_LANG']['tl_sf_account']['show'] = 'Show the details of account ID %s';
$GLOBALS['TL_LANG']['tl_sf_account']['new'] = ['New account', 'Create a new social feed account.'];

$GLOBALS['TL_LANG']['tl_sf_account']['title_legend'] = 'Account';
$GLOBALS['TL_LANG']['tl_sf_account']['import_legend'] = 'Import settings';
$GLOBALS['TL_LANG']['tl_sf_account']['avatar_legend'] = 'Profile picture';

$GLOBALS['TL_LANG']['tl_sf_account']['type'] = ['Type', 'Please select the type first.'];
$GLOBALS['TL_LANG']['tl_sf_account']['service'] = ['Service or app', 'Select the app or service that contains the credentials.'];
$GLOBALS['TL_LANG']['tl_sf_account']['name'] = ['Account name', 'Is read automatically when the account is connected via the service.'];
$GLOBALS['TL_LANG']['tl_sf_account']['identifier'] = ['Identifier', 'Facebook: page ID or name, Instagram: user ID, X: account name, LinkedIn: company ID.'];
$GLOBALS['TL_LANG']['tl_sf_account']['accessToken'] = ['Access token', 'Is set automatically when the account is connected via the service. If the field is empty, the access token of the service is used.'];
$GLOBALS['TL_LANG']['tl_sf_account']['newsArchive'] = ['News archive', 'Select the news archive the posts should be imported into.'];
$GLOBALS['TL_LANG']['tl_sf_account']['author'] = ['Author', 'Select the user who is set as author of newly imported news.'];
$GLOBALS['TL_LANG']['tl_sf_account']['numberPosts'] = ['Maximum number of posts', 'Enter the maximum number of posts to be imported.'];
$GLOBALS['TL_LANG']['tl_sf_account']['cronjob'] = ['Cronjob interval', 'Specify how often the cronjob should import posts. Without a cronjob, the import is done via the console command.'];
$GLOBALS['TL_LANG']['tl_sf_account']['cronjobOptions'] = [
    'no_cronjob' => 'No cronjob',
    '60' => 'Every minute',
    '3600' => 'Hourly',
    '86400' => 'Daily',
    '604800' => 'Weekly',
    '2629800' => 'Monthly',
];
$GLOBALS['TL_LANG']['tl_sf_account']['lastImport'] = ['Last import', 'Is set automatically. Clear the field to import immediately with the next cronjob run.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importPostsOnly'] = ['Only import posts of the page', 'If enabled, only posts of the page are imported and no posts that users have written on your wall.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importMentions'] = ['Import mentions', 'Enable this option to import mentions.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importSharedContent'] = ['Import shared posts', 'Enable to import shared posts of the company page.'];
$GLOBALS['TL_LANG']['tl_sf_account']['search'] = ['Search term', 'Instead of a specific account you can also search for a term. If both account and search term are specified, the account is fetched and its posts are filtered by the search term.'];
$GLOBALS['TL_LANG']['tl_sf_account']['showRetweets'] = ['Import reposts', 'If enabled, reposts are imported as well.'];
$GLOBALS['TL_LANG']['tl_sf_account']['showReplies'] = ['Import replies', 'If enabled, replies are imported as well.'];
$GLOBALS['TL_LANG']['tl_sf_account']['hashtagsLink'] = ['Link hashtags and mentions', 'If enabled, hashtags and mentions are linked. The extended template must be used for this.'];
$GLOBALS['TL_LANG']['tl_sf_account']['avatar'] = ['Profile picture', 'Is set automatically for Facebook. Is shown in the front end if the post has no profile picture of its own.'];
$GLOBALS['TL_LANG']['tl_sf_account']['avatarSize'] = ['Profile picture size', 'Select the image size of the profile picture in the front end.'];

/*
 * Dialog "Add account"
 */
$GLOBALS['TL_LANG']['tl_sf_account']['connect'] = [
    'title' => 'Add account',
    'close' => 'Close',
    'back' => 'Back',
    'loading' => 'Loading …',
    'loadError' => 'The dialog could not be loaded.',
    'chooseService' => 'Choose the service the account is connected with.',
    'noServices' => 'There is no service yet.',
    'createService' => 'Create service',
    'connected' => 'connected',
    'selected' => 'selected',
    'chooseOther' => 'Choose another service',
    'notConnected' => 'not connected',
    'manualCreate' => 'Create account manually',
    'chooseAccounts' => 'Choose the accounts to import.',
    'alreadyAdded' => 'already added',
    'noAccounts' => 'No accounts are shared with this service. Check the permissions and log in again in the service settings.',
    'connectHint' => 'Log in in the service settings (request access token), then you can choose the accounts here.',
    'notConnectedTitle' => 'The service is not connected yet.',
    'connectService' => 'Connect service',
    'openService' => 'Edit service',
    'error' => 'The accounts could not be loaded. See the system log for details. If the access token has expired, log in again in the service settings.',
    'manualHint' => 'Enter the identifier of the account, then continue with the import settings.',
    'identifierX' => 'Account name (without @)',
    'identifierLinkedIn' => 'Organization ID',
    'name' => 'Display name (optional)',
    'submit' => 'Add accounts',
    'continue' => 'Continue',
    'added' => '%s account(s) added.',
];
