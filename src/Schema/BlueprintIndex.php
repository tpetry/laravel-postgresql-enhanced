<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Fluent;

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
     * Indicate that the given unique key should be dropped.
     */
    public function dropUniqueIndex(array|string $index): Fluent
    {
        return $this->dropIndexCommand('dropUnique2', 'unique', $index);
    }

    /**
     * Indicate that the given unique key should be dropped if it exists.
     */
    public function dropUniqueIndexIfExists(array|string $index): Fluent
    {
        return $this->dropGenericIfExists($index, 'unique', 'dropUnique2IfExists');
    }

    /**
     * Specify a unique index for the table.
     */
    public function uniqueIndex($columns, ?string $name = null, ?string $algorithm = null): Fluent
    {
        return $this->indexCommand('unique2', $columns, $name ?: $this->createIndexName('unique', (array) $columns), $algorithm);
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
}
