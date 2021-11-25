<?php

namespace Pdir\SocialFeedBundle\Importer;

use Contao\Date;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Message;
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
    public function getInstagramPosts($accessToken, $socialFeedId, $numberPosts = 30)
    {
        if ('' === $accessToken)
            return 'no access token given';

        $client = System::getContainer()->get(InstagramClient::class);
        $items = $client->getMediaData($accessToken, (int) $socialFeedId, (int) $numberPosts);

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
    public function getInstagramAccount($accessToken, $socialFeedId)
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
    public function getInstagramAccountImage($accessToken, $socialFeedId)
    {
        $client = System::getContainer()->get(InstagramClient::class);
        $image = $client->getUserImage($accessToken, (int) $socialFeedId, false);

        return $image;
    }

    public function moderation($items) {

        $listItems = [];

        foreach ($items as $item) {
            $listItems[] = [
                'id' => $item['id'],
                'title' => $item['caption'],
                'time' => Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], strtotime($item['timestamp'])),
                'image' => strpos($item['media_url'],"jpg")!==false ? $item['media_url'] : $item['thumbnail_url'],
                'link' => $item['permalink'],
            ];
        }

        return $listItems;
    }

    function getPostsByAccount($id, $numberPosts) {

        $objSocialFeed = SocialFeedModel::findBy('id', $id);

        if (NULL === $objSocialFeed) {
            return;
        }

        switch ($objSocialFeed->socialFeedType) {
            case "Facebook":
                Message::addError('Facebook is currently not supported. Try Instagram!');
                return [];
            case "Instagram":
                return $this->getInstagramPosts($objSocialFeed->psf_instagramAccessToken, $objSocialFeed->id, $numberPosts);
            case "Twitter":
                Message::addError('Twitter is currently not supported. Try Instagram.');
                return [];
        }

    }
}
