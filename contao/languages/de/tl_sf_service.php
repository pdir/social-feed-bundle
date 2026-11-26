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

$GLOBALS['TL_LANG']['tl_sf_service']['edit'] = ['', 'Editieren'];
$GLOBALS['TL_LANG']['tl_sf_service']['delete'] = ['', 'Löschen'];
$GLOBALS['TL_LANG']['tl_sf_service']['show'] = ['', 'Informationen anzeigen'];
$GLOBALS['TL_LANG']['tl_sf_service']['new'] = ['Neuen Service anlegen', 'Neuen Service oder App anlegen.'];

$GLOBALS['TL_LANG']['tl_sf_service']['type_legend'] = 'Social Feed Apps & Services';
$GLOBALS['TL_LANG']['tl_sf_service']['credentials_legend'] = 'Anmeldeinformationen';
$GLOBALS['TL_LANG']['tl_sf_service']['type'] = ['Typ', 'Wählen Sie hier zuerst den Typ aus.'];
$GLOBALS['TL_LANG']['tl_sf_service']['name'] = ['Name', 'Legen Sie hier den Namen für diese App oder den Service an. Kann hilfreich sein, wenn Sie mehrere Apps vom gleichen Anbieter verbinden wollen.'];

$GLOBALS['TL_LANG']['tl_sf_service']['appId'] = ['App ID', 'Geben Sie hier die App ID an. Beim Instagram Unified Login ist das die Meta App ID (App-Einstellungen > Allgemeines), beim bisherigen Instagram Login die Instagram App ID.'];
$GLOBALS['TL_LANG']['tl_sf_service']['appSecret'] = ['App Secret', 'Geben Sie hier das App Secret an. Beim Instagram Unified Login ist das der Meta App-Geheimcode, beim bisherigen Instagram Login das Instagram App Secret.'];
$GLOBALS['TL_LANG']['tl_sf_service']['loginType'] = ['Login-Verfahren', 'Neu angelegte Meta-Apps unterstützen nur noch den Unified Login. Der Instagram Login steht nur für bestehende Apps zur Verfügung.'];
$GLOBALS['TL_LANG']['tl_sf_service']['loginTypes'] = ['instagram_login' => 'Instagram Login (bestehende Apps)', 'unified_login' => 'Unified Login (Facebook Login for Business)'];
$GLOBALS['TL_LANG']['tl_sf_service']['configId'] = ['Konfigurations-ID', 'Die Konfigurations-ID aus der Meta-App unter Instagram > API-Einrichtung mit Unified Login.'];
$GLOBALS['TL_LANG']['tl_sf_service']['instagramTokenError'] = 'Der Instagram Access Token konnte nicht angefordert werden. Details finden Sie im System-Log.';
$GLOBALS['TL_LANG']['tl_sf_service']['instagramAccountError'] = 'Beim Login wurde kein Instagram-Konto freigegeben. Bitte wählen Sie ein professionelles Instagram-Konto aus und versuchen Sie es erneut.';

