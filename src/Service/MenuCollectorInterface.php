<?php

namespace Tourze\EasyAdminMenuBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface MenuCollectorInterface
{
    public function mainMenu(?UserInterface $user = null): ItemInterface;
}
