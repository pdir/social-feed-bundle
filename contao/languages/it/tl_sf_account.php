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

$GLOBALS['TL_LANG']['tl_sf_account']['edit'] = 'Modifica l\'account ID %s';
$GLOBALS['TL_LANG']['tl_sf_account']['copy'] = 'Duplica l\'account ID %s';
$GLOBALS['TL_LANG']['tl_sf_account']['delete'] = 'Elimina l\'account ID %s';
$GLOBALS['TL_LANG']['tl_sf_account']['show'] = 'Mostra i dettagli dell\'account ID %s';
$GLOBALS['TL_LANG']['tl_sf_account']['new'] = ['Nuovo account', 'Crea un nuovo account di Social Feed.'];

$GLOBALS['TL_LANG']['tl_sf_account']['title_legend'] = 'Account';
$GLOBALS['TL_LANG']['tl_sf_account']['import_legend'] = 'Impostazioni di importazione';
$GLOBALS['TL_LANG']['tl_sf_account']['avatar_legend'] = 'Immagine del profilo';

$GLOBALS['TL_LANG']['tl_sf_account']['type'] = ['Tipo', 'Seleziona prima il tipo.'];
$GLOBALS['TL_LANG']['tl_sf_account']['service'] = ['Servizio o app', 'Seleziona l\'app o il servizio con le credenziali di accesso.'];
$GLOBALS['TL_LANG']['tl_sf_account']['name'] = ['Nome dell\'account', 'Viene letto automaticamente quando l\'account viene collegato tramite il servizio.'];
$GLOBALS['TL_LANG']['tl_sf_account']['identifier'] = ['Identificativo', 'Facebook: ID o nome della pagina, Instagram: ID utente, X: nome dell\'account, LinkedIn: ID dell\'azienda.'];
$GLOBALS['TL_LANG']['tl_sf_account']['accessToken'] = ['Token di accesso', 'Viene impostato automaticamente quando l\'account viene collegato tramite il servizio. Se il campo è vuoto, viene utilizzato il token di accesso del servizio.'];
$GLOBALS['TL_LANG']['tl_sf_account']['newsArchive'] = ['Archivio notizie', 'Seleziona l\'archivio notizie in cui importare i post.'];
$GLOBALS['TL_LANG']['tl_sf_account']['author'] = ['Autore', 'Seleziona l\'utente da impostare come autore delle notizie importate.'];
$GLOBALS['TL_LANG']['tl_sf_account']['numberPosts'] = ['Numero massimo di post', 'Inserisci il numero massimo di post da importare.'];
$GLOBALS['TL_LANG']['tl_sf_account']['cronjob'] = ['Intervallo del cronjob', 'Indica con quale frequenza il cronjob deve importare i post. Senza cronjob l\'importazione avviene tramite il comando da console.'];
$GLOBALS['TL_LANG']['tl_sf_account']['cronjobOptions'] = [
    'no_cronjob' => 'Nessun cronjob',
    '60' => 'Ogni minuto',
    '3600' => 'Ogni ora',
    '86400' => 'Ogni giorno',
    '604800' => 'Ogni settimana',
    '2629800' => 'Ogni mese',
];
$GLOBALS['TL_LANG']['tl_sf_account']['lastImport'] = ['Ultima importazione', 'Viene impostato automaticamente. Svuota il campo per importare subito alla prossima esecuzione del cronjob.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importPostsOnly'] = ['Importa solo i post della pagina', 'Se attivata, vengono importati solo i post della pagina e non i post scritti dagli utenti sulla tua bacheca.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importMentions'] = ['Importa le menzioni', 'Attiva questa opzione per importare le menzioni.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importSharedContent'] = ['Importa i post condivisi', 'Attiva per importare i post condivisi della pagina aziendale.'];
$GLOBALS['TL_LANG']['tl_sf_account']['search'] = ['Termine di ricerca', 'Invece di un account specifico puoi cercare anche un termine. Se sono indicati sia l\'account sia il termine di ricerca, viene recuperato l\'account e i suoi post vengono filtrati in base al termine.'];
$GLOBALS['TL_LANG']['tl_sf_account']['showRetweets'] = ['Importa i repost', 'Se attivata, vengono importati anche i repost.'];
$GLOBALS['TL_LANG']['tl_sf_account']['showReplies'] = ['Importa le risposte', 'Se attivata, vengono importate anche le risposte.'];
$GLOBALS['TL_LANG']['tl_sf_account']['hashtagsLink'] = ['Collega hashtag e menzioni', 'Se attivata, hashtag e menzioni vengono collegati. Per questo deve essere utilizzato il template extended.'];
$GLOBALS['TL_LANG']['tl_sf_account']['avatar'] = ['Immagine del profilo', 'Per Facebook viene impostata automaticamente. Viene mostrata nel front end se il post non ha una propria immagine del profilo.'];
$GLOBALS['TL_LANG']['tl_sf_account']['avatarSize'] = ['Dimensione dell\'immagine del profilo', 'Seleziona la dimensione dell\'immagine del profilo nel front end.'];
