# EasyAdminMenuBundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/packagist/php-v/tourze/easy-admin-menu-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-menu-bundle)
[![Latest Version](https://img.shields.io/packagist/v/tourze/easy-admin-menu-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-menu-bundle)
[![License](https://img.shields.io/packagist/l/tourze/easy-admin-menu-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-menu-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/easy-admin-menu-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/easy-admin-menu-bundle)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/php-monorepo.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/php-monorepo)

A Symfony bundle for simplified menu management in EasyAdmin backend systems. 
This bundle provides easy-to-use tools for organizing menus and generating admin links.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Advanced Usage](#advanced-usage)
- [API Reference](#api-reference)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- Simplified menu organization and management
- Attribute-based menu provider registration with `#[MenuProvider]`
- Easy link generation for EasyAdmin CRUD pages
- Entity class extraction from URLs
- Integration with KnpMenuBundle for menu rendering
- Automatic service configuration and DI

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- EasyAdmin Bundle 4.x
- KnpMenuBundle 3.7 or higher

## Installation

Install the bundle using Composer:

```bash
composer require tourze/easy-admin-menu-bundle
```

The bundle will be automatically registered if you're using Symfony Flex. 
Otherwise, add it manually to `config/bundles.php`:

```php
// config/bundles.php
return [
    // ...
    Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle::class => ['all' => true],
];
```

## Configuration

The bundle works out of the box with default configuration. 
No additional configuration is required for basic usage.

### Custom Menu Factory

You can customize the menu factory by defining your own service:

```yaml
# config/services.yaml
services:
    easy-admin-menu.factory:
        class: Knp\Menu\MenuFactory
        # Add your custom configuration here
```

## Quick Start

### 1. Create a Menu Provider

Create a menu provider class and mark it with the `#[MenuProvider]` attribute:

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
        $item->addChild('Dashboard', [
            'route' => 'admin_dashboard'
        ]);
        
        $item->addChild('Users', [
            'route' => 'admin',
            'routeParameters' => [
                'crudAction' => 'index',
                'crudControllerFqcn' => UserCrudController::class
            ]
        ]);
    }
}
```

## 2. Generate Admin Links

Use the `LinkGeneratorInterface` to generate EasyAdmin CRUD links:

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
        // Generate list page URL for User entity
        $userListUrl = $this->linkGenerator->getCurdListPage(User::class);
        
        // Extract entity class from URL
        $someUrl = 'https://example.com/admin?crudControllerFqcn=App\\Controller\\Admin\\UserCrudController';
        $entityClass = $this->linkGenerator->extractEntityFqcn($someUrl);
        // Result: App\Entity\User (if UserCrudController has getEntityFqcn() method)
        
        return $this->render('dashboard/index.html.twig', [
            'userListUrl' => $userListUrl,
        ]);
    }
}
```

## 3. Collect and Render Menus

The bundle automatically collects all menu providers and makes them available 
through the `MenuCollectorInterface`:

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

## Advanced Usage

### Multiple Menu Providers

You can create multiple menu providers for different sections:

```php
#[MenuProvider]
class AdminMenuProvider implements MenuProviderInterface
{
    public function __invoke(ItemInterface $item): void
    {
        $item->addChild('Admin Section', ['uri' => '#'])
            ->addChild('Users', ['route' => 'admin_users'])
            ->addChild('Settings', ['route' => 'admin_settings']);
    }
}

#[MenuProvider]
class ContentMenuProvider implements MenuProviderInterface
{
    public function __invoke(ItemInterface $item): void
    {
        $item->addChild('Content', ['uri' => '#'])
            ->addChild('Articles', ['route' => 'admin_articles'])
            ->addChild('Categories', ['route' => 'admin_categories']);
    }
}
```

## API Reference

### LinkGeneratorInterface

- `getCurdListPage(string $entityClass): string` - Generate CRUD list page URL for an entity
- `extractEntityFqcn(string $url): ?string` - Extract entity FQCN from admin URL

### MenuProviderInterface

- `__invoke(ItemInterface $item): void` - Add menu items to the root menu

### MenuCollectorInterface

- `mainMenu(?UserInterface $user = null): ItemInterface` - Collect and return the main menu

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/easy-admin-menu-bundle/tests
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.