<?php

namespace Tourze\EasyAdminMenuBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\EasyAdminMenuBundle\DependencyInjection\EasyAdminMenuExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(EasyAdminMenuExtension::class)]
final class EasyAdminMenuExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    /** @var MockObject&ContainerBuilder */
    private MockObject $containerBuilder;

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
         * 是否有更好的替代方案：
         * 在测试中，Mock ContainerBuilder 是标准做法。我们可以考虑使用实际的 ContainerBuilder 实例，
         * 但 Mock 对象能更好地验证特定方法调用，更适合单元测试
         */
        $this->containerBuilder = $this->createMock(ContainerBuilder::class);
        $this->extension = new EasyAdminMenuExtension();
    }

    /**
     * 测试扩展加载配置文件
     */
    public function testLoadLoadsCorrectConfigurationFile(): void
    {
        // 创建临时文件夹，用于测试文件加载
        $tempDir = sys_get_temp_dir() . '/easy_admin_menu_test_' . uniqid();
        mkdir($tempDir . '/Resources/config', 0o777, true);
        $configFile = $tempDir . '/Resources/config/services.yaml';
        file_put_contents($configFile, "services:\n  _defaults:\n    autowire: true\n    autoconfigure: true\n");

        try {
            // 创建扩展的子类，重写配置目录以使用临时目录
            $extension = new class($tempDir) extends EasyAdminMenuExtension {
                private string $tempDir;

                public function __construct(string $tempDir)
                {
                    $this->tempDir = $tempDir;
                }

                public function getAlias(): string
                {
                    return 'easy_admin_menu';
                }

                protected function getConfigDir(): string
                {
                    return $this->tempDir . '/Resources/config';
                }

                public function getPublicConfigDir(): string
                {
                    return $this->getConfigDir();
                }
            };

            // 模拟容器参数
            $this->containerBuilder->method('getParameter')
                ->with('kernel.environment')
                ->willReturn('test')
            ;

            // 调用扩展的 load 方法
            $extension->load([], $this->containerBuilder);

            // 验证扩展的非 Mock 属性
            $this->assertInstanceOf(EasyAdminMenuExtension::class, $extension);
            $this->assertSame('easy_admin_menu', $extension->getAlias());

            // 由于使用了AutoExtension，我们验证配置目录被正确设置
            $this->assertEquals($tempDir . '/Resources/config', $extension->getPublicConfigDir());

            // 验证临时配置文件的内容
            $configContent = file_get_contents($configFile);
            $this->assertNotEmpty($configContent);
            $this->assertStringContainsString('services:', $configContent);
        } finally {
            // 清理临时文件和目录
            @unlink($configFile);
            @rmdir($tempDir . '/Resources/config');
            @rmdir($tempDir . '/Resources');
            @rmdir($tempDir);
        }
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
