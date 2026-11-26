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

$GLOBALS['TL_LANG']['tl_sf_service']['edit'] = ['', 'Modifica'];
$GLOBALS['TL_LANG']['tl_sf_service']['delete'] = ['', 'Elimina'];
$GLOBALS['TL_LANG']['tl_sf_service']['show'] = ['', 'Visualizza le informazioni'];
$GLOBALS['TL_LANG']['tl_sf_service']['new'] = ['Crea un nuovo servizio', 'Crea un nuovo servizio o una nuova app.'];

$GLOBALS['TL_LANG']['tl_sf_service']['type_legend'] = 'App e servizi di Social Feed';
$GLOBALS['TL_LANG']['tl_sf_service']['credentials_legend'] = 'Credenziali di accesso';
$GLOBALS['TL_LANG']['tl_sf_service']['type'] = ['Tipo', 'Seleziona prima il tipo.'];
$GLOBALS['TL_LANG']['tl_sf_service']['name'] = ['Nome', 'Inserisci un nome per questa app o questo servizio. Utile se vuoi collegare più app dello stesso fornitore.'];

$GLOBALS['TL_LANG']['tl_sf_service']['appId'] = ['App ID', 'Inserisci l\'ID app. Con l\'Unified Login di Instagram è l\'ID app di Meta (Impostazioni app > Di base), con il login Instagram precedente l\'ID app di Instagram.'];
$GLOBALS['TL_LANG']['tl_sf_service']['appSecret'] = ['App secret', 'Inserisci la chiave segreta dell\'app. Con l\'Unified Login di Instagram è la chiave segreta dell\'app Meta, con il login Instagram precedente quella dell\'app Instagram.'];
$GLOBALS['TL_LANG']['tl_sf_service']['loginType'] = ['Metodo di login', 'Le nuove app Meta supportano solo l\'Unified Login. Il login Instagram è disponibile solo per le app esistenti.'];
$GLOBALS['TL_LANG']['tl_sf_service']['loginTypes'] = ['instagram_login' => 'Login Instagram (app esistenti)', 'unified_login' => 'Unified Login (Facebook Login for Business)'];
$GLOBALS['TL_LANG']['tl_sf_service']['configId'] = ['ID configurazione', 'L\'ID configurazione della tua app Meta in Instagram > Configurazione API con Unified Login.'];
$GLOBALS['TL_LANG']['tl_sf_service']['instagramTokenError'] = 'Impossibile richiedere il token di accesso Instagram. Controlla il log di sistema per i dettagli.';
$GLOBALS['TL_LANG']['tl_sf_service']['instagramAccountError'] = 'Durante il login non è stato condiviso alcun account Instagram. Seleziona un account Instagram professionale e riprova.';

$GLOBALS['TL_LANG']['tl_sf_service']['requestToken'] = ['Genera token di accesso', 'Salva prima le credenziali. Il login si apre in una nuova scheda, dopodiché il token di accesso viene salvato automaticamente.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiKey'] = ['API key', 'Inserisci l\'API key.'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiKeySecret'] = ['API key secret', 'Inserisci l\'API key secret.'];
$GLOBALS['TL_LANG']['tl_sf_service']['accessTokenSecret'] = ['Access token secret', 'Inserisci l\'access token secret.'];
$GLOBALS['TL_LANG']['tl_sf_service']['clientId'] = ['Client ID', 'Inserisci il client ID dell\'app.'];
$GLOBALS['TL_LANG']['tl_sf_service']['clientSecret'] = ['Client secret', 'Inserisci il client secret dell\'app.'];
$GLOBALS['TL_LANG']['tl_sf_service']['accessToken'] = ['Token di accesso', 'Viene salvato automaticamente dopo aver completato il login tramite "Genera token di accesso".'];
$GLOBALS['TL_LANG']['tl_sf_service']['apiVersion'] = ['Versione API', 'Versione della Facebook Graph API.'];
$GLOBALS['TL_LANG']['tl_sf_service']['tokenExpires'] = ['Il token di accesso scade il', 'Il token di accesso viene rinnovato automaticamente prima della scadenza. Con l\'Unified Login di Instagram e un token utente di sistema il campo resta vuoto, perché il token non scade.'];
$GLOBALS['TL_LANG']['tl_sf_service']['refreshToken'] = ['Refresh token', 'Viene salvato automaticamente quando viene generato il token di accesso.'];
$GLOBALS['TL_LANG']['tl_sf_service']['refreshTokenExpires'] = ['Il refresh token scade il', 'Dopo la scadenza del refresh token, il token di accesso deve essere generato di nuovo manualmente.'];
$GLOBALS['TL_LANG']['tl_sf_service']['noteForRefreshTokenMail'] = ['Nota per l\'e-mail del refresh token', 'Indica quale dominio o nota deve essere mostrata nell\'e-mail del refresh token.'];

$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoService'] = 'Nessun servizio specificato';
$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelNoType'] = 'Nessun tipo specificato';
$GLOBALS['TL_LANG']['tl_sf_service']['psfLabelSearchTerm'] = '(Termine di ricerca <span style="color:#999;">%s</span>)';
$GLOBALS['TL_LANG']['tl_sf_service']['setupWelcome'] = 'Social Feed Bundle';
$GLOBALS['TL_LANG']['tl_sf_service']['setupLinks'] = <<<HTML
<p>Il Social Feed Bundle è sponsorizzato da <a href="https://pdir.de/" target="_blank">pdir / digital agentur</a></p>
<h2>Link interessanti</h2>
<ul class="link-list">
  <li><a href="https://pdir.de/docs/de/contao/extensions/socialfeed/" target="_blank" style="text-decoration: underline;">Documentazione</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/issues" target="_blank" style="text-decoration: underline;">Segnala problemi</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/" target="_blank" style="text-decoration: underline;">Github</a></li>
  <li><a href="https://contao-themes.net/sponsoring.html?isorc=3" target="_blank" style="text-decoration: underline;">Sponsorizzazione / Supporto</a></li>
</ul>
HTML;

$GLOBALS['TL_LANG']['tl_sf_service']['setupHeadline'] = 'Benvenuti al Social Feed Bundle per Contao';
$GLOBALS['TL_LANG']['tl_sf_service']['setupDesc'] = 'L\'estensione Social Feed visualizza un feed dai social network più popolari. Attualmente sono supportati Facebook, Instagram, X (ex Twitter) e LinkedIn. L\'estensione può essere utilizzata gratuitamente. Esiste una versione Plus a pagamento che consente di pubblicare i post su tutti i social media contemporaneamente o di farlo manualmente.';
$GLOBALS['TL_LANG']['tl_sf_service']['setupPlus'] = <<<HTML
  <h3>Versione Social Feed<span class="high-plus"></span></h3>
    <ul class="benefit">
      <li>Pubblicazione su più canali di social media contemporaneamente</li>
      <li>Pubblicazione automatica di post programmati e normali</li>
      <li>Anteprima e regolazione del testo prima della pubblicazione</li>
      <li>Supporto per i tag che vengono inseriti automaticamente alla fine dei post</li>
      <li>Assistenza rapida in caso di problemi tramite il nostro sistema di ticket</li>
    </ul>
    <p><br><a href="https://pdir.de/socialfeed+?mtm_campaign=ExtensionSetup&mtm_content=it" target="_blank" class="tl_submit">Scopri di più</a></p>
HTML;
$GLOBALS['TL_LANG']['tl_sf_service']['setupFooter'] = 'Il vostro team di <img src="/bundles/pdirsocialfeed/img/pdir_logo.svg" width="50" alt="pdir logo" style="vertical-align: sub;"> / agenzia digitale';
