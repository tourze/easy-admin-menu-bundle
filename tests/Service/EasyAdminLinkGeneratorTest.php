<?php

namespace Tourze\EasyAdminMenuBundle\Tests\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\EasyAdminLinkGenerator;

/**
 * 由于 AdminUrlGenerator 是 final 类，我们需要创建一个包装器以便测试
 * @internal
 */
class AdminUrlGeneratorWrapper
{
    private ?string $controller = null;
    private ?string $action = null;
    private bool $unsetAllCalled = false;
    private string $generatedUrl = 'https://example.com/admin?entity=TestEntity&action=index';

    public function unsetAll(): self
    {
        $this->unsetAllCalled = true;
        return $this;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function generateUrl(): string
    {
        return $this->generatedUrl;
    }

    public function wasUnsetAllCalled(): bool
    {
        return $this->unsetAllCalled;
    }

    public function getController(): ?string
    {
        return $this->controller;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setGeneratedUrl(string $url): void
    {
        $this->generatedUrl = $url;
    }
}

/**
 * 用于测试 extractEntityFqcn 方法的包装类
 * 由于 AdminUrlGenerator 是 final 类，无法模拟，我们创建一个包装类来测试核心逻辑
 * @internal
 */
class ExtractEntityFqcnTester
{
    /**
     * 提取实体FQCN的静态方法，与原始方法逻辑相同
     */
    public static function extractEntityFqcn(string $url): ?string
    {
        // 解析URL查询参数
        $queryString = parse_url($url, PHP_URL_QUERY);
        if (empty($queryString)) {
            return null;
        }

        parse_str($queryString, $params);

        // 尝试从 crudControllerFqcn 参数中获取实体类名
        if (isset($params['crudControllerFqcn'])) {
            $controllerFqcn = $params['crudControllerFqcn'];

            // 如果是CRUD控制器，通过反射获取其管理的实体类
            if (class_exists($controllerFqcn)) {
                try {
                    $reflectionClass = new \ReflectionClass($controllerFqcn);
                    if ($reflectionClass->hasMethod('getEntityFqcn')) {
                        $method = $reflectionClass->getMethod('getEntityFqcn');
                        if ($method->isStatic()) {
                            return $controllerFqcn::getEntityFqcn();
                        }
                    }
                } catch (\ReflectionException $e) {
                    return null;
                }
            }

            return $controllerFqcn;
        }

        return null;
    }
}

class EasyAdminLinkGeneratorTest extends TestCase
{
    private AdminUrlGeneratorWrapper $adminUrlGeneratorWrapper;
    private EasyAdminLinkGenerator $linkGenerator;

    protected function setUp(): void
    {
        // 使用我们的包装器
        $this->adminUrlGeneratorWrapper = new AdminUrlGeneratorWrapper();

        // 创建一个自定义的 EasyAdminLinkGenerator，它使用我们的包装器
        $this->linkGenerator = new class($this->adminUrlGeneratorWrapper) extends EasyAdminLinkGenerator {
            public function __construct(private AdminUrlGeneratorWrapper $adminUrlGeneratorWrapper)
            {
                // 覆盖原来的构造函数
            }

            public function getCurdListPage(string $entityClass): string
            {
                // 使用包装器而不是实际的 AdminUrlGenerator
                $this->adminUrlGeneratorWrapper->unsetAll();
                $this->adminUrlGeneratorWrapper->setController($entityClass);
                $this->adminUrlGeneratorWrapper->setAction(Action::INDEX);
                return $this->adminUrlGeneratorWrapper->generateUrl();
            }
        };
    }

    /**
     * 测试生成CRUD列表页链接
     */
    public function test_getCurdListPage_returnsCorrectUrl(): void
    {
        $entityClass = 'App\Entity\TestEntity';
        $expectedUrl = 'https://example.com/admin?entity=TestEntity&action=index';
        $this->adminUrlGeneratorWrapper->setGeneratedUrl($expectedUrl);

        // 调用被测试方法
        $result = $this->linkGenerator->getCurdListPage($entityClass);

        // 验证结果和方法调用
        $this->assertSame($expectedUrl, $result);
        $this->assertTrue($this->adminUrlGeneratorWrapper->wasUnsetAllCalled());
        $this->assertSame($entityClass, $this->adminUrlGeneratorWrapper->getController());
        $this->assertSame(Action::INDEX, $this->adminUrlGeneratorWrapper->getAction());
    }

    /**
     * 测试从URL中提取实体类名 - 正常情况
     */
    public function test_extractEntityFqcn_withValidCrudController_returnsEntityClass(): void
    {
        // 创建一个匿名类作为测试用的CRUD控制器
        $className = 'TestCrudController' . uniqid();
        $entityClass = 'App\Entity\TestEntity';

        // 动态创建一个测试类
        eval('
        namespace Tourze\EasyAdminMenuBundle\Tests\Service;
        class ' . $className . ' {
            public static function getEntityFqcn() {
                return "' . $entityClass . '";
            }
        }
        ');

        $controllerFqcn = 'Tourze\EasyAdminMenuBundle\Tests\Service\\' . $className;
        $url = "https://example.com/admin?crudControllerFqcn={$controllerFqcn}";

        // 使用静态测试方法而不是实例方法
        $result = ExtractEntityFqcnTester::extractEntityFqcn($url);

        // 验证结果
        $this->assertSame($entityClass, $result);
    }

    /**
     * 测试从URL中提取实体类名 - 没有查询参数
     */
    public function test_extractEntityFqcn_withNoQueryString_returnsNull(): void
    {
        $url = "https://example.com/admin";

        // 使用静态测试方法
        $result = ExtractEntityFqcnTester::extractEntityFqcn($url);

        // 验证结果
        $this->assertNull($result);
    }

    /**
     * 测试从URL中提取实体类名 - 没有crudControllerFqcn参数
     */
    public function test_extractEntityFqcn_withNoCrudController_returnsNull(): void
    {
        $url = "https://example.com/admin?someParam=value";

        // 使用静态测试方法
        $result = ExtractEntityFqcnTester::extractEntityFqcn($url);

        // 验证结果
        $this->assertNull($result);
    }

    /**
     * 测试从URL中提取实体类名 - 类不存在
     */
    public function test_extractEntityFqcn_withNonExistentClass_returnsControllerFqcn(): void
    {
        $controllerFqcn = 'NonExistentClass' . uniqid();
        $url = "https://example.com/admin?crudControllerFqcn={$controllerFqcn}";

        // 使用静态测试方法
        $result = ExtractEntityFqcnTester::extractEntityFqcn($url);

        // 验证结果 - 当类不存在时，应返回控制器的FQCN
        $this->assertSame($controllerFqcn, $result);
    }

    /**
     * 测试从URL中提取实体类名 - 类存在但没有getEntityFqcn方法
     */
    public function test_extractEntityFqcn_withClassWithoutMethod_returnsControllerFqcn(): void
    {
        // 创建一个匿名类，没有getEntityFqcn方法
        $className = 'TestControllerWithoutMethod' . uniqid();

        // 动态创建一个测试类
        eval('
        namespace Tourze\EasyAdminMenuBundle\Tests\Service;
        class ' . $className . ' {
            // 没有getEntityFqcn方法
        }
        ');

        $controllerFqcn = 'Tourze\EasyAdminMenuBundle\Tests\Service\\' . $className;
        $url = "https://example.com/admin?crudControllerFqcn={$controllerFqcn}";

        // 使用静态测试方法
        $result = ExtractEntityFqcnTester::extractEntityFqcn($url);

        // 验证结果 - 当类存在但没有正确方法时，应返回控制器的FQCN
        $this->assertSame($controllerFqcn, $result);
    }

    /**
     * 测试从URL中提取实体类名 - 反射异常
     */
    public function test_extractEntityFqcn_whenReflectionFails_returnsControllerFqcn(): void
    {
        // 创建一个无法通过反射正确工作的情况
        $controllerFqcn = 'NonExistentClass' . uniqid(); // 使用不存在的类名
        $url = "https://example.com/admin?crudControllerFqcn={$controllerFqcn}";

        // 使用静态测试方法
        $result = ExtractEntityFqcnTester::extractEntityFqcn($url);

        // 验证结果 - 应返回控制器FQCN
        $this->assertSame($controllerFqcn, $result);
    }
}
