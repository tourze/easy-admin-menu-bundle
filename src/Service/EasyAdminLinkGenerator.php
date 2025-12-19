<?php

namespace Tourze\EasyAdminMenuBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * EasyAdmin链接生成器
 */
#[AsAlias(id: LinkGeneratorInterface::class)]
#[Autoconfigure(public: true)]
final class EasyAdminLinkGenerator implements LinkGeneratorInterface
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * 生成实体的列表页链接
     */
    public function getCurdListPage(string $entityClass): string
    {
        $generator = $this->adminUrlGenerator
            ->unsetAll()
            ->setController($entityClass)
            ->setAction(Action::INDEX)
        ;

        if ($this->isTestEnvironment()) {
            $defaultDashboard = $this->getDefaultDashboard();
            if (null !== $defaultDashboard) {
                $generator->setDashboard($defaultDashboard);
            }

            return $this->toRelativeUrl($generator->generateUrl());
        }

        $defaultDashboard = $this->getDefaultDashboard();
        if (null !== $defaultDashboard) {
            $generator->setDashboard($defaultDashboard);
        }

        return $this->toRelativeUrl($generator->generateUrl());
    }

      private ?string $defaultDashboardFqcn = null;

    /**
     * 检查是否在测试环境中
     */
    private function isTestEnvironment(): bool
    {
        return 'test' === ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null);
    }

    /**
     * 设置默认 Dashboard
     */
    public function setDashboard(string $dashboardControllerFqcn): void
    {
        $this->defaultDashboardFqcn = $dashboardControllerFqcn;
    }

    /**
     * 获取当前默认 Dashboard
     */
    public function getDefaultDashboard(): ?string
    {
        return $this->defaultDashboardFqcn;
    }

    /**
     * 从URL中提取实体类名
     */
    public function extractEntityFqcn(string $url): ?string
    {
        $params = $this->parseUrlQueryParameters($url);
        if (null === $params) {
            return null;
        }

        if (!isset($params['crudControllerFqcn'])) {
            return null;
        }

        $controllerFqcn = $params['crudControllerFqcn'];

        return $this->extractEntityFromController($controllerFqcn);
    }

    /**
     * 解析URL查询参数
     * @return array<string, string>|null
     */
    private function parseUrlQueryParameters(string $url): ?array
    {
        $queryString = parse_url($url, PHP_URL_QUERY);
        if (null === $queryString || '' === $queryString || false === $queryString) {
            return null;
        }

        parse_str($queryString, $params);

        // 确保只返回字符串类型的值
        $result = [];
        foreach ($params as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $result[$key] = $value;
            }
        }

        return [] === $result ? null : $result;
    }

    private function toRelativeUrl(string $url): string
    {
        $components = parse_url($url);
        if (false === $components) {
            return $url;
        }

        $path = $components['path'] ?? '';
        $query = isset($components['query']) ? '?' . $components['query'] : '';
        $fragment = isset($components['fragment']) ? '#' . $components['fragment'] : '';

        return $path . $query . $fragment;
    }

    /**
     * 从控制器类中提取实体类名
     */
    private function extractEntityFromController(string $controllerFqcn): string
    {
        if (!class_exists($controllerFqcn)) {
            return $controllerFqcn;
        }

        $entityFqcn = $this->getEntityFqcnFromController($controllerFqcn);
        if (null !== $entityFqcn) {
            return $entityFqcn;
        }

        return $controllerFqcn;
    }

    /**
     * 通过反射从控制器获取实体类名
     */
    private function getEntityFqcnFromController(string $controllerFqcn): ?string
    {
        try {
            if (!class_exists($controllerFqcn)) {
                return null;
            }
            /** @var class-string $controllerFqcn */
            $reflectionClass = new \ReflectionClass($controllerFqcn);
            if (!$reflectionClass->hasMethod('getEntityFqcn')) {
                return null;
            }

            $method = $reflectionClass->getMethod('getEntityFqcn');
            if (!$method->isStatic()) {
                return null;
            }

            return $controllerFqcn::getEntityFqcn();
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
