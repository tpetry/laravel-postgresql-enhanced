<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Closure;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use PDO;
use Tpetry\PostgresqlEnhanced\Eloquent\Mixins\BuilderLazyByCursor;
use Tpetry\PostgresqlEnhanced\Eloquent\Mixins\BuilderReturning;
use Tpetry\PostgresqlEnhanced\Support\Helpers\ZeroDowntimeMigrationSupervisor;
use Tpetry\PostgresqlEnhanced\Types\BitType;
use Tpetry\PostgresqlEnhanced\Types\CidrType;
use Tpetry\PostgresqlEnhanced\Types\CitextType;
use Tpetry\PostgresqlEnhanced\Types\DatemultirangeType;
use Tpetry\PostgresqlEnhanced\Types\DaterangeType;
use Tpetry\PostgresqlEnhanced\Types\Ean13Type;
use Tpetry\PostgresqlEnhanced\Types\HstoreType;
use Tpetry\PostgresqlEnhanced\Types\Int4multirangeType;
use Tpetry\PostgresqlEnhanced\Types\Int4rangeType;
use Tpetry\PostgresqlEnhanced\Types\Int8multirangeType;
use Tpetry\PostgresqlEnhanced\Types\Int8rangeType;
use Tpetry\PostgresqlEnhanced\Types\Isbn13Type;
use Tpetry\PostgresqlEnhanced\Types\IsbnType;
use Tpetry\PostgresqlEnhanced\Types\Ismn13Type;
use Tpetry\PostgresqlEnhanced\Types\IsmnType;
use Tpetry\PostgresqlEnhanced\Types\Issn13Type;
use Tpetry\PostgresqlEnhanced\Types\IssnType;
use Tpetry\PostgresqlEnhanced\Types\LtreeType;
use Tpetry\PostgresqlEnhanced\Types\NummultirangeType;
use Tpetry\PostgresqlEnhanced\Types\NumrangeType;
use Tpetry\PostgresqlEnhanced\Types\TsmultirangeType;
use Tpetry\PostgresqlEnhanced\Types\TsrangeType;
use Tpetry\PostgresqlEnhanced\Types\TstzmultirangeType;
use Tpetry\PostgresqlEnhanced\Types\TstzrangeType;
use Tpetry\PostgresqlEnhanced\Types\TsvectorType;
use Tpetry\PostgresqlEnhanced\Types\UpcType;
use Tpetry\PostgresqlEnhanced\Types\VarbitType;
use Tpetry\PostgresqlEnhanced\Types\XmlType;

class PostgresqlEnhancedServiceProvider extends ServiceProvider
{
    protected array $doctrineTypes = [
        BitType::class,
        CidrType::class,
        CitextType::class,
        DatemultirangeType::class,
        DaterangeType::class,
        Ean13Type::class,
        HstoreType::class,
        Int4multirangeType::class,
        Int4rangeType::class,
        Int8multirangeType::class,
        Int8rangeType::class,
        IsbnType::class,
        Isbn13Type::class,
        IsmnType::class,
        Ismn13Type::class,
        IssnType::class,
        Issn13Type::class,
        LtreeType::class,
        NummultirangeType::class,
        NumrangeType::class,
        TsmultirangeType::class,
        TsrangeType::class,
        TstzmultirangeType::class,
        TstzrangeType::class,
        TsvectorType::class,
        UpcType::class,
        VarbitType::class,
        XmlType::class,
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        EloquentBuilder::mixin(new BuilderLazyByCursor());
        EloquentBuilder::mixin(new BuilderReturning());

        Connection::resolverFor('pgsql', function (PDO|Closure $pdo, string $database = '', string $tablePrefix = '', array $config = []) {
            return new PostgresEnhancedConnection($pdo, $database, $tablePrefix, $config);
        });
        $this->app->singleton(ZeroDowntimeMigrationSupervisor::class);

        Event::listen(MigrationsStarted::class, function (): void {
            $this->registerDoctrineTypes();
            $this->app->get(ZeroDowntimeMigrationSupervisor::class)->start();
        });
        if ($this->app->runningUnitTests()) {
            $this->registerDoctrineTypes();
        }
    }

    protected function registerDoctrineTypes(): void
    {
        foreach ($this->doctrineTypes as $type) {
            /** @var Type $typeInstance */
            $typeInstance = new $type();

            if (!Type::hasType($typeInstance->getName())) {
                Type::addType($typeInstance->getName(), $type);
            }
        }
    }
}
