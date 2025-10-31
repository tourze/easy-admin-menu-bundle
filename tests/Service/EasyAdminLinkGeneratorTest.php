<?php

namespace Tourze\EasyAdminMenuBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\EasyAdminLinkGenerator;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(EasyAdminLinkGenerator::class)]
#[RunTestsInSeparateProcesses]
final class EasyAdminLinkGeneratorTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试初始化逻辑
    }

    /**
     * 测试服务能够正确获取和调用
     */
    public function testLinkGeneratorServiceCanBeRetrieved(): void
    {
        $linkGenerator = self::getService(EasyAdminLinkGenerator::class);

        $this->assertInstanceOf(EasyAdminLinkGenerator::class, $linkGenerator);
    }

    /**
     * 测试从URL中提取实体类名方法存在
     */
    public function testExtractEntityFqcnMethodExists(): void
    {
        $linkGenerator = self::getService(EasyAdminLinkGenerator::class);
        $url = 'https://example.com/admin?someParam=value';

        $result = $linkGenerator->extractEntityFqcn($url);

        // 基本测试 - 方法能正确调用
        $this->assertNull($result);
    }
}
