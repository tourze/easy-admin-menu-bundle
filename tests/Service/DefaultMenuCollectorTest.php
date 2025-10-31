<?php

namespace Tourze\EasyAdminMenuBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\EasyAdminMenuBundle\Service\DefaultMenuCollector;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(DefaultMenuCollector::class)]
#[RunTestsInSeparateProcesses]
final class DefaultMenuCollectorTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试初始化逻辑
    }

    /**
     * 测试服务能够正确获取和调用
     */
    public function testMenuCollectorServiceCanBeRetrieved(): void
    {
        $menuCollector = self::getService(DefaultMenuCollector::class);

        $this->assertInstanceOf(DefaultMenuCollector::class, $menuCollector);
    }

    /**
     * 测试主菜单方法返回菜单项实例
     */
    public function testMainMenuReturnsMenuItemInterface(): void
    {
        $menuCollector = self::getService(DefaultMenuCollector::class);

        $result = $menuCollector->mainMenu();

        $this->assertInstanceOf(ItemInterface::class, $result);
    }

    /**
     * 测试带用户参数调用主菜单
     */
    public function testMainMenuWithUser(): void
    {
        $menuCollector = self::getService(DefaultMenuCollector::class);
        $user = $this->createMock(UserInterface::class);

        $result = $menuCollector->mainMenu($user);

        $this->assertInstanceOf(ItemInterface::class, $result);
    }
}
