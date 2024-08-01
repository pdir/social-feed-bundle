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

namespace Pdir\SocialFeedBundle\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Email;
use Contao\System;
use GuzzleHttp\Client;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;
use Psr\Log\LogLevel;

#[AsCronJob('daily')]
class RefreshAccessTokenCron
{
    private ?object $logger;

    public function __construct(private ContaoFramework $framework)
    {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(): void
    {
        $this->framework->initialize();
        $this->logger = System::getContainer()->get('monolog.logger.contao');

        $objSocialFeed = SocialFeedModel::findAll();

        if (null === $objSocialFeed) {
            return;
        }

        foreach ($objSocialFeed as $account) {
            // LinkedIn
            if ('LinkedIn' === $account->socialFeedType && '' !== $account->linkedin_access_token && '' !== $account->linkedin_refresh_token) {
                $this->refreshLinkedInAccessToken($account);
            }

            // Instagram
            if ('Instagram' === $account->socialFeedType && '' !== $account->psf_instagramAccessToken) {
                $this->refreshInstagramAccessToken($account);
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function refreshLinkedInAccessToken(SocialFeedModel $socialFeedModel): void
    {
        if (\strtotime('+1 week', \time()) >= $socialFeedModel->linkedin_refresh_token_expires || '' === $socialFeedModel->linkedin_refresh_token_expires) {
            $objMail = new Email();
            $objMail->subject = $GLOBALS['TL_LANG']['BE_MOD']['emailLinkedInSubject'];
            $objMail->html = sprintf($GLOBALS['TL_LANG']['BE_MOD']['emailLinkedInHtml'], $socialFeedModel->noteForRefreshTokenMail?? '-', $socialFeedModel->linkedin_company_id);
            $objMail->from = $GLOBALS['TL_CONFIG']['adminEmail'];
            $objMail->fromName = $socialFeedModel->noteForRefreshTokenMail?? '-';
            $objMail->sendTo($GLOBALS['TL_CONFIG']['adminEmail']);
        }

        if ($socialFeedModel->access_token_expires <= \strtotime('+1 week', \time())) {
            $data = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $socialFeedModel->linkedin_refresh_token,
                'client_id' => $socialFeedModel->linkedin_client_id,
                'client_secret' => $socialFeedModel->linkedin_client_secret,
            ];

            try {
                $token = json_decode(file_get_contents('https://www.linkedin.com/oauth/v2/accessToken?'.http_build_query($data)));

                // Store the access token
                $db = Database::getInstance();
                $set = ['linkedin_access_token' => $token->access_token, 'access_token_expires' => time() + $token->expires_in, 'linkedin_refresh_token' => $token->refresh_token, 'linkedin_refresh_token_expires' => time() + $token->refresh_token_expires_in];
                $db->prepare('UPDATE tl_social_feed %s WHERE id = ?')->set($set)->execute($socialFeedModel->id);
            } catch (\Exception $e) {
                $this->logger->log(LogLevel::ERROR, $e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }
        }
    }

    private function refreshInstagramAccessToken(SocialFeedModel $socialFeedModel): void
    {
        if (\strtotime('+1 week', \time()) >= $socialFeedModel->access_token_expires || '' === $socialFeedModel->access_token_expires) {
            $client = new Client();
            $response = $client->get('https://graph.instagram.com/refresh_access_token', [
                'query' => [
                    'grant_type' => 'ig_refresh_token',
                    'access_token' => $socialFeedModel->psf_instagramAccessToken,
                ],
            ]);

            try {
                $data = json_decode((string) $response->getBody(), true);

                // Store the access token
                $db = Database::getInstance();
                $set = ['psf_instagramAccessToken' => $data['access_token'], 'access_token_expires' => time() + $data['expires_in']];
                $db->prepare('UPDATE tl_social_feed %s WHERE id = ?')->set($set)->execute($socialFeedModel->id);
            } catch (\Exception $e) {
                $this->logger->log(LogLevel::ERROR, $e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }
        }
    }
}
