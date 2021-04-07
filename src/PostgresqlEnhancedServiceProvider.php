<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseServiceProvider;
use PDO;

class PostgresqlEnhancedServiceProvider extends DatabaseServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        parent::register();

        Connection::resolverFor('pgsql', fn (PDO | Closure $pdo, string $database = '', string $tablePrefix = '', array $config = []) => new PostgresEnhancedConnection($pdo, $database, $tablePrefix, $config));
    }
}
