<?php

declare(strict_types=1);

namespace Tourze\EasyAdminMenuBundle\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

/**
 * @implements Rule<InClassNode>
 */
final class AdminMenuImplementationRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection) {
            return [];
        }

        $className = $classReflection->getName();

        if (!str_ends_with($className, 'Service\AdminMenu')) {
            return [];
        }

        $interfaceName = MenuProviderInterface::class;
        if (!$classReflection->implementsInterface($interfaceName)) {
            return [
                RuleErrorBuilder::message(
                    sprintf('Class %s must implement %s.', $className, $interfaceName)
                )->build(),
            ];
        }

        return [];
    }
}
