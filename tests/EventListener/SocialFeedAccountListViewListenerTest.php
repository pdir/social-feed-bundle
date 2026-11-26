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

namespace Pdir\SocialFeedBundle\Tests\EventListener;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Pdir\SocialFeedBundle\Controller\AccountController;
use Pdir\SocialFeedBundle\EventListener\DataContainer\SocialFeedAccountListViewListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class SocialFeedAccountListViewListenerTest extends TestCase
{
    public function testRedirectsTheListViewToTheOverview(): void
    {
        $listener = $this->createListener(Request::create('/contao?do=sfService&table=tl_sf_account&ref=abc'));

        try {
            $listener();
            $this->fail('No redirect');
        } catch (RedirectResponseException $e) {
            $this->assertSame('/contao/sfAccount', $e->getResponse()->headers->get('Location'));
        }
    }

    /**
     * @dataProvider noRedirectProvider
     */
    public function testDoesNotRedirectOtherViews(Request $request): void
    {
        $this->createListener($request)();

        $this->addToAssertionCount(1);
    }

    /**
     * @return iterable<string, array{Request}>
     */
    public static function noRedirectProvider(): iterable
    {
        yield 'edit' => [Request::create('/contao?do=sfService&table=tl_sf_account&act=edit&id=3')];
        yield 'delete' => [Request::create('/contao?do=sfService&table=tl_sf_account&act=delete&id=3')];
        yield 'select' => [Request::create('/contao?do=sfService&table=tl_sf_account&act=select')];
        yield 'popup' => [Request::create('/contao?do=sfService&table=tl_sf_account&popup=1')];
        yield 'picker' => [Request::create('/contao?do=sfService&table=tl_sf_account&picker=abc')];
        yield 'services' => [Request::create('/contao?do=sfService')];
        yield 'post' => [Request::create('/contao?do=sfService&table=tl_sf_account', 'POST')];
    }

    private function createListener(Request $request): SocialFeedAccountListViewListener
    {
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->with(AccountController::class)
            ->willReturn('/contao/sfAccount')
        ;

        return new SocialFeedAccountListViewListener($requestStack, $router);
    }
}
