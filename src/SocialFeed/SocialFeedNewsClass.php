<?php

namespace Pdir\SocialFeedBundle\SocialFeed;

class SocialFeedNewsClass {

    public function parseNews($objTemplate, $arrRow, $objModule)
    {
        if($arrRow['pdir_sf_fb_id'] != "") {

            if($objModule->pdir_sf_text_length > 0) {
                $teaser = substr($arrRow['teaser'] ,0,$objModule->pdir_sf_text_length);
                if(substr($arrRow['teaser'] ,$objModule->pdir_sf_text_length,1) != "") $teaser .= "...";
            }

            $objTemplate->sfTextLength = $objModule->pdir_sf_text_length;
            $objTemplate->sfFbLink = $arrRow['pdir_sf_fb_link'];
            $objTemplate->sfFbAccountPicture = $arrRow['pdir_sf_fb_account_picture'];
            $objTemplate->sfFbAccount = $arrRow['pdir_sf_fb_account'];
            $objTemplate->teaser = $teaser;
        }
    }

}