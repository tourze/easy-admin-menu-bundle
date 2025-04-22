<?php

namespace Tourze\EasyAdminMenuBundle\Attribute;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MenuProvider extends AutoconfigureTag
{
    public function __construct()
    {
        parent::__construct('app.admin.menu');
    }
}
