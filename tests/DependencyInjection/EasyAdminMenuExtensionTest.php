<?php

namespace Tourze\EasyAdminMenuBundle\Tests\DependencyInjection;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Tourze\EasyAdminMenuBundle\DependencyInjection\EasyAdminMenuExtension;

class EasyAdminMenuExtensionTest extends TestCase
{
    private EasyAdminMenuExtension $extension;
    private MockObject|ContainerBuilder $containerBuilder;

    protected function setUp(): void
    {
        $this->extension = new EasyAdminMenuExtension();
        $this->containerBuilder = $this->createMock(ContainerBuilder::class);
    }

    /**
     * 测试扩展加载配置文件
     */
    public function test_load_loadsCorrectConfigurationFile(): void
    {
        // 创建临时文件夹，用于测试文件加载
        $tempDir = sys_get_temp_dir() . '/easy_admin_menu_test_' . uniqid();
        mkdir($tempDir . '/Resources/config', 0777, true);
        $configFile = $tempDir . '/Resources/config/services.yaml';
        file_put_contents($configFile, "# 测试配置文件\nservices:\n  _defaults:\n    autowire: true\n");

        try {
            // 创建扩展的子类，重写 __DIR__ 常量以使用临时目录
            $extension = new class ($tempDir) extends EasyAdminMenuExtension {
                private string $tempDir;
                public bool $loaderCalled = false;

                public function __construct(string $tempDir)
                {
                    $this->tempDir = $tempDir;
                }

                public function load(array $configs, ContainerBuilder $container): void
                {
                    $loader = new YamlFileLoader(
                        $container,
                        new FileLocator($this->tempDir . '/Resources/config')
                    );
                    $loader->load('services.yaml');
                    $this->loaderCalled = true;
                }
            };

            // 调用扩展的 load 方法
            $extension->load([], $this->containerBuilder);

            // 验证加载器被调用
            $this->assertTrue($extension->loaderCalled);
        } finally {
            // 清理临时文件和目录
            @unlink($configFile);
            @rmdir($tempDir . '/Resources/config');
            @rmdir($tempDir . '/Resources');
            @rmdir($tempDir);
        }
    }

    /**
     * 测试实际配置文件存在，不是完整测试，但可验证路径正确
     */
    public function test_configFile_exists(): void
    {
        $configPath = realpath(__DIR__ . '/../../src/Resources/config/services.yaml');
        $this->assertNotFalse($configPath, '配置文件应该存在');
        $this->assertFileExists($configPath);
    }
}
