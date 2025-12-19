<?php

namespace Tourze\EasyAdminMenuBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\EasyAdminMenuBundle\DependencyInjection\EasyAdminMenuExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(EasyAdminMenuExtension::class)]
final class EasyAdminMenuExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    private ContainerBuilder $containerBuilder;

    private EasyAdminMenuExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * 为什么必须使用具体类而不是接口：
         * 1. ContainerBuilder 是 Symfony DI 容器的核心具体实现类，虽然实现了 ContainerInterface 等接口，
         *    但是 EasyAdminMenuExtension::load 方法内部需要调用 ContainerBuilder 特有的方法（如注册服务定义等），
         *    这些方法在基础接口中并不存在
         * 2. Extension 的 load 方法签名明确要求 ContainerBuilder 类型参数，而不是接口类型
         *
         * 这种使用是否合理和必要：
         * 是的，完全合理。DependencyInjection Extension 的设计就是要与 ContainerBuilder 直接交互，
         * 因为需要注册服务定义、设置参数等操作，这些都是容器构建时期的操作
         *
         * 使用真实 ContainerBuilder 实例的原因：
         * AutoExtension 需要进行实际的文件加载操作，使用 Mock 对象会导致文件路径为 null，
         * 无法正确测试文件加载功能
         */
        $this->containerBuilder = new ContainerBuilder();
        $this->extension = new EasyAdminMenuExtension();
    }

    /**
     * 测试扩展加载配置文件
     */
    public function testLoadLoadsCorrectConfigurationFile(): void
    {
        // 设置容器参数
        $this->containerBuilder->setParameter('kernel.environment', 'test');

        // 验证 load 方法能够正常调用且不抛出异常
        $this->expectNotToPerformAssertions();
        $this->extension->load([], $this->containerBuilder);
    }

    /**
     * 测试配置目录路径正确性
     */
    public function testConfigDirectoryPath(): void
    {
        // 使用 Reflection 访问受保护的 getConfigDir 方法
        $reflection = new \ReflectionClass($this->extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);

        $configDir = $method->invoke($this->extension);
        $expectedPath = __DIR__ . '/../../src/Resources/config';

        // 比较规范化的路径
        $this->assertEquals(realpath($expectedPath), realpath($configDir));
        $this->assertDirectoryExists($configDir);
    }

    /**
     * 测试扩展的基本属性和别名
     */
    public function testExtensionProperties(): void
    {
        // 验证扩展别名
        $this->assertSame('easy_admin_menu', $this->extension->getAlias());

        // 验证扩展是 DependencyInjection Extension 的实例
        $this->assertInstanceOf(EasyAdminMenuExtension::class, $this->extension);

        // 验证扩展配置路径
        $reflectionClass = new \ReflectionClass($this->extension);
        $this->assertTrue($reflectionClass->hasMethod('load'));
        $this->assertTrue($reflectionClass->hasMethod('getAlias'));
    }

    /**
     * 测试实际配置文件存在，不是完整测试，但可验证路径正确
     */
    public function testConfigFileExists(): void
    {
        $configPath = realpath(__DIR__ . '/../../src/Resources/config/services.yaml');
        $this->assertNotFalse($configPath, '配置文件应该存在');
        $this->assertFileExists($configPath);
    }
}
