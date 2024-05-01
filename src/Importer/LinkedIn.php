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

namespace Pdir\SocialFeedBundle\Importer;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Dbafs;
use Contao\File;
use Contao\NewsModel;
use Contao\System;
use LinkedIn\Client;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;
use Psr\Log\LogLevel;

class LinkedIn
{
    private int $maxPosts = 100;
    private bool $debug = false;

    private bool $ignoreInterval = false;
    public function import(): bool
    {
        $logger = System::getContainer()->get('monolog.logger.contao');

        $objSocialFeed = SocialFeedModel::findAll();

        if($this->debug) {
            dump($objSocialFeed);
        }

        if (null === $objSocialFeed) {
            dump('--- no social feed account available!');
            return false;
        }

        foreach ($objSocialFeed as $obj) {
            if ('LinkedIn' === $obj->socialFeedType) {
                #dump($obj->socialFeedType);
                $cron = $obj->pdir_sf_fb_news_cronjob;
                $lastImport = $obj->pdir_sf_fb_news_last_import_date;
                $tstamp = time();

                # dump('LastImport: '.$lastImport);
                if ('' === $lastImport) {
                    $lastImport = 0;
                }
                $interval = $tstamp - $lastImport;

                dump(($interval >= $cron && 'no_cronjob' !== $cron) || (0 === $lastImport && 'no_cronjob' !== $cron));
                if (($interval >= $cron && 'no_cronjob' !== $cron) || (0 === $lastImport && 'no_cronjob' !== $cron) || true === $this->ignoreInterval) {
                    dump('Check Interval: '.$interval);

                    $this->setLastImportDate($obj);

                    $client = new Client(
                        $obj->linkedin_client_id,
                        $obj->linkedin_client_secret
                    );

                    $client->setApiHeaders([
                        'Content-Type' => 'application/json',
                        'X-Restli-Protocol-Version' => '2.0.0', // use protocol v2
                    ]);

                    $client->setAccessToken($obj->linkedin_access_token);

                    /* Not enough permissions to access: GET /me
                     $profile = $client->get(
                        'me',
                        ['fields' => 'id,firstName,lastName']
                    );
                    print_r($profile); */

                    if($this->debug) {
                        dump('-- List companies where you are an admin');
                        $profile = $client->get(
                            'organizationalEntityAcls',
                            ['q' => 'roleAssignee']
                        );
                        dump($profile);
                    }

                    /*
                    $ugcPosts = $client->get(
                        'ugcPosts?author=urn:li:organization:'.$obj->linkedin_company_id #.'&lifecycleState=PUBLISHED'
                    );
                    dump($ugcPosts);
                    */


                    $companyInfo = $client->get('organizations/' . $obj->linkedin_company_id);
                    dump('-- List organizations');
                    dump($companyInfo);

                    # urn:li:organization:70570732
                    # urn%3ali%3aorganization%3a70570732
                    # $client->setApiRoot('https://api.linkedin.com/rest/');
                    $client->setApiHeaders([
                        'Content-Type' => 'application/json',
                        'X-Restli-Protocol-Version' => '2.0.0', // use protocol v2,
                        'LinkedIn-Version' => '202306',
                        #'X-RestLi-Method' => 'BATCH_GET'
                    ]);

                    /*
                    $profile = $client->get(
                        'me',
                        ['fields' => 'id,firstName,lastName']
                    );
                    print_r($profile);*/

                    # ma urn:li:person:pX1FysEalk
                    $posts = $client->get(
                        # 'posts?q=owners&owners=urn:li:organization:'.$obj->linkedin_company_id.'&sortBy=LAST_MODIFIED&sharesPerOwner='.$this->maxPosts
                        # 'dmaPosts/urn:li:ugcPost:70570732'
                        # 'dmaPosts?ids=List(urn:li:ugcPost:70570732,urn:li:share:70570732)'
                        # 'dmaPosts?ids=List(urn%3ali%3augcPost%3a70570732,urn%3ali%3ashare%3a70570732)'
                        'posts?ids=List(urn%3ali%3augcPost%3a70570732,urn%3ali%3ashare%3a70570732)'
                        # 'posts?ids=List(urn%3ali%3augcPost%3a70570732,urn%3ali%3ashare%3a70570732,urn%3ali%3aorganization%3a70570732)'
                        # 'posts?ids=List(urn%3ali%3agroupPost%3a70570732,urn%3ali%3augcPost%3a70570732,urn%3ali%3ashare%3a70570732,urn%3ali%3aorganization%3a70570732)'
                        # 'posts?ids=List(urn%3ali%3agroupPost%3a70570732,urn%3ali%3augcPost%3a70570732,urn%3ali%3ashare%3a70570732)'
                        # 'posts?ids=List(urn%3ali%3augcPost%3a70570732,urn%3ali%3ashare%3a70570732)'
                        # 'posts/urn%3ali%3aorganization%3a70570732/?ids=List(urn%3ali%3augcPost%3a70570732,urn%3ali%3ashare%3a70570732)'
                        #'posts/urn%3Ali%3AugcPost%3A70570732'
                        # 'posts/urn%3Ali%3AugcPost%3ApX1FysEalk'
                    );

                    dump('-- Get post details');
                    dump($posts);

                    /* Resource dmaPosts does not exist
                    $posts = $client->get(
                    # 'posts/urn%3Ali%3Aorganization%3A70570732' # 'shares'
                        'dmaPosts?q=authors&authors=List(urn%3Ali%3Aorganization%3A70570732)&sortBy=LAST_MODIFIED'
                    );
                    dump('-- List posts');
                    dump($posts);
                    */

                    $posts = $client->get(
                        # 'posts/urn%3Ali%3Aorganization%3A70570732' # 'shares'
                        'ugcPosts?q=authors&authors=List(urn%3Ali%3Aorganization%3A70570732)&sortBy=LAST_MODIFIED'
                    );
                    dump('-- List posts');
                    dump($posts);


                    /* message":"Invalid query parameters passed to request
                    dump('shares?q=owners&owners=urn:li:organization:'.$obj->linkedin_company_id.'&sortBy=LAST_MODIFIED&sharesPerOwner='.$this->maxPosts);
                    $posts = $client->get(
                        'shares?q=owners&owners=urn:li:organization:'.$obj->linkedin_company_id.'&sortBy=LAST_MODIFIED&sharesPerOwner='.$this->maxPosts
                    );

                    dump($posts); */

                    /* works
                    $organization = $client->get(
                        'organizations/'.$obj->linkedin_company_id
                    );

                    dump($organization);
                    */

                    if (!\is_array($posts['elements'])) {
                        continue;
                    }

                    $counter = 1;

                    foreach ($posts['elements'] as $element) {
                        if ($counter++ > $obj->number_posts) {
                            continue;
                        }

                        $objNews = new NewsModel();

                        if (null !== $objNews->findBy('social_feed_id', $element['id'])) {
                            continue;
                        }

                        $objFile = '';

                        if (null !== $element['content']['contentEntities'][0]['thumbnails'][0]['resolvedUrl']) {
                            $imgPath = $this->createImageFolder($obj->linkedin_company_id);
                            $picturePath = $imgPath.$element['id'].'.jpg';

                            if (!file_exists($picturePath)) {
                                $file = new File($picturePath);
                                $file->write(file_get_contents($element['content']['contentEntities'][0]['thumbnails'][0]['resolvedUrl']));
                                $file->close();
                            }

                            $objFile = Dbafs::addResource($imgPath.$element['id'].'.jpg');
                        }

                        $message = $element['text']['text']?? '';
                        $this->saveLinkedInNews($objNews, $obj, $objFile, $message, $element, $organization);
                    }

                    $logger->log(LogLevel::INFO, 'Social Feed: LinkedIn Import Account', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);

                    #$this->import('Automator');
                    #$this->Automator->generateSymlinks();
                }

                $logger->log(LogLevel::INFO, 'Social Feed: LinkedIn Import - nothing to import', ['contao' => new ContaoContext(__METHOD__, 'INFO')]);

                #$this->import('Automator');
                #$this->Automator->generateSymlinks();
            }
        }

        return true;
    }

    private function setLastImportDate($socialFeedModel): void
    {
        $socialFeedModel->pdir_sf_fb_news_last_import_date = time();
        $socialFeedModel->save();
    }

    public function setDebugMode($flag): void
    {
        if(true === $flag) {
            $this->debug = true;
        }
    }

    public function setIgnoreInterval($flag): void
    {
        if(true === $flag) {
            $this->ignoreInterval = true;
        }
    }

    public function setMaxPosts($maxPosts): void
    {
        $this->maxPosts = $maxPosts;
    }
}
