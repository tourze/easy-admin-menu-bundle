<?php

namespace Tourze\EasyAdminMenuBundle\Service;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsAlias(id: MenuCollectorInterface::class)]
class DefaultMenuCollector implements MenuCollectorInterface
{
    public function __construct(
        #[TaggedIterator('easy-admin-menu.provider')] private readonly iterable $providers,
        #[Autowire(service: 'easy-admin-menu.factory')] private readonly FactoryInterface $factory,
    )
    {
    }

    /**
     * @return object[]|MenuProviderInterface[]
     */
    private function getMenus(): iterable
    {
        return $this->providers;
    }

    public function mainMenu(?UserInterface $user = null): ItemInterface
    {
        $rootMenu = $this->factory->createItem('root');

        foreach ($this->getMenus() as $menu) {
            if (is_callable($menu)) {
                $menu($rootMenu);
            }
        }

        return $rootMenu;
    }
}
