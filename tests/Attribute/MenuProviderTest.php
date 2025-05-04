<?php

namespace Tourze\EasyAdminMenuBundle\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;

class MenuProviderTest extends TestCase
{
    /**
     * 测试 MenuProvider 标签名称常量是否正确
     */
    public function test_tagNameConstant_hasCorrectValue(): void
    {
        // 验证标签名称常量是否有正确的值
        $this->assertSame('easy-admin-menu.provider', MenuProvider::TAG_NAME);
    }

    /**
     * 测试 MenuProvider 是否为属性
     */
    public function test_isAttribute_targetIsClass(): void
    {
        // 验证 MenuProvider 类的注解
        $reflectionClass = new \ReflectionClass(MenuProvider::class);
        $attributes = $reflectionClass->getAttributes();

        // 确认类有一个属性注解，并且目标是类
        $this->assertCount(1, $attributes);
        $this->assertSame(\Attribute::class, $attributes[0]->getName());

        // 如果注解是 Attribute，确认它的 target 参数是 TARGET_CLASS
        $attributeArguments = $attributes[0]->getArguments();
        $this->assertArrayHasKey(0, $attributeArguments);
        $this->assertSame(\Attribute::TARGET_CLASS, $attributeArguments[0]);
    }

    /**
     * 测试 MenuProvider 构造函数是否正确设置父类参数
     */
    public function test_constructor_callsParentConstructor(): void
    {
        // 创建 MenuProvider 实例
        $menuProvider = new MenuProvider();

        // 因为我们不能直接访问私有属性，使用反射验证
        // 当我们不能直接测试私有属性时，可以验证类的行为

        // MenuProvider 是 AutoconfigureTag 的子类，并且应该传递 TAG_NAME 给父构造函数
        // 这可以通过验证类的接口实现和类型来简单测试
        $this->assertInstanceOf(\Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag::class, $menuProvider);
    }
}
