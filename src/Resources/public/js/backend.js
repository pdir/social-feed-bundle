jQuery(document).ready(function($) {

    if( $('#ctrl_socialFeedType').length > 0 ) {
        if( $('#ctrl_socialFeedType').val() == 'Instagram' ) {
            $('#ctrl_pdir_sf_fb_news_cronjob option[value=60]').hide();
            if( $('#ctrl_pdir_sf_fb_news_cronjob').val() == '60' ) {
                $('#ctrl_pdir_sf_fb_news_cronjob option[value=60]').attr('selected',false);
                $('#ctrl_pdir_sf_fb_news_cronjob option[value=3600]').attr('selected',true);
            }
        }
    }

});