<?php

namespace Pdir\SocialFeedBundle\Importer;

use InstagramScraper\Instagram;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;
use Contao\Date;

class Importer
{
    /*
     * account image uuid
     */
    private $accountImage;

    /**
     * Collect data from the instagram api and return array.
     *
     * @return void | array
     */
    public function getInstagramPosts($accountName, $numberOfPosts = 10)
    {
        $instagram = new Instagram();

        if ('' === $accountName)
            return 'no account given';

        $items = $instagram->getMedias($accountName, $numberOfPosts);
        $this->accountImage = $this->getInstagramAccountImage($accountName);
        // echo "<br>insta: <pre style='height: 200px;overflow:hidden;'>"; print_r($items); echo "</pre>";

        return $items;
    }

    /**
     *
     */
    public function getAccountImage() {
        return $this->accountImage;
    }

    /**
     * Collect data from the instagram api and return array.
     *
     * @return void | array
     */
    public function getInstagramAccount($accountName)
    {
        $instagram = new Instagram();

        return $instagram->getAccount($accountName);
    }

    /**
     * Collect data from the instagram api and return array.
     *
     * @return void | array
     */
    public function getInstagramAccountImage($accountName)
    {
        $instagram = new Instagram();

        return $instagram->getAccount($accountName)->getProfilePicUrl();
    }

    public function moderation($items) {

        $listItems = [];

        foreach ($items as $item) {
            $listItems[] = [
                'id' => $item->getId(),
                'title' => $item->getCaption(),
                'time' => Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $item->getCreatedTime()),
                'image' => $item->getImageThumbnailUrl(),
                'link' => $item->getLink()
            ];
        }

        return $listItems;
    }

    function getPostsByAccount($id) {

        $objSocialFeed = SocialFeedModel::findBy('id', $id);

        if (null === $objSocialFeed) {
            return;
        }

        switch ($objSocialFeed->socialFeedType) {
            case "Facebook":
                return 'Facebook is currently not supported.';
                break;
            case "Instagram":
                return $this->getInstagramPosts($objSocialFeed->instagram_account, $objSocialFeed->number_posts);
                break;
            case "Twitter":
                return 'Twitter is currently not supported.';
                break;
        }

    }
}
