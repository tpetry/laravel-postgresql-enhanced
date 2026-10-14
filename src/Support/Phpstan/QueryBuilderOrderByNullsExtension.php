<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Phpstan;

use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;

/**
 * The $nulls parameter added to orderBy() can't be added to the native method by a reflection extension.
 */
class QueryBuilderOrderByNullsExtension implements IgnoreErrorExtension
{
    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        if (!$node instanceof MethodCall || !$node->name instanceof Identifier || 'orderby' !== $node->name->toLowerString()) {
            return false;
        }
        if (!(new ObjectType(BuilderContract::class))->isSuperTypeOf($scope->getType($node->var))->yes()) {
            return false;
        }

        return match ($error->getIdentifier()) {
            'argument.unknown' => str_starts_with($error->getMessage(), 'Unknown parameter $nulls '),
            'arguments.count' => 3 === \count($node->getArgs()),
            default => false,
        };
    }
}
