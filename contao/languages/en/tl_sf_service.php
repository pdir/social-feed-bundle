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

$GLOBALS['TL_LANG']['tl_sf_service']['edit'] = ['', 'Edit'];
$GLOBALS['TL_LANG']['tl_sf_service']['delete'] = ['', 'Delete'];
$GLOBALS['TL_LANG']['tl_sf_service']['show'] = ['', 'Show details'];
$GLOBALS['TL_LANG']['tl_sf_service']['new'] = ['Create new service', 'Create a new service or app.'];

$GLOBALS['TL_LANG']['tl_sf_service']['type_legend'] = 'Social Feed apps & services';
$GLOBALS['TL_LANG']['tl_sf_service']['credentials_legend'] = 'Credentials';
$GLOBALS['TL_LANG']['tl_sf_service']['type'] = ['Type', 'Please select the type first.'];
$GLOBALS['TL_LANG']['tl_sf_service']['name'] = ['Name', 'Enter a name for this app or service. Helpful if you want to connect several apps of the same provider.'];

$GLOBALS['TL_LANG']['tl_sf_service']['appId'] = ['App ID', 'Enter the app ID. For the Instagram unified login this is the Meta app ID (App settings > Basic), for the legacy Instagram login the Instagram app ID.'];
$GLOBALS['TL_LANG']['tl_sf_service']['appSecret'] = ['App secret', 'Enter the app secret. For the Instagram unified login this is the Meta app secret, for the legacy Instagram login the Instagram app secret.'];
$GLOBALS['TL_LANG']['tl_sf_service']['loginType'] = ['Login method', 'Newly created Meta apps only support the unified login. The Instagram login is only available for existing apps.'];
$GLOBALS['TL_LANG']['tl_sf_service']['loginTypes'] = ['instagram_login' => 'Instagram login (existing apps)', 'unified_login' => 'Unified login (Facebook Login for Business)'];
$GLOBALS['TL_LANG']['tl_sf_service']['configId'] = ['Configuration ID', 'The configuration ID from your Meta app under Instagram > API setup with Unified login.'];
$GLOBALS['TL_LANG']['tl_sf_service']['instagramTokenError'] = 'The Instagram access token could not be requested. Please check the system log for details.';
$GLOBALS['TL_LANG']['tl_sf_service']['instagramAccountError'] = 'No Instagram account was shared during login. Please select an Instagram professional account and try again.';

$GLOBALS['TL_LANG']['tl_sf_service']['requestToken'] = ['Generate access token', 'The access token is generated automatically when you save.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiKey'] = ['API key', 'Enter the API key.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiKeySecret'] = ['API key secret', 'Enter the API key secret.'];
$GLOBALS['TL_LANG']['tl_sf_service']['accessTokenSecret'] = ['Access token secret', 'Enter the access token secret.'];
$GLOBALS['TL_LANG']['tl_sf_service']['clientId'] = ['Client ID', 'Enter the client ID of the app.'];
$GLOBALS['TL_LANG']['tl_sf_service']['clientSecret'] = ['Client secret', 'Enter the client secret of the app.'];
$GLOBALS['TL_LANG']['tl_sf_service']['accessToken'] = ['Access token', 'Is stored automatically when you select "Generate access token" and save.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiVersion'] = ['API version', 'Version of the Facebook Graph API.'];
$GLOBALS['TL_LANG']['tl_sf_service']['tokenExpires'] = ['Access token expires on', 'The access token is renewed automatically before it expires. For the Instagram unified login with a system user token this field stays empty, as the token does not expire.'];
$GLOBALS['TL_LANG']['tl_sf_service']['refreshToken'] = ['Refresh token', 'Is stored automatically when the access token is generated.'];
$GLOBALS['TL_LANG']['tl_sf_service']['refreshTokenExpires'] = ['Refresh token expires on', 'Once the refresh token has expired, the access token must be generated again manually.'];
$GLOBALS['TL_LANG']['tl_sf_service']['noteForRefreshTokenMail'] = ['Note for refresh token email', 'Specify which domain or note should be shown in the refresh token email.'];

$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoService'] = 'No service specified';
$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoType'] = 'No type specified';
$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelSearchTerm'] = '(Search term <span style="color:#999;">%s</span>)';
$GLOBALS['TL_LANG']['tl_sf_service']['setupWelcome'] = 'Social Feed Bundle';
$GLOBALS['TL_LANG']['tl_sf_service']['setupLinks'] = <<<HTML
<p>The SocialFeedBundle is sponsored by the <a href="https://pdir.de/" target="_blank">pdir / digital agentur</a></p>
<h2>Interesting links</h2>
<ul class="link-list">
  <li><a href="https://pdir.de/docs/de/contao/extensions/socialfeed/" target="_blank" style="text-decoration: underline;">Documentation</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/issues" target="_blank" style="text-decoration: underline;">Report problems</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/" target="_blank" style="text-decoration: underline;">Github</a></li>
  <li><a href="https://contao-themes.net/sponsoring.html?isorc=3" target="_blank" style="text-decoration: underline;">Sponsorship / Support</a></li>
</ul>
HTML;

$GLOBALS['TL_LANG']['tl_sf_service']['setupHeadline'] = 'Welcome to the Social Feed Bundle for Contao';
$GLOBALS['TL_LANG']['tl_sf_service']['setupDesc'] = 'The Social Feed extension displays a feed from the most popular social networks. Currently Facebook, Instagram, X (formerly Twitter) and LinkedIn are supported. The extension can be used free of charge. There is a paid Plus version to publish posts to all social media at once or to do this manually.';
$GLOBALS['TL_LANG']['tl_sf_service']['setupPlus'] = <<<HTML
  <h3>Social Feed<span class="high-plus"></span> Version</h3>
    <ul class="benefit">
      <li>Publish to multiple social media channels at once</li>
      <li>Automatically publish scheduled and normal posts</li>
      <li>Customize the text in the preview before publishing</li>
      <li>Support for tags that are automatically inserted at the end</li>
      <li>Quick help in case of problems via our ticket system</li>
    </ul>
    <p><br><a href="https://pdir.de/socialfeed+?mtm_campaign=ExtensionSetup&mtm_content=en" target="_blank" class="tl_submit">Learn more</a></p>
HTML;
$GLOBALS['TL_LANG']['tl_sf_service']['setupFooter'] = 'Your team of <img src="/bundles/pdirsocialfeed/img/pdir_logo.svg" width="50" alt="pdir logo" style="vertical-align: sub;"> / digital agency';
