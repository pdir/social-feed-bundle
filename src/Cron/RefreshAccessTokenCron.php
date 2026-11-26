<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2026 pdir / digital agentur // pdir GmbH
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

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Email;
use Contao\System;
use GuzzleHttp\Client;
use Pdir\SocialFeedBundle\Importer\InstagramClient;
use Pdir\SocialFeedBundle\Model\SocialFeedServiceModel;
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

        $services = SocialFeedServiceModel::findAll();

        if (null === $services) {
            return;
        }

        foreach ($services as $service) {
            // LinkedIn
            if ('LinkedIn' === $service->type && !empty($service->accessToken) && !empty($service->refreshToken)) {
                $this->refreshLinkedInAccessToken($service);
            }

            // Instagram
            if ('Instagram' === $service->type && !empty($service->accessToken)) {
                $this->refreshInstagramAccessToken($service);
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function refreshLinkedInAccessToken(SocialFeedServiceModel $service): void
    {
        $nextWeek = strtotime('+1 week', time());

        // The refresh token expires after one year, the user has to generate a new access token manually
        if ('' === (string) $service->refreshTokenExpires || $nextWeek >= (int) $service->refreshTokenExpires) {
            $note = $service->noteForRefreshTokenMail ?: '-';

            $objMail = new Email();
            $objMail->subject = $GLOBALS['TL_LANG']['BE_MOD']['emailLinkedInSubject'];
            $objMail->html = sprintf($GLOBALS['TL_LANG']['BE_MOD']['emailLinkedInHtml'], $note, $service->name);
            $objMail->from = $GLOBALS['TL_CONFIG']['adminEmail'];
            $objMail->fromName = $note;
            $objMail->sendTo($GLOBALS['TL_CONFIG']['adminEmail']);
        }

        if ((int) $service->tokenExpires > $nextWeek) {
            return;
        }

        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $service->refreshToken,
            'client_id' => $service->clientId,
            'client_secret' => $service->clientSecret,
        ];

        try {
            $token = json_decode(file_get_contents('https://www.linkedin.com/oauth/v2/accessToken?'.http_build_query($data)));

            if (empty($token->access_token)) {
                throw new \RuntimeException('Social Feed (Service ID '.$service->id.'): LinkedIn access token could not be refreshed.');
            }

            $service->accessToken = $token->access_token;
            $service->tokenExpires = time() + $token->expires_in;
            $service->refreshToken = $token->refresh_token ?? $service->refreshToken;

            if (isset($token->refresh_token_expires_in)) {
                $service->refreshTokenExpires = time() + $token->refresh_token_expires_in;
            }

            $service->tstamp = time();
            $service->save();
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage(), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
        }
    }

    private function refreshInstagramAccessToken(SocialFeedServiceModel $service): void
    {
        // Unified login: system user access tokens never expire and cannot be refreshed via graph.instagram.com
        if (InstagramClient::isUnifiedLogin($service->loginType)) {
            if ('' !== (string) $service->tokenExpires && strtotime('+1 week', time()) >= (int) $service->tokenExpires) {
                $this->logger->log(LogLevel::WARNING, 'Social Feed (Service ID '.$service->id.'): The Instagram access token (unified login) expires soon and cannot be renewed automatically. Please request a new access token in the backend.', ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
            }

            return;
        }

        if ('' !== (string) $service->tokenExpires && (int) $service->tokenExpires > strtotime('+1 week', time())) {
            return;
        }

        try {
            $client = new Client();
            $response = $client->get('https://graph.instagram.com/refresh_access_token', [
                'query' => [
                    'grant_type' => 'ig_refresh_token',
                    'access_token' => $service->accessToken,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            if (empty($data['access_token'])) {
                throw new \RuntimeException('Social Feed (Service ID '.$service->id.'): Instagram access token could not be refreshed.');
            }

            $service->accessToken = $data['access_token'];
            $service->tokenExpires = time() + $data['expires_in'];
            $service->tstamp = time();
            $service->save();
        } catch (\Exception $e) {
            // Do not log the exception message, as it contains the request URI including the access token
            $this->logger->log(LogLevel::ERROR, sprintf('Social Feed (Service ID %s): Unable to refresh the Instagram access token (%s, code %s).', $service->id, $e::class, $e->getCode()), ['contao' => new ContaoContext(__METHOD__, 'ERROR')]);
        }
    }
}
