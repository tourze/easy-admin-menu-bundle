<?php

namespace Tourze\EasyAdminMenuBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'easy-admin-menu.provider')]
interface MenuProviderInterface
{
    public function __invoke(ItemInterface $item): void;
}
