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

$GLOBALS['TL_LANG']['tl_sf_service']['appId'] = ['App ID', 'Geben Sie hier die App ID an.'];
$GLOBALS['TL_LANG']['tl_sf_service']['appSecret'] = ['App Secret', 'Geben Sie hier den App Secret der App an.'];

$GLOBALS['TL_LANG']['tl_sf_service']['requestToken'] = ['Access Token generieren', 'Wenn Sie speichern wird der Access Token automatisch generiert.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiKey'] = ['API Key', 'Geben Sie hier den API Key ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiSecretKey'] = ['API Key Secret', 'Geben Sie hier den API Key Secret ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['accessTokenSecret'] = ['Access Token Secret', 'Geben Sie hier den Access Token Secret ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['clientId'] = ['Client ID', 'Geben Sie hier die Client ID aus der App ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['clientSecret'] = ['Client Secret', 'Geben Sie hier den Client Secret aus der App ein.'];
$GLOBALS['TL_LANG']['tl_sf_service']['tokenExpires'] = ['Refresh Token läuft ab am', 'Wenn der Refresh Token abgelaufen ist muss der Access Token manuell neu generiert werden.'];
$GLOBALS['TL_LANG']['tl_sf_service']['noteForRefreshTokenMail'] = ['Hinweis für Refresh Token E-Mail', 'Geben Sie hier bitte an, welche Domain oder welcher Hinweis in der Refresh Token E-Mail angezeigt werden soll.'];

$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoService'] = 'Kein Dienst angegeben';
$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoType'] = 'Kein Typ angegeben';
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
