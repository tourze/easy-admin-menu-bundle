# EasyAdminMenuBundle

EasyAdminMenuBundle 是一个 Symfony 包，用于简化 EasyAdmin 后台管理系统的菜单管理。

## 功能特点

- 提供菜单组织和管理功能
- 通过属性标签注册菜单提供者
- 简化菜单链接生成
- 支持从 URL 中提取实体类信息

## 安装

使用 Composer 安装:

```bash
composer require tourze/easy-admin-menu-bundle
```

## 使用方法

### 基本用法

在 Symfony 应用中注册 bundle:

```php
// config/bundles.php
return [
    // ...
    Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle::class => ['all' => true],
];
```

### 链接生成器

```php
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

class YourController
{
    public function __construct(
        private readonly LinkGeneratorInterface $linkGenerator,
    ) {
    }
    
    public function someAction()
    {
        // 生成实体的列表页链接
        $listUrl = $this->linkGenerator->getCurdListPage(YourEntity::class);
        
        // 从URL中提取实体类信息
        $entityClass = $this->linkGenerator->extractEntityFqcn($someUrl);
    }
}
```

### 菜单提供者

使用 `MenuProvider` 属性标记菜单提供者类:

```php
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;

#[MenuProvider]
class YourMenuProvider
{
    // 实现菜单提供逻辑
}
```

## 开发

### 运行测试

在项目根目录执行以下命令运行测试:

```bash
./vendor/bin/phpunit packages/easy-admin-menu-bundle/tests
```

## 许可证

此包基于 MIT 许可证发布。详情请查看 [LICENSE](LICENSE) 文件。
