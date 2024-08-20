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

$GLOBALS['TL_LANG']['tl_social_feed']['edit'] = ['', 'Edit'];
$GLOBALS['TL_LANG']['tl_social_feed']['delete'] = ['', 'Delete'];
$GLOBALS['TL_LANG']['tl_social_feed']['show'] = ['', 'Show details'];
$GLOBALS['TL_LANG']['tl_social_feed']['new'] = ['Create new social feed account', ''];

$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_type_legend'] = 'Social Feed Configuration';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_account_image_legend'] = 'Account Picture Configuration';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_legend'] = 'Facebook Configuration';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_account'] = ['Facebook Account', 'Please enter the facebook acount name (from the URL).'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_app_id'] = ['App ID', 'Please enter the facebook app ID.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_app_secret'] = ['App Secret', 'Please enter the facebook app secret.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_access_token'] = ['Access Token', 'The Access Token is automatically stored when you select and save Generate Access Token. When you save, the Access Token is generated automatically.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_facebookRequestToken'] = ['Access Token generieren', 'When you save, the Access Token is generated automatically.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_archive'] = ['News archive', 'Please enter the news archive to which the facebook posts should be imported.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_cronjob'] = ['Execution of the cronjob', 'Please choose the time how often the cronjob should be called to import facebook posts.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_last_import_date'] = ['Last import (date) - will be filled out automatically', 'This field will be filled out automatically and you should not fill it out.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_posts'] = ['Import only own posts', 'If this option is activated, only the posts of the page will be imported and not the posts that users have written to your feed.'];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account'] = ['Account name', 'Here you can enter a account name which will displayed on the website.'];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account_picture'] = ['Account picture', 'Here you can choose a account picture which will displayed on the website.'];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account_picture_size'] = ['Image size', 'Here you can set the image dimensions and the resize mode.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAppId'] = ['Instagram App ID', 'Please enter the Instagram App ID.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAppSecret'] = ['Instagram App Secret', 'Please enter the Instagram App Secret.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAccessToken'] = ['Instagram access token', 'This is an auto-generated value that will be filled in when you submit the form.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramRequestToken'] = ['Request access token and update feed', 'Check this box and save the record to request the access token and update the feed.'];

$GLOBALS['TL_LANG']['tl_social_feed']['socialFeedType'] = ['Type', 'Please choose here the typ of the social feed.'];
$GLOBALS['TL_LANG']['tl_social_feed']['no_cronjob'] = 'no cronjob';
$GLOBALS['TL_LANG']['tl_social_feed']['minutely'] = 'minutely';
$GLOBALS['TL_LANG']['tl_social_feed']['hourly'] = 'hourly';
$GLOBALS['TL_LANG']['tl_social_feed']['daily'] = 'daily';
$GLOBALS['TL_LANG']['tl_social_feed']['weekly'] = 'weekly';
$GLOBALS['TL_LANG']['tl_social_feed']['monthly'] = 'monthly';
$GLOBALS['TL_LANG']['tl_social_feed']['number_posts'] = ['Maximum number of posts', 'Enter the maximum number of posts to be imported here.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_account'] = ['Twitter account', 'Please enter the twitter acount name (without @).'];
$GLOBALS['TL_LANG']['tl_social_feed']['search'] = ['Search Term', 'Instead of looking for a specific account, you can also search for a search term. If account and search term are specified, the account is retrieved and the search term is searched for in the tweets.'];
$GLOBALS['TL_LANG']['tl_social_feed']['show_retweets'] = ['Import retweets', 'If this option is activated also retweets will be imported.'];
$GLOBALS['TL_LANG']['tl_social_feed']['show_reply'] = ['Import reply', 'If this option is activated also reply will be imported.'];
$GLOBALS['TL_LANG']['tl_social_feed']['hashtags_link'] = ['Link hashtags and mentions', 'If this option is activated the hashtags and mentions will be linked. The extended-template should be used for this feature.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_api_key'] = ['API Key', 'Please enter the api key.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_api_secret_key'] = ['Please enter the api secret key.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_access_token'] = ['Access Token', 'Please enter the access token.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_access_token_secret'] = ['Access Token Secret', 'Please enter the access token secret.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_client_id'] = ['Client ID', 'Please enter the client id from your app.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_client_secret'] = ['Client Secret', 'Please enter the client secret from your app.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_company_id'] = ['Company Page ID', 'Please enter the company page id.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_access_token'] = ['Access Token', 'This is an auto-generated value that will be filled in when you submit the form.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_request_token'] = ['Generate Access Token', 'Check this box and save the record to request the access token.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_account_picture'] = ['Account picture', 'Here you can choose a account picture which will displayed on the website.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_account_picture_size'] = ['Image size', 'Here you can set the image dimensions and the resize mode.'];
$GLOBALS['TL_LANG']['tl_social_feed']['access_token_expires'] = ['Access token expires in', 'The Access Token renews automatically.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_refresh_token_expires'] = ['Refresh token expires in', 'If the refresh token has expired, the access token must be regenerated manually.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelSearchTerm'] = '(Search term <span style="color:#999;">%s</span>)';
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelNoAccount'] = 'No account specified';
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelNoType'] = 'No type specified';
$GLOBALS['TL_LANG']['tl_social_feed']['user'] = ['User', 'Please specify here which user should be set for newly imported messages'];


$GLOBALS['TL_LANG']['tl_social_feed']['setupWelcome'] = 'Social Feed Bundle';
$GLOBALS['TL_LANG']['tl_social_feed']['setupLinks'] = <<<HTML
<p>The SocialFeedBundle is sponsored by the <a href="https://pdir.de/" target="_blank">pdir / digital agentur</a></p>
<h2>Interesting links</h2>
<ul class="link-list">
  <li><a href="https://pdir.de/docs/de/contao/extensions/socialfeed/" target="_blank" style="text-decoration: underline;">Documentation</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/issues" target="_blank" style="text-decoration: underline;">Report problems</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/" target="_blank" style="text-decoration: underline;">Github</a></li>
  <li><a href="https://contao-themes.net/sponsoring.html?isorc=3" target="_blank" style="text-decoration: underline;">Sponsorship / Support</a></li>
</ul>
HTML;

$GLOBALS['TL_LANG']['tl_social_feed']['setupHeadline'] = 'Welcome to the Social Feed Bundle for Contao';
$GLOBALS['TL_LANG']['tl_social_feed']['setupDesc'] = 'The Social Feed extension displays a feed from the most popular social networks. Currently Facebook, Instagram, Twitter and LinkedIn are supported. The extension can be used free of charge. There is a paid Plus version to publish posts to all social media at once or do it manually.';
$GLOBALS['TL_LANG']['tl_social_feed']['setupPlus'] = <<<HTML
  <h3>Social Feed<span class="high-plus"></span> Version</h3>
    <ul class="benefit">
      <li>Publish to multiple social media channels at once</li>
      <li>Automatically publish scheduled and normal posts</li>
      <li>Customize the text in the preview before publishing</li>
      <li>Support for tags that are automatically inserted at the end</li>
      <li>Quick help in case of problems via our ticket system</li>
    </ul>
    <p><br><span class="price">99,- €</span> <a href="https://pdir.de/socialfeed+" target="_blank" class="tl_submit">Buy now</a></p>
HTML;
$GLOBALS['TL_LANG']['tl_social_feed']['setupFooter'] = 'Your team of <img src="/bundles/pdirsocialfeed/img/pdir_logo.svg" width="50" alt="pdir logo" style="vertical-align: sub;"> / digital agentur';
