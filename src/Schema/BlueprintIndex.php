<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

trait BlueprintIndex
{
    /**
     * Indicate that the given index should be dropped if it exists.
     */
    public function dropIndexIfExists(array|string $index): Fluent
    {
        return $this->dropGenericIfExists($index, 'index', 'dropIndexIfExists');
    }

    /**
     * Indicate that the given partial unique key should be dropped.
     */
    public function dropPartialUnique(array|string $index): Fluent
    {
        return $this->dropIndexCommand('dropIndex', 'unique', $index);
    }

    /**
     * Indicate that the given partial unique key should be dropped if it exists.
     */
    public function dropPartialUniqueIfExists(array|string $index): Fluent
    {
        return $this->dropGenericIfExists($index, 'unique', 'dropIndexIfExists');
    }

    /**
     * Indicate that the given primary key should be dropped if it exists.
     */
    public function dropPrimaryIfExists(array|string $index): Fluent
    {
        return $this->dropGenericIfExists($index, 'primary', 'dropPrimaryIfExists');
    }

    /**
     * Indicate that the given spatial key should be dropped if it exists.
     */
    public function dropSpatialIndexIfExists(array|string $index): Fluent
    {
        return $this->dropGenericIfExists($index, 'spatialIndex', 'dropSpatialIndexIfExists');
    }

    /**
     * Indicate that the given unique key should be dropped if it exists.
     */
    public function dropUniqueIfExists(array|string $index): Fluent
    {
        return $this->dropGenericIfExists($index, 'unique', 'dropUniqueIfExists');
    }

    /**
     * Specify an partial index for the table.
     */
    public function partialIndex(array|string $columns, Closure|string $condition, ?string $name = null, ?string $algorithm = null): Fluent
    {
        // If no name was specified for this index, we will create one using a basic
        // convention of the table name, followed by the columns, followed by an
        // index type, such as primary or index, which makes the index unique.
        $index = $name ?: $this->createIndexName('index', (array) $columns);

        return $this->genericPartialIndex('partialIndex', (array) $columns, $condition, $index, false, $algorithm);
    }

    /**
     * Specify a partial spatial index for the table.
     */
    public function partialSpatialIndex(array|string $columns, Closure|string $condition, ?string $name = null): Fluent
    {
        // If no name was specified for this index, we will create one using a basic
        // convention of the table name, followed by the columns, followed by an
        // index type, such as primary or index, which makes the index unique.
        $index = $name ?: $this->createIndexName('spatialIndex', (array) $columns);

        return $this->genericPartialIndex('partialSpatialIndex', (array) $columns, $condition, $index, false, null);
    }

    /**
     * Specify a partial unique index for the table.
     */
    public function partialUnique(array|string $columns, Closure|string $condition, ?string $name = null, ?string $algorithm = null): Fluent
    {
        // If no name was specified for this index, we will create one using a basic
        // convention of the table name, followed by the columns, followed by an
        // index type, such as primary or index, which makes the index unique.
        $index = $name ?: $this->createIndexName('unique', (array) $columns);

        return $this->genericPartialIndex('partialUnique', (array) $columns, $condition, $index, true, $algorithm);
    }

    /**
     * Generic implementation to create the migration command information for dropping an index if it exists.
     */
    protected function dropGenericIfExists(array|string $index, string $indexType, string $indexCommand): Fluent
    {
        $columns = [];

        // If the given "index" is actually an array of columns, the developer means
        // to drop an index merely by specifying the columns involved without the
        // conventional name, so we will build the index name from the columns.
        if (\is_array($index)) {
            $index = $this->createIndexName($indexType, $columns = $index);
        }

        return $this->addCommand($indexCommand, compact('index', 'columns') + ['algorithm' => null]);
    }

    /**
     * Generic implementation to create the migration command information for creating a partial index.
     */
    protected function genericPartialIndex(string $type, array $columns, Closure|string $condition, string $index, bool $unique, ?string $algorithm): Fluent
    {
        // If the condition is a closure the migration will build a condition with
        // the query builder methods which needs to be transformed to a raw sql
        // query string for creating the index.
        if ($condition instanceof Closure) {
            $query = $condition(DB::query());
            $condition = trim(str_replace('select * where', '', Query::toSql($query)));
        }

        return $this->addCommand($type, compact('index', 'columns', 'algorithm', 'condition'));
    }
}
