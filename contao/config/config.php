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

use Contao\System;
use Pdir\SocialFeedBundle\Module\ModuleCustomNewslist;
use Pdir\SocialFeedBundle\Module\NewsCategoriesModule; // @phpstan-ignore-line
use Symfony\Component\HttpFoundation\Request;

/*
 * Backend modules
 */
if (!isset($GLOBALS['BE_MOD']['pdir']) || !\is_array($GLOBALS['BE_MOD']['pdir'])) {
    \array_splice($GLOBALS['BE_MOD'], 1,0, ['pdir' => []]);
}

$GLOBALS['TL_HOOKS']['parseArticles'][] = ['Pdir\SocialFeedBundle\SocialFeed\SocialFeedNewsClass', 'parseNews'];

$assetsDir = 'bundles/pdirsocialfeed';

$GLOBALS['BE_MOD']['pdir'] = [
    'sfService' => [
        'tables' => ['tl_sf_service', 'tl_sf_account'],
    ],
];

$GLOBALS['BE_MOD']['content']['news']['moderate'] = ['pdir_social_feed_moderate.controller', 'run'];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_sf_account'] = 'Pdir\SocialFeedBundle\Model\SocialFeedAccountModel';
$GLOBALS['TL_MODELS']['tl_sf_service'] = 'Pdir\SocialFeedBundle\Model\SocialFeedServiceModel';

/*
 * Frontend modules
 */
$GLOBALS['FE_MOD']['news']['newslist'] = ModuleCustomNewslist::class;

if (\str_contains($GLOBALS['FE_MOD']['news']['newslist'], 'Codefog\NewsCategoriesBundle')) {
    $GLOBALS['FE_MOD']['news']['newslist'] = NewsCategoriesModule::class; // @phpstan-ignore-line
}

/*
 * CSS for Backend
 */
$request = System::getContainer()->get('request_stack')->getCurrentRequest();
$scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');

if ($request && $scopeMatcher->isBackendRequest($request)) {
    $GLOBALS['TL_CSS'][] = $assetsDir.'/css/sf_moderation.scss|static';
    $GLOBALS['TL_CSS'][] = $assetsDir.'/css/backend.css|static';
}
