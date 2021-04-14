<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Support\Facades;

use Illuminate\Support\Facades\Schema as BaseSchema;

/**
 * @method static void createExtension(string $name)
 * @method static void createExtensionIfNotExists(string $name)
 * @method static void dropExtension(string ...$name)
 * @method static void dropExtensionIfExists(string ...$name)
 */
class Schema extends BaseSchema
{
}
