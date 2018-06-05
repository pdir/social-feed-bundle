<?php

namespace Pdir\SocialFeedBundle\SocialFeed;

class SocialFeedNewsClass {

    public function parseNews($objTemplate, $arrRow, $objModule)
    {
        if($arrRow['pdir_sf_fb_id'] != "") {

            if($objModule->pdir_sf_text_length > 0) {
                $teaser = $arrRow['teaser'];
                $teaser = \StringUtil::substrHtml($teaser,$objModule->pdir_sf_text_length)." ...";
            } else {
                $teaser = $arrRow['teaser'];
            }

            $objTemplate->sfTextLength = $objModule->pdir_sf_text_length;
            $objTemplate->sfElementWidth = $objModule->pdir_sf_columns;
            $objTemplate->sfImages = $objModule->pdir_sf_enableImages;
            $objTemplate->sfFbLink = $arrRow['pdir_sf_fb_link'];
            $objTemplate->sfFbAccountPicture = $arrRow['pdir_sf_fb_account_picture'];
            $objTemplate->sfFbAccount = $arrRow['pdir_sf_fb_account'];
            $objTemplate->teaser = $teaser;
        }
    }

}