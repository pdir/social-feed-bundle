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

namespace Pdir\SocialFeedBundle\SocialFeed;

use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Imagine\Exception\RuntimeException;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;

class SocialFeedNewsClass
{
    private string|array|bool|int|null|float $projectDir;
    private $staticUrl;

    public function parseNews($objTemplate, $arrRow, $objModule): void
    {
        if ('' !== $arrRow['social_feed_id']) {

            $container = System::getContainer();
            $this->projectDir = $container->getParameter('kernel.project_dir');
            $this->staticUrl = $container->get('contao.assets.files_context')->getStaticUrl();

            $teaser = $arrRow['teaser'];

            if ($objModule->pdir_sf_text_length > 0 && null !== $teaser) {
                $more = '';

                if (\strlen($teaser) > $objModule->pdir_sf_text_length) {
                    $more = ' ...';
                }
                $teaser = StringUtil::substrHtml($teaser, $objModule->pdir_sf_text_length).$more;
            }

            $objTemplate->sfFbAccountPicture = $arrRow['social_feed_account_picture'];
            $objTemplate->sfTextLength = $objModule->pdir_sf_text_length;
            $objTemplate->sfElementWidth = $objModule->pdir_sf_columns;
            $objTemplate->sfImages = $objModule->pdir_sf_enableImages;
            $objTemplate->teaser = $teaser;
            $objTemplate->socialFeedType = $arrRow['social_feed_type'];

            $pictureFactory = System::getContainer()->get('contao.image.picture_factory');

            if (null !== $arrRow['social_feed_account_picture']) {
                $imagePath = FilesModel::findByUuid($arrRow['social_feed_account_picture'])->path?? null;

                if (null === $imagePath) {
                    $objTemplate->accountPicture = '';
                }

                if (null !== $imagePath) {
                    try {
                        $pictureObj = $pictureFactory->create($this->projectDir.DIRECTORY_SEPARATOR.$imagePath);
                    } catch (RuntimeException) {
                        $pictureObj = null;
                    }

                    if (null !== $pictureObj) {
                        $objTemplate->accountPicture = $this->getTemplateData($pictureObj);
                    }
                }
            } else {
                $socialFeedAccount = SocialFeedModel::findBy('id', $arrRow['social_feed_config']);

                if (null !== $socialFeedAccount->instagram_account_picture) {
                    $image = FilesModel::findByUuid($socialFeedAccount->instagram_account_picture);
                    $size = StringUtil::deserialize($socialFeedAccount->instagram_account_picture_size);
                    $objTemplate->accountPicture = $this->getTemplateData($pictureFactory->create($this->projectDir.DIRECTORY_SEPARATOR.$image->path, $size));
                } elseif (null !== $socialFeedAccount->linkedin_account_picture) {
                    $image = FilesModel::findByUuid($socialFeedAccount->linkedin_account_picture);
                    $size = StringUtil::deserialize($socialFeedAccount->linkedin_account_picture_size);
                    $objTemplate->accountPicture = $this->getTemplateData($pictureFactory->create($this->projectDir.DIRECTORY_SEPARATOR.$image->path, $size));
                }
            }

            if (null !== $arrRow['social_feed_account'] && '' !== $arrRow['social_feed_account']) {
                $objTemplate->sfFbAccount = $arrRow['social_feed_account'];
            } else {
                $socialFeedAccount = SocialFeedModel::findBy('id', $arrRow['social_feed_config']);
                $objTemplate->sfFbAccount = $socialFeedAccount->instagram_account;
            }
        }
    }

    private function getTemplateData($picture)
    {
        if (null === $picture) {
            return;
        }

        return [
            'img' => $picture->getImg($this->projectDir, $this->staticUrl),
            'sources' => $picture->getSources($this->projectDir, $this->staticUrl),
        ];
    }
}
