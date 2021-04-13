<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Closure;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseServiceProvider;
use PDO;
use Tpetry\PostgresqlEnhanced\Types\BigIntegerRangeType;
use Tpetry\PostgresqlEnhanced\Types\BitType;
use Tpetry\PostgresqlEnhanced\Types\CaseInsensitiveTextType;
use Tpetry\PostgresqlEnhanced\Types\DateRangeType;
use Tpetry\PostgresqlEnhanced\Types\DecimalRangeType;
use Tpetry\PostgresqlEnhanced\Types\EuropeanArticleNumber13Type;
use Tpetry\PostgresqlEnhanced\Types\HstoreType;
use Tpetry\PostgresqlEnhanced\Types\IntegerRangeType;
use Tpetry\PostgresqlEnhanced\Types\InternationalStandardBookNumber13Type;
use Tpetry\PostgresqlEnhanced\Types\InternationalStandardBookNumberType;
use Tpetry\PostgresqlEnhanced\Types\InternationalStandardMusicNumber13Type;
use Tpetry\PostgresqlEnhanced\Types\InternationalStandardMusicNumberType;
use Tpetry\PostgresqlEnhanced\Types\InternationalStandardSerialNumber13Type;
use Tpetry\PostgresqlEnhanced\Types\InternationalStandardSerialNumberType;
use Tpetry\PostgresqlEnhanced\Types\IpNetworkType;
use Tpetry\PostgresqlEnhanced\Types\LabelTreeType;
use Tpetry\PostgresqlEnhanced\Types\TimestampRangeType;
use Tpetry\PostgresqlEnhanced\Types\TimestamptzRangeType;
use Tpetry\PostgresqlEnhanced\Types\UniversalProductNumberType;
use Tpetry\PostgresqlEnhanced\Types\VarbitType;
use Tpetry\PostgresqlEnhanced\Types\XmlType;

class PostgresqlEnhancedServiceProvider extends DatabaseServiceProvider
{
    protected array $doctrineTypes = [
        BigIntegerRangeType::class,
        BitType::class,
        CaseInsensitiveTextType::class,
        DateRangeType::class,
        DecimalRangeType::class,
        EuropeanArticleNumber13Type::class,
        HstoreType::class,
        IntegerRangeType::class,
        InternationalStandardBookNumber13Type::class,
        InternationalStandardBookNumberType::class,
        InternationalStandardMusicNumber13Type::class,
        InternationalStandardMusicNumberType::class,
        InternationalStandardSerialNumber13Type::class,
        InternationalStandardSerialNumberType::class,
        IpNetworkType::class,
        LabelTreeType::class,
        TimestampRangeType::class,
        TimestamptzRangeType::class,
        UniversalProductNumberType::class,
        VarbitType::class,
        XmlType::class,
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        parent::register();

        Connection::resolverFor('pgsql', fn (PDO | Closure $pdo, string $database = '', string $tablePrefix = '', array $config = []) => new PostgresEnhancedConnection($pdo, $database, $tablePrefix, $config));

        foreach ($this->doctrineTypes as $type) {
            if (!Type::hasType($type::LARAVEL_NAME)) {
                Type::addType($type::LARAVEL_NAME, $type);
            }
        }
    }
}
