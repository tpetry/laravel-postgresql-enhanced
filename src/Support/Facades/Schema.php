<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Facades;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Schema as BaseSchema;

/**
 * @method static void changeDomainConstraint(string $name, null|string|(callable(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed) $check)
 * @method static void changeEnumTypeValueName( string $name, string $existingValue, string $newValue )
 * @method static void changeType(string $name, ?string $alteration = null)
 * @method static void changeTypeName( string $name, string $newName )
 * @method static void changeTypeToAddEnumValue(string $name, string $newValue)
 * @method static void createDomain(string $name, string $type, string|(callable(\Tpetry\PostgresqlEnhanced\Query\Builder): mixed) $check = null)
 * @method static void dropDomain(string ...$name)
 * @method static void dropDomainIfExists(string ...$name)
 * @method static void createExtension(string $name, ?string $schema = null)
 * @method static void createExtensionIfNotExists(string $name, ?string $schema = null)
 * @method static void createFunction(string $name, array $parameters, array|string $return, string $language, string $body, array $options = [])
 * @method static void createFunctionOrReplace(string $name, array $parameters, array|string $return, string $language, string $body, array $options = [])
 * @method static void createRecursiveView(string $name, Builder|string $query, array $columns)
 * @method static void createRecursiveViewOrReplace(string $name, Builder|string $query, array $columns)
 * @method static void createType(string $name, string $type)
 * @method static void createMaterializedView(string $name, Builder|string $query, bool $withData = true, array $columns = [])
 * @method static void createView(string $name, Builder|string $query, array $columns = [])
 * @method static void createViewOrReplace(string $name, Builder|string $query, array $columns = [])
 * @method static void dropExtension(string ...$name)
 * @method static void dropExtensionIfExists(string ...$name)
 * @method static void dropFunction(string $name, ?array $arguments = null)
 * @method static void dropFunctionIfExists(string $name, ?array $arguments = null)
 * @method static void dropType(string ...$name)
 * @method static void dropTypeIfExists(string ...$name)
 * @method static void dropView(string ...$name)
 * @method static void dropViewIfExists(string ...$name)
 * @method static void dropMaterializedView(string ...$name)
 * @method static void dropMaterializedViewIfExists(string ...$name)
 * @method static void refreshMaterializedView(string $name, bool $concurrently = false, bool $withData = true)
 */
class Schema extends BaseSchema
{
}
