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

namespace Pdir\SocialFeedBundle\Controller;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Message;
use Contao\System;
use Pdir\SocialFeedBundle\Model\SocialFeedAccountModel;
use Pdir\SocialFeedBundle\Model\SocialFeedServiceModel;
use Pdir\SocialFeedBundle\SocialFeed\AccountDiscovery;
use Pdir\SocialFeedBundle\SocialFeed\AccountDiscoveryException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Dialog "add account" of the account overview: select a service, then the accounts of the service.
 *
 * The GET actions return HTML fragments which are loaded into the dialog.
 */
#[Route('%contao.backend.route_prefix%/sfAccount/connect', defaults: ['_scope' => 'backend'])]
class AccountConnectController extends AbstractBackendController
{
    public function __construct(private readonly AccountDiscovery $discovery)
    {
    }

    #[Route('', name: 'pdir_sf_account_connect', methods: ['GET'])]
    public function services(): Response
    {
        $this->checkPermission();

        $services = [];

        foreach (SocialFeedServiceModel::findAll(['order' => 'type, name']) ?? [] as $service) {
            $services[] = [
                'id' => (int) $service->id,
                'name' => (string) $service->name,
                'type' => (string) $service->type,
                'typeIcon' => SocialFeedAccountModel::getTypeIcon((string) $service->type),
                'connected' => $this->discovery->isConnected($service) || !$this->discovery->supportsDiscovery((string) $service->type),
                'url' => $this->generateUrl('pdir_sf_account_connect_service', ['id' => $service->id]),
            ];
        }

        return $this->renderFragment([
            'step' => 'services',
            'services' => $services,
            'serviceCreateUrl' => $this->getBackendUrl(['act' => 'create']),
            'manualUrl' => $this->getBackendUrl(['table' => 'tl_sf_account', 'act' => 'create']),
        ]);
    }

    #[Route('/{id}', name: 'pdir_sf_account_connect_service', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function accounts(int $id): Response
    {
        $this->checkPermission();

        $service = $this->getService($id);
        $type = (string) $service->type;
        $context = [
            'step' => 'accounts',
            'service' => ['id' => (int) $service->id, 'name' => (string) $service->name, 'type' => $type, 'typeIcon' => SocialFeedAccountModel::getTypeIcon($type)],
            'backUrl' => $this->generateUrl('pdir_sf_account_connect'),
            'action' => $this->generateUrl('pdir_sf_account_connect_service', ['id' => $id]),
            'serviceEditUrl' => $this->getBackendUrl(['act' => 'edit', 'id' => $id]),
            'accounts' => [],
            'manual' => !$this->discovery->supportsDiscovery($type),
            'connected' => true,
            'error' => null,
        ];

        if (!$context['manual']) {
            if (!$this->discovery->isConnected($service)) {
                $context['connected'] = false;
            } else {
                try {
                    $existing = $this->getExistingIdentifiers($type);

                    foreach ($this->discovery->discover($service) as $item) {
                        $context['accounts'][] = $item + ['added' => isset($existing[$item['identifier']])];
                    }
                } catch (AccountDiscoveryException) {
                    $context['error'] = true;
                }

                // LinkedIn organizations can also be entered manually (e.g. missing permission to list them)
                $context['manual'] = 'LinkedIn' === $type && ($context['error'] || [] === $context['accounts']);
            }
        }

        return $this->renderFragment($context);
    }

    #[Route('/{id}', name: 'pdir_sf_account_connect_save', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function save(int $id, Request $request): Response
    {
        $this->checkPermission();

        $service = $this->getService($id);
        $this->loadLanguage();

        // Manually entered account (X user name or LinkedIn organization ID): continue with the account settings
        $manualIdentifier = trim((string) $request->request->get('identifier'));

        if ('' !== $manualIdentifier) {
            $account = $this->discovery->addAccount($service, [
                'identifier' => $manualIdentifier,
                'name' => trim((string) $request->request->get('name')) ?: $manualIdentifier,
                'picture' => null,
                'accessToken' => null,
            ]);

            return new RedirectResponse($this->getBackendUrl(['table' => 'tl_sf_account', 'act' => 'edit', 'id' => $account->id]));
        }

        $selected = array_map('strval', $request->request->all('accounts'));
        $added = 0;

        if ([] !== $selected) {
            try {
                // Only accounts the service has access to (the access token of Facebook pages is not posted)
                foreach ($this->discovery->discover($service) as $item) {
                    if (\in_array($item['identifier'], $selected, true)) {
                        $this->discovery->addAccount($service, $item);
                        ++$added;
                    }
                }
            } catch (AccountDiscoveryException) {
                Message::addError($GLOBALS['TL_LANG']['tl_sf_account']['connect']['error'] ?? 'The accounts could not be loaded.');
            }
        }

        if ($added > 0) {
            Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['tl_sf_account']['connect']['added'] ?? '%s account(s) added.', $added));
        }

        return new RedirectResponse($this->generateUrl(AccountController::class));
    }

    /**
     * @param array<string, mixed> $context
     */
    private function renderFragment(array $context): Response
    {
        $this->loadLanguage();

        $context['lang'] = $GLOBALS['TL_LANG']['tl_sf_account']['connect'] ?? [];
        $context['requestToken'] = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

        $response = new Response($this->renderView('@Contao_PdirSocialFeedBundle/sf_account_connect.html.twig', $context));
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * @return array<string, true>
     */
    private function getExistingIdentifiers(string $type): array
    {
        $identifiers = [];

        foreach (SocialFeedAccountModel::findBy('type', $type) ?? [] as $account) {
            $identifiers[(string) $account->identifier] = true;
        }

        return $identifiers;
    }

    private function getService(int $id): SocialFeedServiceModel
    {
        $service = SocialFeedServiceModel::findById($id);

        if (null === $service) {
            throw new NotFoundHttpException('Service not found.');
        }

        return $service;
    }

    private function checkPermission(): void
    {
        $this->denyAccessUnlessGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, 'sfService');
    }

    private function loadLanguage(): void
    {
        System::loadLanguageFile('tl_sf_account');
    }

    /**
     * @param array<string, int|string> $params
     */
    private function getBackendUrl(array $params): string
    {
        $params['rt'] = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();

        return $this->generateUrl('contao_backend', array_merge(['do' => 'sfService'], $params));
    }
}