$GLOBALS['TL_LANG']['tl_sf_service']['requestToken'] = ['Access Token generieren', 'Speichern Sie zuerst die Zugangsdaten. Die Anmeldung öffnet sich in einem neuen Tab, danach wird der Access Token automatisch hinterlegt.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiKey'] = ['API Key', 'Geben Sie hier den API Key ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiKeySecret'] = ['API Key Secret', 'Geben Sie hier den API Key Secret ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['accessTokenSecret'] = ['Access Token Secret', 'Geben Sie hier den Access Token Secret ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['clientId'] = ['Client ID', 'Geben Sie hier die Client ID aus der App ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['clientSecret'] = ['Client Secret', 'Geben Sie hier den Client Secret aus der App ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['accessToken'] = ['Access Token', 'Wird automatisch hinterlegt, nachdem Sie über „Access Token generieren“ die Anmeldung abgeschlossen haben.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiVersion'] = ['API-Version', 'Version der Facebook Graph API.'];
$GLOBALS['TL_LANG']['tl_sf_service']['tokenExpires'] = ['Access Token läuft ab am', 'Der Access Token wird automatisch vor Ablauf erneuert. Beim Instagram Unified Login mit Systemnutzer-Token bleibt das Feld leer, da der Token nicht abläuft.'];
$GLOBALS['TL_LANG']['tl_sf_service']['refreshToken'] = ['Refresh Token', 'Wird automatisch hinterlegt, wenn der Access Token generiert wird.'];
$GLOBALS['TL_LANG']['tl_sf_service']['refreshTokenExpires'] = ['Refresh Token läuft ab am', 'Wenn der Refresh Token abgelaufen ist, muss der Access Token manuell neu generiert werden.'];
$GLOBALS['TL_LANG']['tl_sf_service']['noteForRefreshTokenMail'] = ['Hinweis für Refresh Token E-Mail', 'Geben Sie hier bitte an, welche Domain oder welcher Hinweis in der Refresh Token E-Mail angezeigt werden soll.'];

$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoService'] = 'Kein Dienst angegeben';
$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoType'] = 'Kein Typ angegeben';
$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelSearchTerm'] = '(Suchbegriff <span style="color:#999;">%s</span>)';
$GLOBALS['TL_LANG']['tl_sf_service']['setupWelcome'] = 'Social Feed Bundle';
$GLOBALS['TL_LANG']['tl_sf_service']['setupLinks'] = <<<HTML
<p>Das SocialFeedBundle ist gesponsert von der <a href="http://www.pdir.de/" target="_blank">pdir / digital agentur</a></p>
<h2>Interessante Links</h2>
<ul class="link-list">
  <li><a href="https://pdir.de/docs/de/contao/extensions/socialfeed/" target="_blank" style="text-decoration: underline;">Dokumentation</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/issues" target="_blank" style="text-decoration: underline;">Probleme melden</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/" target="_blank" style="text-decoration: underline;">Github</a></li>
  <li><a href="https://contao-themes.net/sponsoring.html?isorc=3" target="_blank" style="text-decoration: underline;">Sponsoring / Unterstützen</a></li>
</ul>
HTML;

$GLOBALS['TL_LANG']['tl_sf_service']['setupHeadline'] = 'Willkommen beim Social Feed Bundle für Contao';
$GLOBALS['TL_LANG']['tl_sf_service']['setupDesc'] = 'Die Social Feed Erweiterung zeigt einen Feed aus den beliebtesten sozialen Netzwerken an. Zur Zeit werden Facebook, Instagram, X ehemals Twitter und LinkedIn unterstützt. Die Erweiterung kann kostenfrei genutzt werden. Es gibt eine kostenpflichtige Plus Version um Beiträge in allen sozialen Medien auf einmal zu veröffentlichen oder dies manuell durchzuführen.';
$GLOBALS['TL_LANG']['tl_sf_service']['setupPlus'] = <<<HTML
  <h3>Social Feed<span class="high-plus"></span> Version</h3>
    <ul class="benefit">
      <li>Auf mehreren Social Media Kanälen gleichzeitig veröffentlichen</li>
      <li>Automatisches veröffentlichen von geplanten und normalen Beiträgen</li>
      <li>Anpassen der Texte in der Vorschau bevor die Veröffentlichung erfolgt</li>
      <li>Unterstützung von Tags die automatisch am Ende eingefügt werden</li>
      <li>Schnelle Hilfe bei Problemen über unser Ticketsystem</li>
    </ul>
    <p><br><a href="https://pdir.de/socialfeed+?mtm_campaign=ExtensionSetup&mtm_content=de" target="_blank" class="tl_submit">Jetzt anschauen</a></p>
HTML;
$GLOBALS['TL_LANG']['tl_sf_service']['setupFooter'] = 'Dein Team der <img src="/bundles/pdirsocialfeed/img/pdir_logo.svg" width="50" alt="pdir logo" style="vertical-align: sub;"> / digital agentur';
