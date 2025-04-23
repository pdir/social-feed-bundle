<?php

declare(strict_types=1);

/*
 * Social Feed Plus bundle for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
 *
 * @package    social-feed-plus-bundle
 * @link       https://pdir.de/socialfeed+
 * @license    proprietary / pdir license - All-rights-reserved - commercial extension
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\SocialFeedBundle\Controller;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\Date;
use Contao\FilesModel;
use Contao\StringUtil;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedPostLabelCallbackListener;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('%contao.backend.route_prefix%/sfAccount', name: self::class, defaults: ['_scope' => 'backend'])]
class AccountController extends AbstractBackendController
{
    public function __invoke(): Response
    {
        $items = [];

        $accounts = SocialFeedAccountModel::findAll();

        if(null !== $accounts) {
            foreach($accounts as $account) {
                $items[] = [
                    'name' => $account->name,
                    'type' => $account->type,
                    'added' => Date::parse('Y-m-d\TH:i', $account->added),
                    'avatar' => $account->avatar,
                    #'avatar' => $this->getAvatars(StringUtil::deserialize($account->avatar)),
                    #'typeIcon' => $this->getAvatars(StringUtil::deserialize($account->type)),
                ];
            }
        }

        $GLOBALS['TL_CSS'][] = 'bundles/pdirsocialfeed/css/account.scss|static';

        return $this->render(
            '@Contao_PdirSocialFeedBundle/sf_account.html.twig',
            [
                #'error' => 'Oh no, an error!',
                'title' => 'Social Feed Kalender',
                'headline' => 'Social Feed Kalender',
                'items' => $items?? [],
                'locale' => $GLOBALS['TL_LANGUAGE'],
            ]
        );
    }

    private function getAvatars($ids): string
    {
        $html = '';

        $accounts = SocialFeedAccountModel::findBy(
            [
                'type != ?',
                'id IN (' . implode(',', array_map('\intval', $ids)) . ')'
            ],
            ['']
        );


        foreach($accounts as $account) {
            $html .= SocialFeedPlusPostLabelCallbackListener::getAvatar($account->avatar, $account->type);
        }

        return $html;
    }
}
