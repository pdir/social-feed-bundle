# Changelog

[//]: <> (
Types of changes
    Added for new features.
    Changed for changes in existing functionality.
    Deprecated for soon-to-be removed features.
    Removed for now removed features.
    Fixed for any bug fixes.
    Security in case of vulnerabilities.
)

> [!Tip]
> Thank you to all [EAP](https://pdir.de/crowdfunding/social-feed-bundle.html) supporters!

## [2.13.6](https://github.com/pdir/social-feed-bundle/tree/2.13.6) - 2024-11-21

-[Fixed] Update kevinrob/guzzle-cache-middleware fix #150 🤗 [contaoacademy](https://github.com/contaoacademy)

## [2.12.7](https://github.com/pdir/social-feed-bundle/tree/2.12.7) - 2024-11-15

-[Fixed] issue with array_insert in Contao <4.13 🤗 [koertho](https://github.com/koertho)

## [2.13.5](https://github.com/pdir/social-feed-bundle/tree/2.13.5) - 2024-11-15

- [Fixed] Fix linkedin access token generation

## [2.13.4](https://github.com/pdir/social-feed-bundle/tree/2.13.4) - 2024-08-20

- [Fixed] Fix newslist module (remove void)
- [Fixed] Missing images in moderated instagram import

## [2.13.3](https://github.com/pdir/social-feed-bundle/tree/2.13.3) - 2024-08-07

- [Fixed] missing templates in newslist module

## [2.13.2](https://github.com/pdir/social-feed-bundle/tree/2.13.2) - 2024-08-01

- [Fixed] fix LinkedIn import

## [2.13.1](https://github.com/pdir/social-feed-bundle/tree/2.13.1) - 2024-06-21

- [Added] Activate submit on change for Instagram request token (usability)
- [Fixed] Enable request token in moderate controller
- [Fixed] some import problems

## [2.13.0](https://github.com/pdir/social-feed-bundle/tree/2.13.0) - 2024-05-20

- [Added] Add Contao 5.3 support
- [Changed] Requires Contao 4.13 and PHP 8.0 as minimum requirements
- [Changed] Improvement of usability in back end
- [Fixed] Icons in dark mode
- [Fixed] Remove warnings in cron listener
- [Fixed] Add missing translations (de,en,it)

## [2.12.5](https://github.com/pdir/social-feed-bundle/tree/2.12.5) - 2023-09-20

- [Changed] doctrine/cache from 1.9 to ^2.1
- [Changed] guzzlehttp/guzzle from 6.3 to ^7.3
- [Changed] Change translations and texts
- [Fixed] temporarily supplemented samoritano/linkedin-api-php-client-v2 to support guzzle 7.3

## [2.12.4](https://github.com/pdir/social-feed-bundle/tree/2.12.4) - 2023-07-31

- [Fixed] Fix compatibility with Codefog News Categories bundle
- [Changed] Add system log entries for better and easier debugging [#127](https://github.com/pdir/social-feed-bundle/pull/127) 🤗 [w3scout](https://github.com/w3scout)

## [2.12.3](https://github.com/pdir/social-feed-bundle/tree/2.12.3) - 2023-06-27

- [Fixed] Fix dca to make compatible with ContaoNewsBundle ^4.4 [#94](https://github.com/pdir/social-feed-bundle/issues/94)
- [Fixed] Fix error in Contao 4.4: Attempted to call function "sprintf" from namespace "Safe"

## [2.12.2](https://github.com/pdir/social-feed-bundle/tree/2.12.2) - 2023-06-13

- [Fixed] Fix instagram import if post has no description [#116](https://github.com/pdir/social-feed-bundle/issues/116)

## [2.12.1](https://github.com/pdir/social-feed-bundle/tree/2.12.1) - 2023-06-09

- [Fixed] Fix error if type and access token is empty
- [Fixed] Fix error if twitter access token is invalid or expired
- [Fixed] Fix Instagram import if media_url is null [#109](https://github.com/pdir/social-feed-bundle/issues/109)
- [Fixed] Fix Facebook import if post has no attachments
- [Fixed] Fix Instagram account name is not displayed [#115](https://github.com/pdir/social-feed-bundle/issues/115)

## [2.12.0](https://github.com/pdir/social-feed-bundle/tree/2.12.0) - 2023-02-10

- [Added] refresh instagram access token automatically
- [Changed] Unlock nickdnk/graph-sdk v7 🤗 [rabauss](https://github.com/rabauss)
- [Fixed] Fix type error in cron if message is null 🤗 [rabauss](https://github.com/rabauss)

## [2.11.2](https://github.com/pdir/social-feed-bundle/tree/2.11.2) - 2022-12-18

- [Fixed] fix warning in language files

## [2.11.1](https://github.com/pdir/social-feed-bundle/tree/2.11.1) - 2022-07-18

- [Fixed] fix warning in debug mode

## [2.11.0](https://github.com/pdir/social-feed-bundle/tree/2.11.0) – 2022-07-07

- [Added] add author for new news
- [Fixed] fix facebook import (if teaser or image is null)
- [Fixed] fix twitter import [#83](https://github.com/pdir/social-feed-bundle/issues/83)
- [Fixed] Fix critical errors in facebook import and return error message instead
- [Fixed] Fix compatibility with newest DBAL version

## [2.10.1](https://github.com/pdir/social-feed-bundle/tree/2.10.1) – 2022-02-17

- [Fixed] fix twitter import

## [2.10.0](https://github.com/pdir/social-feed-bundle/tree/2.10.0) – 2021-12-06

- [Added] linkedin import
- [Fixed] show account pictures
- [Fixed] twitter import
- [Removed] remove support for PHP 7.3

## [2.9.3](https://github.com/pdir/social-feed-bundle/tree/2.9.3) – 2021-11-27

- [Fixed] warning in debug mode while moderation

## [2.9.2](https://github.com/pdir/social-feed-bundle/tree/2.9.2) – 2021-09-06

- [Fixed] instagram import
- [Fixed] language loading

## [2.9.1](https://github.com/pdir/social-feed-bundle/tree/2.9.1) – 2021-08-10

- [Fixed] broken account image

## [2.9.0](https://github.com/pdir/social-feed-bundle/tree/2.9.0) – 2021-07-19

- [Added] added select all function in moderation list
- [Added] added php 8 support
- [Added] added italian translation
- [Added] added facebook moderation feature
- [Added] set number of posts when moderating instagram feed
- [Added] generate facebook access token via checkbox
- [Added] replace facebook/graph-sdk with nickdnk/graph-sdk
- [Added] use options callback for image sizes
- [Fixed] fix missing array keys + translations
- [Fixed] twitter link
- [Fixed] fix install with empty database
- [Changed] use PaletteManipulator in dca file

## [2.8.5](https://github.com/pdir/social-feed-bundle/tree/2.8.5) – 2021-03-16

- [Fixed] use mb_substr instead of substr to avoid utf 8 problems

## [2.8.4](https://github.com/pdir/social-feed-bundle/tree/2.8.4) – 2021-01-07

- [Fixed] add instagram access token field in dca

## [2.8.3](https://github.com/pdir/social-feed-bundle/tree/2.8.3) – 2020-10-29

- [Fixed] fix dca palette

## [2.8.2](https://github.com/pdir/social-feed-bundle/tree/2.8.2) – 2020-10-19

- [Fixed] use instagram graph api pagination

## [2.8.1](https://github.com/pdir/social-feed-bundle/tree/2.8.1) – 2020-09-18

- [Fixed] update dca

## [2.8.0](https://github.com/pdir/social-feed-bundle/tree/2.8.0) – 2020-08-11

- [Added] twitter feed: find in tweets by account

## [2.7.1](https://github.com/pdir/social-feed-bundle/tree/2.7.1) – 2020-07-21

- [Fixed] fix displaying images for carousel album posts

## [2.7.0](https://github.com/pdir/social-feed-bundle/tree/2.7.0) – 2020-07-07

- [Added] add checkbox to import twitter reply
- [Added] add account picture and name for instagram feed

## [2.6.0](https://github.com/pdir/social-feed-bundle/tree/2.6.0) – 2020-07-02

- [Added] add instagram import via instagram graph api

## [2.5.2](https://github.com/pdir/social-feed-bundle/tree/2.5.2) – 2020-06-19

- [Fixed] support configurable stylesheets

## [2.5.1](https://github.com/pdir/social-feed-bundle/tree/2.5.1) – 2020-06-02

- [Fixed] fix instagram import

## [2.5.0](https://github.com/pdir/social-feed-bundle/tree/2.5.0) – 2020-05-14

- [Fixed] add moderation for instagram

## [2.4.1](https://github.com/pdir/social-feed-bundle/tree/2.4.1) – 2020-05-07

- [Fixed] fix instagram import bug
- [Fixed] remove font awesome and add svg icons

## [2.4.0](https://github.com/pdir/social-feed-bundle/tree/2.4.0) – 2020-02-03

- [Fixed] add twitter import

## [2.3.1](https://github.com/pdir/social-feed-bundle/tree/2.3.1) – 2019-12-05

- [Fixed] update version and links in backend template

## [2.3.0](https://github.com/pdir/social-feed-bundle/tree/2.3.0) – 2019-11-13

- [Added] add instagram posts

## [2.2.4](https://github.com/pdir/social-feed-bundle/tree/2.2.4) – 2019-09-23

- [Fixed] fix import error with long title

## [2.2.3](https://github.com/pdir/social-feed-bundle/tree/2.2.3) – 2019-09-19

- [Fixed] import image title

## [2.2.2](https://github.com/pdir/social-feed-bundle/tree/2.2.2) – 2019-08-28

- [Fixed] replace html tags br with p in teaser

## [2.2.1](https://github.com/pdir/social-feed-bundle/tree/2.2.1) – 2019-02-06

- [Fixed] load masonry script if all images are loaded
- [Fixed] bugfix: masonry list if lazyload is active

## [2.2.0](https://github.com/pdir/social-feed-bundle/tree/2.2.0) – 2018-12-07

- [Added] now you can show facebook posts and contao news in one list

## [2.1.6](https://github.com/pdir/social-feed-bundle/tree/2.1.6) – 2018-12-07

- [Fixed] fix jquery conflict in template

## [2.1.5](https://github.com/pdir/social-feed-bundle/tree/2.1.5) – 2018-12-06

- [Fixed] add „…“ only if the post is longer
- [Fixed] remove icons in contao 4.4 because the post is cut off after icons

## [2.1.4](https://github.com/pdir/social-feed-bundle/tree/2.1.4) – 2018-12-03

- [Fixed] update template and css

## [2.1.3](https://github.com/pdir/social-feed-bundle/tree/2.1.3) – 2018-12-03

- [Fixed] update template

## [2.1.2](https://github.com/pdir/social-feed-bundle/tree/2.1.2) – 2018-12-03

- [Fixed] set external url while import, remove unneeded field, update template

## [2.1.1](https://github.com/pdir/social-feed-bundle/tree/2.1.1) – 2018-11-27

- [Fixed] bugfix posts without images

## [2.1.0](https://github.com/pdir/social-feed-bundle/tree/2.1.0) – 2018-11-26

- [Added] add access token field

## [2.0.0](https://github.com/pdir/social-feed-bundle/tree/2.0.0) – 2018-06-05

- [feature] insert facebook posts in database, save all posts in an news archive und display the social feed via module type news list

## [1.0.3](https://github.com/pdir/social-feed-bundle/tree/1.0.3) – 2018-05-30

- [Fixed] load local scripts for font awesome and masonry

## [1.0.2](https://github.com/pdir/social-feed-bundle/tree/1.0.2) – 2018-02-20

- [Fixed] fix error in fe module

## [1.0.1](https://github.com/pdir/social-feed-bundle/tree/1.0.1) – 2018-02-20

- [Fixed] updated template and ListingElement

## [1.0.0](https://github.com/pdir/social-feed-bundle/tree/1.0.0) – 2017-12-13

- [Fixed] first release
