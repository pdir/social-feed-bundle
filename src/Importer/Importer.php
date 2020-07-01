<?php

namespace Pdir\SocialFeedBundle\Importer;

use Contao\Date;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\System;
use Pdir\SocialFeedBundle\Importer\InstagramClient;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;

class Importer
{
    /**
     * @var InstagramClient
     */
    protected $client;

    /*
     * account image uuid
     */
    private $accountImage;

    /**
     * Collect data from the instagram api and return array.
     *
     * @return void | array
     * @throws \RuntimeException
     */
    public function getInstagramPosts($socialFeedId, $appId, $accessToken, $numberOfPosts = 10)
    {
        if ('' === $appId)
            return 'no app id given';

        if ('' === $accessToken)
            return 'no access token given';

        $client = System::getContainer()->get(InstagramClient::class);
        $items = $client->getMediaData($accessToken, (int) $socialFeedId);

        return $items['data'];
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
    public function getInstagramAccount($socialFeedId, $accessToken)
    {

        $client = System::getContainer()->get(InstagramClient::class);
        $username = $client->getUserData($accessToken, (int) $socialFeedId);

        return $username;
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
                'id' => $item['id'],
                'title' => $item['caption'],
                'time' => Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $item['timestamp']),
                'image' => strpos($item['media_url'],"jpg")!==false ? $item['media_url'] : $item['thumbnail_url'],
                'link' => $item['permalink'],
            ];
        }

        return $listItems;
    }

    function getPostsByAccount($id) {

        $objSocialFeed = SocialFeedModel::findBy('id', $id);

        if (NULL === $objSocialFeed) {
            return;
        }

        switch ($objSocialFeed->socialFeedType) {
            case "Facebook":
                return 'Facebook is currently not supported.';
                break;
            case "Instagram":
                return $this->getInstagramPosts($objSocialFeed->id, $objSocialFeed->psf_instagramAppId, $objSocialFeed->psf_instagramAccessToken, $objSocialFeed->number_posts);
                break;
            case "Twitter":
                return 'Twitter is currently not supported.';
                break;
        }

    }
}
