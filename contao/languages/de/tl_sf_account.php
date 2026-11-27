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

$GLOBALS['TL_LANG']['tl_sf_account']['edit'] = 'Konto ID %s bearbeiten';
$GLOBALS['TL_LANG']['tl_sf_account']['copy'] = 'Konto ID %s duplizieren';
$GLOBALS['TL_LANG']['tl_sf_account']['delete'] = 'Konto ID %s löschen';
$GLOBALS['TL_LANG']['tl_sf_account']['show'] = 'Details des Kontos ID %s anzeigen';
$GLOBALS['TL_LANG']['tl_sf_account']['new'] = ['Neues Konto', 'Ein neues Social Feed Konto anlegen.'];

$GLOBALS['TL_LANG']['tl_sf_account']['title_legend'] = 'Konto';
$GLOBALS['TL_LANG']['tl_sf_account']['import_legend'] = 'Import-Einstellungen';
$GLOBALS['TL_LANG']['tl_sf_account']['avatar_legend'] = 'Profilbild';

$GLOBALS['TL_LANG']['tl_sf_account']['type'] = ['Typ', 'Wählen Sie hier zuerst den Typ aus.'];
$GLOBALS['TL_LANG']['tl_sf_account']['service'] = ['Service oder App', 'Wählen Sie die App bzw. den Service mit den Zugangsdaten aus.'];
$GLOBALS['TL_LANG']['tl_sf_account']['name'] = ['Name des Kontos', 'Wird automatisch ausgelesen, wenn das Konto über den Service verbunden wird.'];
$GLOBALS['TL_LANG']['tl_sf_account']['identifier'] = ['Kennung', 'Facebook: Seiten-ID oder -Name, Instagram: Benutzer-ID, X: Account-Name, LinkedIn: Unternehmens-ID.'];
$GLOBALS['TL_LANG']['tl_sf_account']['accessToken'] = ['Access Token', 'Wird automatisch gesetzt, wenn das Konto über den Service verbunden wird. Ist das Feld leer, wird der Access Token des Service verwendet.'];
$GLOBALS['TL_LANG']['tl_sf_account']['newsArchive'] = ['Nachrichtenarchiv', 'Geben Sie hier das Nachrichtenarchiv an, in welches die Posts importiert werden sollen.'];
$GLOBALS['TL_LANG']['tl_sf_account']['author'] = ['Autor', 'Geben Sie hier an, welcher Benutzer für neu importierte Nachrichten gesetzt werden soll.'];
$GLOBALS['TL_LANG']['tl_sf_account']['numberPosts'] = ['Maximale Anzahl an Posts', 'Geben Sie hier die maximale Anzahl der Posts an, die importiert werden sollen.'];
$GLOBALS['TL_LANG']['tl_sf_account']['cronjob'] = ['Ausführung des Cronjobs', 'Geben Sie hier an, wie oft der Cronjob aufgerufen werden soll, um Posts zu importieren. Ohne Cronjob erfolgt der Import über den Konsolenbefehl.'];
$GLOBALS['TL_LANG']['tl_sf_account']['cronjobOptions'] = [
    'no_cronjob' => 'Kein Cronjob',
    '60' => 'Minütlich',
    '3600' => 'Stündlich',
    '86400' => 'Täglich',
    '604800' => 'Wöchentlich',
    '2629800' => 'Monatlich',
];
$GLOBALS['TL_LANG']['tl_sf_account']['lastImport'] = ['Letzter Import', 'Wird automatisch gesetzt. Leeren Sie das Feld, um beim nächsten Cronjob sofort zu importieren.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importPostsOnly'] = ['Nur Posts der Seite importieren', 'Wenn diese Option aktiviert ist, werden nur die Posts der Seite importiert und keine Posts, die Nutzer an Ihre Pinnwand geschrieben haben.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importMentions'] = ['Erwähnungen importieren', 'Aktivieren Sie diese Option, um Erwähnungen zu importieren.'];
$GLOBALS['TL_LANG']['tl_sf_account']['importSharedContent'] = ['Geteilte Beiträge importieren', 'Aktivieren, um geteilte Beiträge der Unternehmensseite zu importieren.'];
$GLOBALS['TL_LANG']['tl_sf_account']['search'] = ['Suchbegriff', 'Statt nach einem bestimmten Account können Sie auch nach einem Suchbegriff suchen. Sind Account und Suchbegriff angegeben, wird der Account abgerufen und in den Posts nach dem Suchbegriff gesucht.'];
$GLOBALS['TL_LANG']['tl_sf_account']['showRetweets'] = ['Reposts importieren', 'Wenn diese Option aktiviert ist, werden auch Reposts importiert.'];
$GLOBALS['TL_LANG']['tl_sf_account']['showReplies'] = ['Antworten importieren', 'Wenn diese Option aktiviert ist, werden auch Antworten importiert.'];
$GLOBALS['TL_LANG']['tl_sf_account']['hashtagsLink'] = ['Hashtags und Erwähnungen verlinken', 'Wenn diese Option aktiviert ist, werden Hashtags und Erwähnungen verlinkt. Dafür muss das extended-Template verwendet werden.'];
$GLOBALS['TL_LANG']['tl_sf_account']['avatar'] = ['Profilbild', 'Wird bei Facebook automatisch gesetzt. Wird im Frontend angezeigt, wenn der Post kein eigenes Profilbild enthält.'];
$GLOBALS['TL_LANG']['tl_sf_account']['avatarSize'] = ['Bildgröße des Profilbilds', 'Wählen Sie die Bildgröße für das Profilbild im Frontend aus.'];

