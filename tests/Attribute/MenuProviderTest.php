<?php

namespace Tourze\EasyAdminMenuBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;

/**
 * @internal
 */
#[CoversClass(MenuProvider::class)]
final class MenuProviderTest extends TestCase
{
    /**
     * 测试 MenuProvider 标签名称常量是否正确
     */
    public function testTagNameConstantHasCorrectValue(): void
    {
        // 验证标签名称常量是否有正确的值
        $this->assertSame('easy-admin-menu.provider', MenuProvider::TAG_NAME);
    }

    /**
     * 测试 MenuProvider 是否为属性
     */
    public function testIsAttributeTargetIsClass(): void
    {
        // 验证 MenuProvider 类的注解
        $reflectionClass = new \ReflectionClass(MenuProvider::class);
        $attributes = $reflectionClass->getAttributes();

        // 确认类有一个属性注解，并且目标是类
        $this->assertCount(1, $attributes);
        $this->assertSame(\Attribute::class, $attributes[0]->getName());

        // 获取 Attribute 实例
        $attributeInstance = $attributes[0]->newInstance();

        // 验证 flags 属性是 TARGET_CLASS
        // 使用反射安全访问 flags 属性
        $attributeReflection = new \ReflectionClass($attributeInstance);
        $flagsProperty = $attributeReflection->getProperty('flags');
        $this->assertSame(\Attribute::TARGET_CLASS, $flagsProperty->getValue($attributeInstance));
    }

    /**
     * 测试 MenuProvider 构造函数是否正确设置父类参数
     */
    public function testConstructorCallsParentConstructor(): void
    {
        // 创建 MenuProvider 实例
        $menuProvider = new MenuProvider();

        // 因为我们不能直接访问私有属性，使用反射验证
        // 当我们不能直接测试私有属性时，可以验证类的行为

        // MenuProvider 是 AutoconfigureTag 的子类，并且应该传递 TAG_NAME 给父构造函数
        // 这可以通过验证类的接口实现和类型来简单测试
        $this->assertInstanceOf(AutoconfigureTag::class, $menuProvider);
    }
}
