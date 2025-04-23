<?php

declare(strict_types=1);

/*
 * Social Feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
 *
 * @package    social-feed-bundle
 * @link       https://pdir.de/socialfeed+
 * @license    proprietary / pdir license - All-rights-reserved - commercial extension
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\SocialFeedBundle\EventListener;

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\MenuEvent;
use Pdir\SocialFeedBundle\Controller\AccountController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener(ContaoCoreEvents::BACKEND_MENU_BUILD, priority: -255)]
class BackendMenuListener
{
    protected $router;
    protected $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function __invoke(MenuEvent $event): void
    {
        $factory = $event->getFactory();
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $contentNode = $tree->getChild('pdir');

        $node = $factory
            ->createItem('SocialFeedAccount')
            ->setUri($this->router->generate(AccountController::class))
            ->setLabel('Social Feed Konten')
            ->setLinkAttribute('title', 'Social Feed Konten')
            ->setLinkAttribute('class', 'sf-account')
            ->setCurrent($this->requestStack->getCurrentRequest()->get('_controller') === AccountController::class)
        ;

        $contentNode->addChild($node);
    }
}
