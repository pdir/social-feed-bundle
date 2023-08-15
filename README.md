Social Feed extension for Contao 4
============================================================

[![Latest Stable Version](https://poser.pugx.org/pdir/social-feed-bundle/v/stable)](https://packagist.org/packages/pdir/social-feed-bundle)
[![Total Downloads](https://poser.pugx.org/pdir/social-feed-bundle/downloads)](https://packagist.org/packages/pdir/social-feed-bundle)
[![License](https://poser.pugx.org/pdir/social-feed-bundle/license)](https://packagist.org/packages/pdir/social-feed-bundle)

About
-----

The Social Feed Extension shows a user feed from the most popular social
networks (Facebook, Instagram, Twitter and Linkedin). The posts are written directly
into the database, created as news and can then displayed on the website
using the module type news list. Since version 2.5.0 modaration of posts
in news archive for instagram is available.

**Deutsch**

Die Social Feed Erweiterung zeigt einen Feed aus den beliebtesten sozialen
Netzwerken an. Zur Zeit wird Facebook, Instagram, Twitter und LinkedIn unterstützt,
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

* [Contao 4.3](https://github.com/contao/contao-bundle) or higher

Installation & Configuration
----------------------------
* [Dokumentation](https://pdir.de/docs/de/contao/extensions/socialfeed/)

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

Developing & Pull Request
-------

Run the PHP-CS-Fixer and the unit tests before you make a pull request to the bundle:

    vendor/bin/ecs check src tests --ansi
    vendor/bin/phpunit
    vendor/bin/phpstan analyse --no-progress --ansi
