<?php

namespace Tourze\EasyAdminMenuBundle\Tests\Service;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\EasyAdminMenuBundle\Service\DefaultMenuCollector;

class DefaultMenuCollectorTest extends TestCase
{
    private MockObject|FactoryInterface $factory;
    private MockObject|ItemInterface $rootMenu;
    private DefaultMenuCollector $menuCollector;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FactoryInterface::class);
        $this->rootMenu = $this->createMock(ItemInterface::class);
        
        $this->factory->expects($this->any())
            ->method('createItem')
            ->with('root')
            ->willReturn($this->rootMenu);
    }

    /**
     * 测试没有任何菜单提供者时的情况
     */
    public function test_mainMenu_withNoProviders_returnsRootMenu(): void
    {
        $this->menuCollector = new DefaultMenuCollector([], $this->factory);
        
        $result = $this->menuCollector->mainMenu();
        
        $this->assertSame($this->rootMenu, $result);
    }

    /**
     * 测试有菜单提供者时的情况
     */
    public function test_mainMenu_withProviders_callsProviders(): void
    {
        $provider1Called = false;
        $provider2Called = false;
        
        $provider1 = function ($rootMenu) use (&$provider1Called) {
            $provider1Called = true;
            $this->assertSame($this->rootMenu, $rootMenu);
        };
        
        $provider2 = function ($rootMenu) use (&$provider2Called) {
            $provider2Called = true;
            $this->assertSame($this->rootMenu, $rootMenu);
        };
        
        $this->menuCollector = new DefaultMenuCollector([$provider1, $provider2], $this->factory);
        
        $result = $this->menuCollector->mainMenu();
        
        $this->assertSame($this->rootMenu, $result);
        $this->assertTrue($provider1Called, 'Provider 1 should be called');
        $this->assertTrue($provider2Called, 'Provider 2 should be called');
    }

    /**
     * 测试带用户参数调用的情况
     */
    public function test_mainMenu_withUser_returnsRootMenu(): void
    {
        $user = $this->createMock(UserInterface::class);
        $this->menuCollector = new DefaultMenuCollector([], $this->factory);
        
        $result = $this->menuCollector->mainMenu($user);
        
        $this->assertSame($this->rootMenu, $result);
    }

    /**
     * 测试混合可调用和非可调用提供者的情况
     */
    public function test_mainMenu_withMixedProviders_onlyCallsCallable(): void
    {
        $providerCalled = false;
        
        $callableProvider = function ($rootMenu) use (&$providerCalled) {
            $providerCalled = true;
            $this->assertSame($this->rootMenu, $rootMenu);
        };
        
        $nonCallableProvider = new \stdClass();
        
        $this->menuCollector = new DefaultMenuCollector(
            [$callableProvider, $nonCallableProvider],
            $this->factory
        );
        
        $result = $this->menuCollector->mainMenu();
        
        $this->assertSame($this->rootMenu, $result);
        $this->assertTrue($providerCalled, 'Callable provider should be called');
    }
}