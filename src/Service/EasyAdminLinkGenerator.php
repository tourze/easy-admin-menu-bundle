<?php

namespace Tourze\EasyAdminMenuBundle\Service;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * EasyAdmin链接生成器
 */
#[AsAlias(id: LinkGeneratorInterface::class)]
class EasyAdminLinkGenerator implements LinkGeneratorInterface
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
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController($entityClass)
            ->setAction(Action::INDEX)
            ->generateUrl();
    }

    /**
     * 从URL中提取实体类名
     */
    public function extractEntityFqcn(string $url): ?string
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
