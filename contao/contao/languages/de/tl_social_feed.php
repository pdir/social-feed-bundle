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

$GLOBALS['TL_LANG']['tl_social_feed']['edit'] = ['', 'Editieren'];
$GLOBALS['TL_LANG']['tl_social_feed']['delete'] = ['', 'Löschen'];
$GLOBALS['TL_LANG']['tl_social_feed']['show'] = ['', 'Informationen anzeigen'];
$GLOBALS['TL_LANG']['tl_social_feed']['new'] = ['Neuen Social-Feed-Account anlegen', ''];

$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_type_legend'] = 'Social Feed Konfiguration';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_account_image_legend'] = 'Profilbild Konfiguration';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_legend'] = 'Facebook-Konfiguration';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_account'] = ['Facebook-Account', 'Geben Sie hier den Namen der Facebook-Seite an, wie er in der URL steht (z. B. meissen.online).'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_app_id'] = ['App ID', 'Geben Sie hier die App ID Ihrer Facebook-App an.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_app_secret'] = ['App Secret', 'Geben Sie hier den App Secret Ihrer Facebook-App an.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_access_token'] = ['Access Token', 'Der Access Token wird automatisch hinterlegt, wenn Sie Access Token generieren auswählen und speichern.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_facebookRequestToken'] = ['Access Token generieren', 'Wenn Sie speichern wird der Access Token automatisch generiert.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_archive'] = ['News-Archiv', 'Geben Sie hier das News-Archiv an, in welches die Posts importiert werden sollen.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_cronjob'] = ['Ausführung des Cronjobes', 'Geben Sie hier an, wie oft der Cronjob aufgerufen werden soll, um Posts zu importieren.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_last_import_date'] = ['Letzter Import (Datum) - wird automatisch ausgefüllt', 'Dieses Feld wird automatisch ausgefüllt und sollten Sie nicht ausfüllen.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_posts'] = ['Nur Posts der Seite importieren', 'Wenn diese Option aktiviert ist, werden nur die Posts der Seite importiert und keine Posts, die Nutzer an Ihre Pinnwand geschrieben haben.'];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account'] = ['Profilname', 'Geben Sie hier den Namen des Accounts ein, der auf der Webseite angezeigt werden soll. Der Accountname wird für den Import nicht zwingend benötigt.'];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account_picture'] = ['Profilbild', 'Wählen Sie hier ein Profilbild aus, welches auf der Webseite angezeigt wird.'];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account_picture_size'] = ['Bildgröße', 'Hier können Sie die Abmessungen des Bildes und den Skalierungsmodus festlegen.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAppId'] = ['Instagram App ID', 'Bitte geben Sie die Instagram App ID ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAppSecret'] = ['Instagram App Secret', 'Bitte geben Sie das Instagram App Secret ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAccessToken'] = ['Instagram Access Token', 'Dies ist ein automatich erzeugter Wert, welcher beim Abschicken des Formulars generiert wird.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramRequestToken'] = ['Access Token anfordern', 'Aktivieren Sie diese Option um einen neuen Instagram Access Token anzufordern.'];
$GLOBALS['TL_LANG']['tl_social_feed']['socialFeedType'] = ['Typ', 'Wählen Sie hier zuerst den Typ aus.'];
$GLOBALS['TL_LANG']['tl_social_feed']['no_cronjob'] = 'Kein Cronjob';
$GLOBALS['TL_LANG']['tl_social_feed']['minutely'] = 'Minütlich';
$GLOBALS['TL_LANG']['tl_social_feed']['hourly'] = 'Stündlich';
$GLOBALS['TL_LANG']['tl_social_feed']['daily'] = 'Täglich';
$GLOBALS['TL_LANG']['tl_social_feed']['monthly'] = 'Monatlich';
$GLOBALS['TL_LANG']['tl_social_feed']['weekly'] = 'Wöchentlich';
$GLOBALS['TL_LANG']['tl_social_feed']['number_posts'] = ['Maximale Anzahl an Posts', 'Geben Sie hier die maximale Anzahl der Posts an, die importiert werden sollen.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_account'] = ['Twitter-Account', 'Geben Sie hier den Namen Ihres Twitter-Accounts ein (ohne @).'];
$GLOBALS['TL_LANG']['tl_social_feed']['search'] = ['Suchbegriff', 'Statt nach einem bestimmten Account können Sie auch nach einem Suchbegriff suchen. Sind Account und Suchbegriff angegeben, wird der Account abgerufen und in den Tweets nach dem Suchbegriff gesucht.'];
$GLOBALS['TL_LANG']['tl_social_feed']['show_retweets'] = ['Importiere auch Retweets', 'Wenn diese Option aktiviert ist werden auch Retweets importiert.'];
$GLOBALS['TL_LANG']['tl_social_feed']['show_reply'] = ['Importiere auch Antworten', 'Wenn diese Option aktiviert ist werden auch Antworten importiert.'];
$GLOBALS['TL_LANG']['tl_social_feed']['hashtags_link'] = ['Hashtags und Mentions verlinken', 'Wenn diese Option aktiviert ist werden die Hashtags und Mentions verlinkt. Dafür muss das extended-Template verwendet werden.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_api_key'] = ['API Key', 'Geben Sie hier den API Key ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_api_secret_key'] = ['API Secret Key', 'Geben Sie hier den API Secret Key ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_access_token'] = ['Access Token', 'Geben Sie hier den Access Token ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_access_token_secret'] = ['Access Token Secret', 'Geben Sie hier den Access Token Secret ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_client_id'] = ['Client ID', 'Geben Sie hier die Client ID aus der App ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_client_secret'] = ['Client Secret', 'Geben Sie hier den Client Secret aus der App ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_company_id'] = ['ID der Unternehmens-Seite', 'Geben Sie hier die ID der Unternehmens-Seite ein.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_access_token'] = ['Access Token', 'Der Access Token wird nach dem Speichern und Setzen der Checkbox automatisch gesetzt.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_request_token'] = ['Generiere Access Token', 'Wenn Sie die Checkbox setzen und anschließend Speichern wird der Access Token generiert.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_account_picture'] = ['Profilbild', 'Wählen Sie hier ein Profilbild aus, welches auf der Webseite angezeigt wird.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_account_picture_size'] = ['Bildgröße', 'Hier können Sie die Abmessungen des Bildes und den Skalierungsmodus festlegen.'];
$GLOBALS['TL_LANG']['tl_social_feed']['access_token_expires'] = ['Access Token läuft ab am', 'Der Access Token verlängert sich automatisch.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_refresh_token_expires'] = ['Refresh Token läuft ab am', 'Wenn der Refresh Token abgelaufen ist muss der Access Token manuell neu generiert werden.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelSearchTerm'] = '(Suchwort <span style="color:#999;">%s</span>)';
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelNoAccount'] = 'Kein Account angegeben';
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelNoType'] = 'Kein Typ angegeben';
$GLOBALS['TL_LANG']['tl_social_feed']['user'] = ['Benutzer', 'Geben Sie hier bitte an, welcher Benutzer für neu importierte Nachrichten gesetzt werden soll.'];
