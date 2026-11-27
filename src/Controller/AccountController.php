<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2026 pdir / digital agentur // pdir GmbH
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

namespace Pdir\SocialFeedBundle\Controller;

use Contao\Config;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\Date;
use Contao\System;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('%contao.backend.route_prefix%/sfAccount', name: self::class, defaults: ['_scope' => 'backend'])]
class AccountController extends AbstractBackendController
{
    public function __invoke(Request $request): Response
    {
        $items = [];
        $requestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        $accounts = SocialFeedAccountModel::findAll(['order' => 'type, name']);

        if (null !== $accounts) {
            foreach ($accounts as $account) {
                $items[] = [
                    'id' => $account->id,
                    'name' => $account->name ?: $account->identifier,
                    'identifier' => $account->identifier,
                    'type' => $account->type,
                    'typeIcon' => SocialFeedAccountModel::getTypeIcon((string) $account->type),
                    'added' => $account->added ? Date::parse(Config::get('datimFormat'), $account->added) : null,
                    'lastImport' => $account->lastImport ? Date::parse(Config::get('datimFormat'), $account->lastImport) : null,
                    'avatar' => SocialFeedAccountModel::getAvatarSrc($account, 96),
                    'service' => $account->getService(),
                    'editUrl' => $this->getBackendUrl(['act' => 'edit', 'id' => $account->id, 'rt' => $requestToken]),
                    'deleteUrl' => $this->getBackendUrl(['act' => 'delete', 'id' => $account->id, 'rt' => $requestToken]),
                ];
            }
        }

        System::loadLanguageFile('tl_sf_account');

        $GLOBALS['TL_CSS'][] = 'bundles/pdirsocialfeed/css/account.css|static';

        return $this->render(
            '@Contao_PdirSocialFeedBundle/sf_account.html.twig',
            [
                'title' => 'Social Feed Konten',
                'headline' => 'Social Feed Konten',
                'items' => $items,
                'connectUrl' => $this->generateUrl('pdir_sf_account_connect'),
                // after connecting a service (e.g. Facebook login) the dialog opens with the accounts of the service
                'connectServiceUrl' => ctype_digit((string) $request->query->get('connect')) ? $this->generateUrl('pdir_sf_account_connect_service', ['id' => (int) $request->query->get('connect')]) : null,
                'connectLang' => $GLOBALS['TL_LANG']['tl_sf_account']['connect'] ?? [],
                'locale' => $GLOBALS['TL_LANGUAGE'],
            ]
        );
    }

    /**
     * @param array<string, int|string> $params
     */
    private function getBackendUrl(array $params): string
    {
        // the referer id lets Contao return to this overview after "save and close"
        $refererId = System::getContainer()->get('request_stack')->getCurrentRequest()?->attributes->get('_contao_referer_id');

        if ($refererId) {
            $params['ref'] = $refererId;
        }

        return $this->generateUrl('contao_backend', array_merge(['do' => 'sfService', 'table' => 'tl_sf_account'], $params));
    }
}
