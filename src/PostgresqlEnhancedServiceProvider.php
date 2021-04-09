<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Closure;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseServiceProvider;
use PDO;
use Tpetry\PostgresqlEnhanced\Types\BigIntegerRangeType;
use Tpetry\PostgresqlEnhanced\Types\DateRangeType;
use Tpetry\PostgresqlEnhanced\Types\DecimalRangeType;
use Tpetry\PostgresqlEnhanced\Types\IntegerRangeType;
use Tpetry\PostgresqlEnhanced\Types\TimestampRangeType;
use Tpetry\PostgresqlEnhanced\Types\TimestamptzRangeType;

class PostgresqlEnhancedServiceProvider extends DatabaseServiceProvider
{
    protected array $doctrineTypes = [
        BigIntegerRangeType::class,
        DateRangeType::class,
        DecimalRangeType::class,
        IntegerRangeType::class,
        TimestampRangeType::class,
        TimestamptzRangeType::class,
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        parent::register();

        Connection::resolverFor('pgsql', fn (PDO | Closure $pdo, string $database = '', string $tablePrefix = '', array $config = []) => new PostgresEnhancedConnection($pdo, $database, $tablePrefix, $config));
    }

    /**
     * Register custom types with the Doctrine DBAL library.
     */
    protected function registerDoctrineTypes(): void
    {
        parent::registerDoctrineTypes();

        foreach ($this->doctrineTypes as $type) {
            if (!Type::hasType($type::LARAVEL_NAME)) {
                Type::addType($type::LARAVEL_NAME, $type);
            }
        }
    }
}
