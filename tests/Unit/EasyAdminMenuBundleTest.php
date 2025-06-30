<?php

namespace Tourze\EasyAdminMenuBundle\Tests\Unit;

use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;

class EasyAdminMenuBundleTest extends TestCase
{
    private EasyAdminMenuBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new EasyAdminMenuBundle();
    }

    /**
     * 测试Bundle依赖配置是否正确
     */
    public function test_getBundleDependencies_returnsCorrectDependencies(): void
    {
        $dependencies = EasyAdminMenuBundle::getBundleDependencies();

        $expectedDependencies = [
            EasyAdminBundle::class => ['all' => true],
            TwigBundle::class => ['all' => true],
            SecurityBundle::class => ['all' => true],
        ];

        $this->assertSame($expectedDependencies, $dependencies);
    }

    /**
     * 测试Bundle依赖包含必要的EasyAdmin Bundle
     */
    public function test_getBundleDependencies_includesEasyAdminBundle(): void
    {
        $dependencies = EasyAdminMenuBundle::getBundleDependencies();

        $this->assertArrayHasKey(EasyAdminBundle::class, $dependencies);
        $this->assertSame(['all' => true], $dependencies[EasyAdminBundle::class]);
    }

    /**
     * 测试Bundle依赖包含必要的Twig Bundle
     */
    public function test_getBundleDependencies_includesTwigBundle(): void
    {
        $dependencies = EasyAdminMenuBundle::getBundleDependencies();

        $this->assertArrayHasKey(TwigBundle::class, $dependencies);
        $this->assertSame(['all' => true], $dependencies[TwigBundle::class]);
    }

    /**
     * 测试Bundle依赖包含必要的Security Bundle
     */
    public function test_getBundleDependencies_includesSecurityBundle(): void
    {
        $dependencies = EasyAdminMenuBundle::getBundleDependencies();

        $this->assertArrayHasKey(SecurityBundle::class, $dependencies);
        $this->assertSame(['all' => true], $dependencies[SecurityBundle::class]);
    }

    /**
     * 测试Bundle继承自正确的父类
     */
    public function test_bundle_extendsSymfonyBundle(): void
    {
        $this->assertInstanceOf(\Symfony\Component\HttpKernel\Bundle\Bundle::class, $this->bundle);
    }

    /**
     * 测试Bundle实现了BundleDependencyInterface
     */
    public function test_bundle_implementsBundleDependencyInterface(): void
    {
        $this->assertInstanceOf(\Tourze\BundleDependency\BundleDependencyInterface::class, $this->bundle);
    }
}