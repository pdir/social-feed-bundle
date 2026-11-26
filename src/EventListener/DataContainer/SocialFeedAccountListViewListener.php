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

namespace Pdir\SocialFeedBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Pdir\SocialFeedBundle\Controller\AccountController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * The accounts are managed in the account overview (AccountController). Contao returns to the list view of
 * tl_sf_account after deleting, "save and close" or "back" (since Contao 5.5 the referer is derived from the URL),
 * so the list view redirects to the overview.
 */
#[AsCallback(table: 'tl_sf_account', target: 'config.onload')]
class SocialFeedAccountListViewListener
{
    public function __construct(private readonly RequestStack $requestStack, private readonly RouterInterface $router)
    {
    }

    public function __invoke(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (
            null === $request
            || !$request->isMethod('GET')
            || $request->isXmlHttpRequest()
            || 'tl_sf_account' !== $request->query->get('table')
        ) {
            return;
        }

        // Only the list view, not edit/delete/select or pickers and popups
        foreach (['act', 'key', 'id', 'popup', 'picker'] as $param) {
            if ($request->query->has($param)) {
                return;
            }
        }

        throw new RedirectResponseException($this->router->generate(AccountController::class));
    }
}
