# EasyAdminMenuBundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/packagist/php-v/tourze/easy-admin-menu-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-menu-bundle)
[![Latest Version](https://img.shields.io/packagist/v/tourze/easy-admin-menu-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-menu-bundle)
[![License](https://img.shields.io/packagist/l/tourze/easy-admin-menu-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-menu-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/easy-admin-menu-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-menu-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)

专为 EasyAdmin 后台管理系统设计的 Symfony 菜单管理包。
该包提供了便捷的工具来组织菜单和生成管理员链接。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [配置](#配置)
- [快速开始](#快速开始)
- [高级用法](#高级用法)
- [API 参考](#api-参考)
- [测试](#测试)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- 简化的菜单组织和管理
- 基于属性的菜单提供者注册 `#[MenuProvider]`
- 便捷的 EasyAdmin CRUD 页面链接生成
- 从 URL 中提取实体类信息
- 与 KnpMenuBundle 集成进行菜单渲染
- 自动服务配置和依赖注入

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- EasyAdmin Bundle 4.x
- KnpMenuBundle 3.7 或更高版本

## 安装

使用 Composer 安装：

```bash
composer require tourze/easy-admin-menu-bundle
```

如果您使用 Symfony Flex，该包将自动注册。
否则，请手动添加到 `config/bundles.php`：

```php
// config/bundles.php
return [
    // ...
    Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle::class => ['all' => true],
];
```

## 配置

该包开箱即用，具有默认配置。
基本使用无需额外配置。

### 自定义菜单工厂

您可以通过定义自己的服务来自定义菜单工厂：

```yaml
# config/services.yaml
services:
    easy-admin-menu.factory:
        class: Knp\Menu\MenuFactory
        # 在此添加您的自定义配置
```

## 快速开始

### 1. 创建菜单提供者

创建一个菜单提供者类并使用 `#[MenuProvider]` 属性标记：

```php
<?php

namespace App\Menu;

use Knp\Menu\ItemInterface;
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[MenuProvider]
class MainMenuProvider implements MenuProviderInterface
{
    public function __invoke(ItemInterface $item): void
    {
        $item->addChild('控制台', [
            'route' => 'admin_dashboard'
        ]);
        
        $item->addChild('用户管理', [
            'route' => 'admin',
            'routeParameters' => [
                'crudAction' => 'index',
                'crudControllerFqcn' => UserCrudController::class
            ]
        ]);
    }
}
```

## 2. 生成管理员链接

使用 `LinkGeneratorInterface` 生成 EasyAdmin CRUD 链接：

```php
<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
    ) {
    }
    
    public function index()
    {
        // 为 User 实体生成列表页面 URL
        $userListUrl = $this->linkGenerator->getCurdListPage(User::class);
        
        // 从 URL 中提取实体类
        $someUrl = 'https://example.com/admin?crudControllerFqcn=App\\Controller\\Admin\\UserCrudController';
        $entityClass = $this->linkGenerator->extractEntityFqcn($someUrl);
        // 结果: App\Entity\User (如果 UserCrudController 有 getEntityFqcn() 方法)
        
        return $this->render('dashboard/index.html.twig', [
            'userListUrl' => $userListUrl,
        ]);
    }
}
```

## 3. 收集和渲染菜单

该包自动收集所有菜单提供者，并通过 `MenuCollectorInterface` 使其可用：

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Tourze\EasyAdminMenuBundle\Service\MenuCollectorInterface;

class MenuController extends AbstractController
{
    public function __construct(
        private readonly MenuCollectorInterface $menuCollector,
    ) {
    }
    
    public function mainMenu()
    {
        $menu = $this->menuCollector->mainMenu($this->getUser());
        
        return $this->render('menu/main.html.twig', [
            'menu' => $menu,
        ]);
    }
}
```

## 高级用法

### 多个菜单提供者

您可以为不同的部分创建多个菜单提供者：

```php
#[MenuProvider]
class AdminMenuProvider implements MenuProviderInterface
{
    public function __invoke(ItemInterface $item): void
    {
        $item->addChild('管理员功能', ['uri' => '#'])
            ->addChild('用户管理', ['route' => 'admin_users'])
            ->addChild('系统设置', ['route' => 'admin_settings']);
    }
}

#[MenuProvider]
class ContentMenuProvider implements MenuProviderInterface
{
    public function __invoke(ItemInterface $item): void
    {
        $item->addChild('内容管理', ['uri' => '#'])
            ->addChild('文章管理', ['route' => 'admin_articles'])
            ->addChild('分类管理', ['route' => 'admin_categories']);
    }
}
```

## API 参考

### LinkGeneratorInterface

- `getCurdListPage(string $entityClass): string` - 为实体生成 CRUD 列表页面 URL
- `extractEntityFqcn(string $url): ?string` - 从管理员 URL 中提取实体 FQCN

### MenuProviderInterface

- `__invoke(ItemInterface $item): void` - 向根菜单添加菜单项

### MenuCollectorInterface

- `mainMenu(?UserInterface $user = null): ItemInterface` - 收集并返回主菜单

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/easy-admin-menu-bundle/tests
```

## 贡献

请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解如何为此项目做出贡献的详细信息。

## 许可证

MIT 许可证 (MIT)。请查看 [许可证文件](LICENSE) 获取更多信息。