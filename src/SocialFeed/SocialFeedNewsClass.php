<?php

namespace Pdir\SocialFeedBundle\SocialFeed;

use Pdir\SocialFeedBundle\Model\SocialFeedModel as SocialFeedModel;

class SocialFeedNewsClass {

    public function parseNews($objTemplate, $arrRow, $objModule)
    {
        if($arrRow['social_feed_id'] != "") {

            if($objModule->pdir_sf_text_length > 0) {
                $teaser = $arrRow['teaser'];
                $more = "";
                if( strlen($teaser) > $objModule->pdir_sf_text_length) {
                    $more = " ...";
                }
                $teaser = \StringUtil::substrHtml($teaser,$objModule->pdir_sf_text_length).$more;
            } else {
                $teaser = $arrRow['teaser'];
            }

            $objTemplate->sfFbAccountPicture = $arrRow['social_feed_account_picture'];
            $objTemplate->sfTextLength = $objModule->pdir_sf_text_length;
            $objTemplate->sfElementWidth = $objModule->pdir_sf_columns;
            $objTemplate->sfImages = $objModule->pdir_sf_enableImages;
            $objTemplate->teaser = $teaser;
            $objTemplate->socialFeedType = $arrRow['social_feed_type'];

            if('' != $arrRow['social_feed_account_picture']) {
                $imagePath = \FilesModel::findByUuid($arrRow['social_feed_account_picture'])->path;
                
                if(null === $imagePath) {
                    $objTemplate->accountPicture = '';
                }

                if(null !== $imagePath) {
                    $pictureObj = \Picture::create($imagePath);
                    
                    if ($pictureObj !== null && $pictureObj->size > 0) {
                      $objTemplate->accountPicture = $pictureObj->getTemplateData();
                    }
                }
            } else {
                $socialFeedAccount = SocialFeedModel::findBy('id', $arrRow['social_feed_config']);
                if ($socialFeedAccount->instagram_account_picture != "") {
                    $imagePath = \FilesModel::findByUuid($socialFeedAccount->instagram_account_picture)->path;
                    $size = deserialize($socialFeedAccount->instagram_account_picture_size);
                    $objTemplate->accountPicture = \Picture::create($imagePath, $size)->getTemplateData();
                }
            }

            if($arrRow['social_feed_account'] != "") {
                $objTemplate->sfFbAccount = $arrRow['social_feed_account'];
            } else {
                $socialFeedAccount = SocialFeedModel::findBy('id', $arrRow['social_feed_config']);
                $objTemplate->sfFbAccount = $socialFeedAccount->instagram_account;
            }
        }
    }

}
