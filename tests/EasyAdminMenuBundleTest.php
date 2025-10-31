<?php

declare(strict_types=1);

namespace Tourze\EasyAdminMenuBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\EasyAdminMenuBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(EasyAdminMenuBundle::class)]
#[RunTestsInSeparateProcesses]
final class EasyAdminMenuBundleTest extends AbstractBundleTestCase
{
}
