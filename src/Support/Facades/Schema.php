<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Facades;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Schema as BaseSchema;

/**
 * @method static void createExtension(string $name)
 * @method static void createExtensionIfNotExists(string $name)
 * @method static void createRecursiveView(string $name, Builder|string $query, array $columns)
 * @method static void createRecursiveViewOrReplace(string $name, Builder|string $query, array $columns)
 * @method static void createMaterializedView(string $name, Builder|string $query, bool $withData = true)
 * @method static void createView(string $name, Builder|string $query)
 * @method static void createViewOrReplace(string $name, Builder|string $query)
 * @method static void dropExtension(string ...$name)
 * @method static void dropExtensionIfExists(string ...$name)
 * @method static void dropFunction(string $name, ?array $arguments = null)
 * @method static void dropFunctionIfExists(string $name, ?array $arguments = null)
 * @method static void dropView(string ...$name)
 * @method static void dropViewIfExists(string ...$name)
 * @method static void dropMaterializedView(string ...$name)
 * @method static void dropMaterializedViewIfExists(string ...$name)
 * @method static void refreshMaterializedView(string $name, bool $concurrently = false, bool $withData = true)
 * @method static void createFunction(string $name, string|array $parameters, string $return, string $body, array $modifiers = [])
 * @method static void createOrReplaceFunction(string $name, string|array $parameters, string $return, string $body, array $modifiers = [])
 */
class Schema extends BaseSchema
{
}
