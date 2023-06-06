<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2021 pdir / digital agentur // pdir GmbH
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
use Contao\Picture;
use Contao\StringUtil;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;

class SocialFeedNewsClass
{
    public function parseNews($objTemplate, $arrRow, $objModule): void
    {
        if ('' !== $arrRow['social_feed_id']) {
            $teaser = $arrRow['teaser'];

            if ($objModule->pdir_sf_text_length > 0 && $teaser !== null) {
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

            if (null !== $arrRow['social_feed_account_picture']) {
                $imagePath = FilesModel::findByUuid($arrRow['social_feed_account_picture'])->path;

                if (null === $imagePath) {
                    $objTemplate->accountPicture = '';
                }

                if (null !== $imagePath) {
                    $pictureObj = Picture::create($imagePath);

                    if (null !== $pictureObj) {
                        $objTemplate->accountPicture = $pictureObj->getTemplateData();
                    }
                }
            } else {
                $socialFeedAccount = SocialFeedModel::findBy('id', $arrRow['social_feed_config']);

                if (null !== $socialFeedAccount->instagram_account_picture) {
                    $imagePath = FilesModel::findByUuid($socialFeedAccount->instagram_account_picture)->path;
                    $size = deserialize($socialFeedAccount->instagram_account_picture_size);
                    $objTemplate->accountPicture = Picture::create($imagePath, $size)->getTemplateData();
                } elseif (null !== $socialFeedAccount->linkedin_account_picture) {
                    $imagePath = FilesModel::findByUuid($socialFeedAccount->linkedin_account_picture)->path;
                    $size = deserialize($socialFeedAccount->linkedin_account_picture_size);
                    $objTemplate->accountPicture = Picture::create($imagePath, $size)->getTemplateData();
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
}
