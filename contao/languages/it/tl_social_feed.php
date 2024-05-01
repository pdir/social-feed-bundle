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

$GLOBALS['TL_LANG']['tl_social_feed']['edit'] = ['Modifica', 'Modifica'];
$GLOBALS['TL_LANG']['tl_social_feed']['delete'] = ['Elimina', 'Elimina'];
$GLOBALS['TL_LANG']['tl_social_feed']['show'] = ['Visualizza', 'Visualizza le informazioni'];
$GLOBALS['TL_LANG']['tl_social_feed']['new'] = ['Crea un nuovo account di Social Feed', ''];

$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_type_legend'] = 'Configurazione Social Feed';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_account_image_legend'] = 'Configurazione dell\'immagine del profilo';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_legend'] = 'Configurazione Facebook';
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_account'] = ['Account Facebook', "Inserisci il nome della pagina Facebook così com'è nell'URL (es. contao)."];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_app_id'] = ['App ID', "Inserisci qui l'ID app della tua app Facebook."];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_app_secret'] = ['App Secret', 'Inserisci la chiave segreta della tua app di Facebook.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_access_token'] = ['Access Token', 'Inserisci il token di accesso. Le istruzioni per la generazione del token di accesso sono disponibili nella documentazione.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_facebookRequestToken'] = ['Genera token di accesso', 'Quando salvi, il token di accesso viene generato automaticamente.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_archive'] = ['Archivio News', 'Seleziona l\'archivio delle news dove devono essere importati i post.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_cronjob'] = ['Esecuzione del cron job', 'Inserisci la frequenza con cui deve essere richiamato il cron job per importare i post.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_news_last_import_date'] = ['Ultima importazione (data): verrà compilata automaticamente', 'Questo campo viene compilato automaticamente e non deve essere compilato.'];
$GLOBALS['TL_LANG']['tl_social_feed']['pdir_sf_fb_posts'] = ['Importa solo i post dalla pagina', 'Se questa opzione è attivata, verranno importati solo i post sulla pagina e non i post che gli utenti hanno pubblicato sulla tua bacheca.'];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account'] = ['Nome Profilo', "Immettere il nome dell'account che dovrebbe essere visualizzato sul sito Web. Il nome dell'account non è richiesto per l'importazione"];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account_picture'] = ['Foto del profilo', "Seleziona qui un'immagine del profilo, che verrà visualizzata sul sito web."];
$GLOBALS['TL_LANG']['tl_social_feed']['instagram_account_picture_size'] = ["Dimensione dell'immagine", "Qui puoi impostare le dimensioni dell'immagine e la modalità di ridimensionamento."];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAppId'] = ['Instagram App ID', 'Inserisci l\'ID app di Instagram.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAppSecret'] = ['Instagram App Secret', 'Inserisci la chiave segreta dell\'app Instagram.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramAccessToken'] = ['Instagram Access Token', 'Questo è un valore generato automaticamente che viene generato quando il modulo viene inviato.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramRequestToken'] = ['Richiedi token di accesso', 'Attiva questa opzione per richiedere un nuovo token di accesso Instagram.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psf_instagramImportMentions'] = ['Importa menzioni', 'Abilita questa opzione per importare le menzioni.'];
$GLOBALS['TL_LANG']['tl_social_feed']['socialFeedType'] = ['Tipologia Account', 'Selezionare il tipo di account.'];
$GLOBALS['TL_LANG']['tl_social_feed']['no_cronjob'] = 'Nessun Cronjob';
$GLOBALS['TL_LANG']['tl_social_feed']['minutely'] = 'Ogni minuto';
$GLOBALS['TL_LANG']['tl_social_feed']['hourly'] = 'Ogni ora';
$GLOBALS['TL_LANG']['tl_social_feed']['daily'] = 'Ogni giorno';
$GLOBALS['TL_LANG']['tl_social_feed']['monthly'] = 'Ogni mese';
$GLOBALS['TL_LANG']['tl_social_feed']['weekly'] = 'Ogni settimana';
$GLOBALS['TL_LANG']['tl_social_feed']['number_posts'] = ['Numero massimo di post', 'Inserisci qui il numero massimo di post da importare.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_account'] = ['Account Twitter', 'Inserisci qui il nome del tuo account Twitter (senza @).'];
$GLOBALS['TL_LANG']['tl_social_feed']['search'] = ['Termine di ricerca', "Invece di cercare un account specifico, puoi anche cercare un termine di ricerca. Se vengono specificati l'account e il termine di ricerca, l'account viene recuperato e il termine di ricerca viene cercato nei tweet."];
$GLOBALS['TL_LANG']['tl_social_feed']['show_retweets'] = ['Importa anche i retweet', 'Se questa opzione è attivata, vengono importati anche i retweet.'];
$GLOBALS['TL_LANG']['tl_social_feed']['show_reply'] = ['Importa anche le risposte', 'Se questa opzione è attivata, verranno importate anche le risposte.'];
$GLOBALS['TL_LANG']['tl_social_feed']['hashtags_link'] = ['Link hashtag e menzioni', 'Se questa opzione è attivata, gli hashtag e le menzioni sono collegati. A tale scopo è necessario utilizzare il modello esteso.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_api_key'] = ['API Key', 'Inserisci qui la API Key.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_api_secret_key'] = ['API Secret Key', "Inserisci qui la chiave segreta dell'API."];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_access_token'] = ['Access Token', 'Inserisci qui il token di accesso.'];
$GLOBALS['TL_LANG']['tl_social_feed']['twitter_access_token_secret'] = ['Access Token Secret', "Inserisci qui l\\'Access Token Secret."];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_client_id'] = ['Client ID', "Inserisci qui l'ID cliente dall'app."];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_client_secret'] = ['Client Secret', "Inserisci qui il client secret dall'app."];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_company_id'] = ['ID sito aziendale', "Inserisci qui l'ID del sito aziendale."];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_access_token'] = ['Access Token', 'Il token di accesso viene impostato automaticamente dopo aver salvato e impostato la casella di controllo.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_request_token'] = ['Genera access token', 'Se selezioni la casella e poi salvi, viene generato il token di accesso.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_account_picture'] = ['Immagine del profilo', "Seleziona qui un'immagine del profilo, che verrà visualizzata sul sito web."];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_account_picture_size'] = ["Dimensione dell'immagine", "
Qui puoi impostare le dimensioni dell'immagine e la modalità di ridimensionamento."];
$GLOBALS['TL_LANG']['tl_social_feed']['access_token_expires'] = ['Access Token scade il', 'Il gettone di accesso viene rinnovato automaticamente.'];
$GLOBALS['TL_LANG']['tl_social_feed']['linkedin_refresh_token_expires'] = ['Refresh Token scade il', 'Se il token di aggiornamento è scaduto, il token di accesso deve essere rigenerato manualmente.'];
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelSearchTerm'] = '(Termine di ricerca <span style="color:#999;">%s</span>)';
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelNoAccount'] = 'Nessun account specificato';
$GLOBALS['TL_LANG']['tl_social_feed']['psfLabelNoType'] = 'Nessun tipo specificato';
$GLOBALS['TL_LANG']['tl_social_feed']['user'] = ['Utente', 'Specificare qui quale utente deve essere impostato per i nuovi messaggi importati.'];

$GLOBALS['TL_LANG']['tl_social_feed']['setupWelcome'] = 'Social Feed Bundle';
$GLOBALS['TL_LANG']['tl_social_feed']['setupLinks'] = <<<HTML
<p>Il Social Feed Bundle è sponsorizzato da <a href="http://www.pdir.de/" target="_blank">pdir / digital agentur</a></p>
<h2>Link interessanti</h2>
<ul class="link-list">
  <li><a href="https://pdir.de/docs/de/contao/extensions/socialfeed/" target="_blank" style="text-decoration: underline;">Documentazione</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/issues" target="_blank" style="text-decoration: underline;">Segnala problemi</a></li>
  <li><a href="https://github.com/pdir/social-feed-bundle/" target="_blank" style="text-decoration: underline;">Github</a></li>
  <li><a href="https://contao-themes.net/sponsoring.html?isorc=3" target="_blank" style="text-decoration: underline;">Sponsorizzazione / Supporto</a></li>
</ul>
HTML;

$GLOBALS['TL_LANG']['tl_social_feed']['setupHeadline'] = 'Benvenuti al Social Feed Bundle per Contao';
$GLOBALS['TL_LANG']['tl_social_feed']['setupDesc'] = 'L\'estensione Social Feed visualizza un feed dai social network più popolari. Attualmente sono supportati Facebook, Instagram, Twitter e LinkedIn. L\'estensione può essere utilizzata gratuitamente. Esiste una versione Plus a pagamento che consente di pubblicare i post su tutti i social media contemporaneamente o di farlo manualmente.';
$GLOBALS['TL_LANG']['tl_social_feed']['setupPlus'] = <<<HTML
  <h3>Versione Social Feed<span class="high-plus"></span></h3>
    <ul class="benefit">
      <li>Pubblicazione su più canali di social media contemporaneamente</li>
      <li>Pubblicazione automatica di post programmati e normali</li>
      <li>Anteprima e regolazione del testo prima della pubblicazione</li>
      <li>Supporto per i tag che vengono inseriti automaticamente alla fine dei post</li>
      <li>Assistenza rapida in caso di problemi tramite il nostro sistema di ticket</li>
    </ul>
    <p><br><span class="price">99,- € inkl. MwSt.</span> <a href="https://pdir.de/socialfeed+?mtm_campaign=ExtensionSetup&mtm_content=it" target="_blank" class="tl_submit">Acquista ora</a></p>
HTML;
$GLOBALS['TL_LANG']['tl_social_feed']['setupFooter'] = 'Il vostro team a <img src="/bundles/pdirsocialfeed/img/pdir_logo.svg" width="50" alt="pdir logo" style="vertical-align: sub;"> / agenzia digitale';