/*
 * Dialog "Konto hinzufügen"
 */
$GLOBALS['TL_LANG']['tl_sf_account']['connect'] = [
    'title' => 'Konto hinzufügen',
    'close' => 'Schließen',
    'back' => 'Zurück',
    'loading' => 'Wird geladen …',
    'loadError' => 'Der Dialog konnte nicht geladen werden.',
    'chooseService' => 'Wähle den Dienst, über den das Konto verbunden ist.',
    'noServices' => 'Es ist noch kein Dienst angelegt.',
    'createService' => 'Dienst anlegen',
    'connected' => 'verbunden',
    'selected' => 'ausgewählt',
    'chooseOther' => 'Anderen Dienst wählen',
    'notConnected' => 'nicht verbunden',
    'manualCreate' => 'Konto manuell anlegen',
    'chooseAccounts' => 'Wähle die Konten, die importiert werden sollen.',
    'alreadyAdded' => 'bereits hinzugefügt',
    'noAccounts' => 'Über diesen Dienst sind keine Konten freigegeben. Prüfe die Berechtigungen und melde dich in den Einstellungen des Dienstes erneut an.',
    'connectHint' => 'Melde dich in den Einstellungen des Dienstes an (Access Token anfordern), danach kannst du hier die Konten auswählen.',
    'notConnectedTitle' => 'Der Dienst ist noch nicht verbunden.',
    'connectService' => 'Dienst verbinden',
    'openService' => 'Dienst bearbeiten',
    'error' => 'Die Konten konnten nicht geladen werden. Details stehen im System-Log. Eventuell ist der Access Token abgelaufen, melde dich dann in den Einstellungen des Dienstes erneut an.',
    'manualHint' => 'Gib die Kennung des Kontos ein, danach geht es mit den Importeinstellungen weiter.',
    'identifierX' => 'Account-Name (ohne @)',
    'identifierLinkedIn' => 'Unternehmens-ID',
    'name' => 'Anzeigename (optional)',
    'submit' => 'Konten hinzufügen',
    'continue' => 'Weiter',
    'added' => '%s Konto/Konten hinzugefügt.',
];
