Social Feed extension for Contao
============================================================

[![Latest Stable Version](https://poser.pugx.org/pdir/social-feed-bundle/v/stable)](https://packagist.org/packages/pdir/social-feed-bundle)
[![Total Downloads](https://poser.pugx.org/pdir/social-feed-bundle/downloads)](https://packagist.org/packages/pdir/social-feed-bundle)
[![License](https://poser.pugx.org/pdir/social-feed-bundle/license)](https://packagist.org/packages/pdir/social-feed-bundle)

Social Feed+ version
-------------------------

| [![Social Feed Plus](https://pdir.de/assets/images/f/pdir_icon_socialfeed_plus-0c93e4f1.svg)](https://pdir.de/socialfeed+) | With the paid Social Feed+ version, you can publish your news, events or regular pages to all available social media channels at once or do it manually with one click. Use Contao's own on-board tools to schedule your posts for Facebook, X ehemals Twitter, Instagram or LinkedIn and publish them automatically to all connected channels 10 minutes after publishing the news, event or a subpage. |
|:--------------------------------------------------------------------------------------------------------------------------:|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
|                                                                                                                            |                                                                                                                                                [**Screenshots**](https://pdir.de/socialfeed+#screenshots) <br> [Kaufen](https://pdir.de/socialfeed+#buy)                                                                                                                                                 |

About the free version
-----

The Social Feed Extension shows a user feed from the most popular social
networks (Facebook, Instagram, X formerly known as Twitter and LinkedIn). The posts are written directly
into the database, created as news and can then displayed on the website
using the module type news list. Since version 2.5.0 modaration of posts
in news archive for instagram is available.

**Deutsch**

Die Social Feed Erweiterung zeigt einen Feed aus den beliebtesten sozialen
Netzwerken an. Zurzeit werden Facebook, Instagram, X ehemals Twitter und LinkedIn unterstützt,
weitere Kanäle folgen in Zukunft. Die Posts werden direkt in die Datenbank
geschrieben, als News angelegt und können anschließend mit dem Modultyp
Nachrichtenliste auf der Webseite angezeigt werden. Seit Version 2.5.0
kannst du Instagram Beiträge direkt im News Archiv auswählen und entscheiden
welche Beiträge importiert werden sollen.


Auf [contao-themes.net](https://contao-themes.net/sponsoring.html?isorc=3) können Sie die Weiterentwicklung unserer Themes und Erweiterungen durch das Kaufen von speziellen Paketen oder das Spenden von Entwicklungsstunden unterstützen.


Screenshot
-----------
![Social Feed Stream](https://pdir.de/files/pdir/01_inhalte/social_feed_demo.png "Social Feed Stream Example")

![Moderate Instagram](https://pdir.de/files/pdir/01_inhalte/moderiere-instagram-im-backend.png "Moderate Instagram Example")

System requirements
-------------------

* PHP 8.1 or higher
* [Contao](https://github.com/contao/contao-bundle) 5.3 or higher (Contao 4.13 is supported up to version 2.13)

Installation & Configuration
----------------------------
* [Dokumentation](https://pdir.de/docs/de/contao/extensions/socialfeed/)

Upgrade to version 3.0
----------------------------

Version 3.0 splits the configuration into **services** (app credentials such as App ID, App Secret and access token, table `tl_sf_service`) and **accounts** (import settings such as news archive, cron job and number of posts, table `tl_sf_account`). Both are managed in the backend under *pdir Apps → Social Feed services*.

1. Make a database backup.
2. Update the bundle with `composer require pdir/social-feed-bundle:^3.0`.
3. Run the migration with `php vendor/bin/contao-console contao:migrate` (or the Contao Manager / install tool). Each `tl_social_feed` record is migrated to a service and an account. Accounts keep their ID if possible, otherwise the imported news are reassigned. X (Twitter) accounts get the type `X`.
4. Check the migrated services and accounts in the backend. The old table `tl_social_feed` is kept and can be deleted manually once everything works.
5. Instagram: accounts created by a new login (e.g. with the unified login, which creates all shared Instagram accounts) have no news archive yet. Select a news archive, otherwise nothing is imported.

Breaking changes for developers: `SocialFeedModel` and `SocialFeedListener` have been removed. Use `SocialFeedServiceModel` and `SocialFeedAccountModel` (`$account->getService()`, `$account->getAccessToken()`) instead. The import commands now import all accounts of a type, also accounts with a cron job.

**Deutsch**

Ab Version 3.0 ist die Konfiguration aufgeteilt in **Dienste** (Zugangsdaten wie App-ID, App-Secret und Access Token, Tabelle `tl_sf_service`) und **Konten** (Importeinstellungen wie Nachrichtenarchiv, Cronjob und Anzahl der Beiträge, Tabelle `tl_sf_account`). Beides verwaltest du im Backend unter *pdir Apps → Social Feed Dienste*.

1. Datenbank sichern.
2. Bundle mit `composer require pdir/social-feed-bundle:^3.0` aktualisieren.
3. Migration mit `php vendor/bin/contao-console contao:migrate` ausführen (oder über Contao Manager / Installtool). Jeder Datensatz aus `tl_social_feed` wird zu einem Dienst und einem Konto. Konten behalten nach Möglichkeit ihre ID, sonst werden die importierten Nachrichten umgehängt. X-(Twitter-)Konten erhalten den Typ `X`.
4. Migrierte Dienste und Konten im Backend prüfen. Die alte Tabelle `tl_social_feed` bleibt erhalten und kann gelöscht werden, sobald alles funktioniert.
5. Instagram: Konten, die durch einen neuen Login angelegt werden (z. B. beim Unified Login, der alle freigegebenen Instagram-Konten anlegt), haben noch kein Nachrichtenarchiv. Wähle eines aus, sonst wird nichts importiert.

Änderungen für Entwickler: `SocialFeedModel` und `SocialFeedListener` wurden entfernt. Verwende stattdessen `SocialFeedServiceModel` und `SocialFeedAccountModel` (`$account->getService()`, `$account->getAccessToken()`). Die Import-Befehle importieren jetzt alle Konten eines Typs, auch Konten mit Cronjob.

Commands
----------------------------

    php vendor/bin/contao-console social-feed:facebook:import
    php vendor/bin/contao-console social-feed:instagram:import
    php vendor/bin/contao-console social-feed:x:import
    php vendor/bin/contao-console social-feed:linkedin:import
    php vendor/bin/contao-console social-feed:linkedin:import -d true -m 30


Demo
----------------------------
* [Social Feed Demo](https://demo.pdir.de/social-feed.html)

Dependencies
------------

- [nickdnk/graph-sdk](https://github.com/nickdnk/php-graph-sdk)
- [abraham/twitteroauth](https://github.com/abraham/twitteroauth)
- [guzzlehttp/guzzle](https://github.com/guzzle/guzzle)
- [zoonman/linkedin-api-php-client](https://github.com/zoonman/linkedin-api-php-client)

License
-------
GNU Lesser General Public License v3.0
Font Awesome Free License

See LICENSE files in package root.

Developing & Pull Request
-------

Run the PHP-CS-Fixer and the unit tests before you make a pull request to the bundle:

    vendor/bin/ecs check src tests --ansi
    vendor/bin/phpunit
    vendor/bin/phpstan analyse --no-progress --ansi
