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

namespace Pdir\SocialFeedBundle\Model;

use Contao\FilesModel;
use Contao\Model;
use Contao\Model\Collection;
use Contao\System;

class SocialFeedAccountModel extends Model
{
    protected static $strTable = 'tl_sf_account';

    private ?SocialFeedServiceModel $serviceModel = null;

    /**
     * Returns the service (app credentials) of this account.
     */
    public function getService(): ?SocialFeedServiceModel
    {
        if (null === $this->serviceModel && $this->service) {
            $this->serviceModel = SocialFeedServiceModel::findByPk($this->service);
        }

        return $this->serviceModel;
    }

    /**
     * Returns the access token of the account and falls back to the access token of the service.
     */
    public function getAccessToken(): string
    {
        if (!empty($this->accessToken)) {
            return (string) $this->accessToken;
        }

        return (string) ($this->getService()?->accessToken ?? '');
    }

    /**
     * Find the accounts of the given type which are imported by the Contao cronjob ($poorManCron = true,
     * accounts with cronjob interval) or by the console command ($poorManCron = false, all accounts).
     *
     * Already imported posts are skipped by the importers, so running both is safe.
     */
    public static function findForImport(string $type, bool $poorManCron): ?Collection
    {
        if ($poorManCron) {
            return static::findBy(['type = ?', 'cronjob != ?'], [$type, 'no_cronjob']);
        }

        return static::findBy(['type = ?'], [$type]);
    }

    public static function getTypeIcon(string $type): string
    {
        return match (strtolower($type)) {
            'facebook' => 'icon_facebook.svg',
            'instagram' => 'icon_instagram.svg',
            'x', 'twitter' => 'icon_twitter.svg',
            'linkedin' => 'linkedin-in-brands.svg',
            default => 'icon_user.svg',
        };
    }

    public static function getAvatar(self $account, $size = 80): string
    {
        $src = self::getAvatarSrc($account, (int) $size);

        if (null === $src) {
            return '<img src="/bundles/pdirsocialfeed/img/icon_user.svg" width="20" height="20" alt="no avatar" class="avatar">';
        }

        return '<img class="sfp_type" src="/bundles/pdirsocialfeed/img/'.self::getTypeIcon((string) $account->type).'" width="40" height="40" alt="'.$account->type.'"><img class="sfp_avatar sfp_'.$account->type.'" src="'.$src.'" width="'.$size.'" height="'.$size.'" alt="'.$account->name.'">';
    }

    /**
     * Returns the URL of the resized avatar or null if the account has no (existing) avatar.
     */
    public static function getAvatarSrc(self $account, int $size = 80): ?string
    {
        if (empty($account->avatar)) {
            return null;
        }

        $file = FilesModel::findByUuid($account->avatar);

        if (null === $file || !file_exists(System::getContainer()->getParameter('kernel.project_dir').'/'.$file->path)) {
            return null;
        }

        $figure = System::getContainer()
            ->get('contao.image.studio')
            ->createFigureBuilder()
            ->fromPath($file->path)
            ->setSize([$size, $size, 'crop'])
            ->build()
        ;

        return $figure->getLegacyTemplateData()['src'] ?? null;
    }
}
