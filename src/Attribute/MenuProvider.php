<?php

namespace Tourze\EasyAdminMenuBundle\Attribute;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MenuProvider extends AutoconfigureTag
{
    const TAG_NAME = 'easy-admin-menu.provider';

    public function __construct()
    {
        parent::__construct(self::TAG_NAME);
    }
}
