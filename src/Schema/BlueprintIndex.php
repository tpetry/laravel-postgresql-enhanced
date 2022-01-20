<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use RuntimeException;

trait BlueprintIndex
{
    /**
     * Indicate that the given fulltext index should be dropped if it exists.
     */
    public function dropFullTextIfExists(array|string $index): Fluent
    {
        return $this->dropGenericIfExists($index, 'fulltext', 'dropFullTextIfExists');
    }

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
        if (null === $name) {
            $name = str_replace('unique2', 'unique', $this->createIndexName('unique2', (array) $columns));
        }

        return $this->indexCommand('unique2', $columns, $name, $algorithm);
    }

    /**
     * Create a default index name for the table.
     *
     * @param string $type
     */
    protected function createIndexName($type, array $columns): string
    {
        if ('unique' === $type) {
            return parent::createIndexName($type, $columns);
        }

        $columns = array_map(function (string $column): string {
            // When the column has a structure like '(.+).*' it's an functional index. But it's not
            // easily possible to extract column names from a functional expression so the developer
            // has to provide an index name instead of relying on the automatic index name generation.
            if (str_starts_with($column, '(')) {
                throw new RuntimeException('For functional indexes an index name must be specified.');
            }

            // In some very rare cases a developer created a column with characters which are not
            // alphanumeric and not an underscore. For the standard laravel migration it's no problem
            // as the full string is used and properly escaped. But for the improved index support a
            // special character like the space means index options are defined. To still support
            // these very uncommon column names the column can be escaped in double quotes.
            // Strictly speaking this is breaking compatability with laravel, but such column names
            // are very uncommon. So it's a good tradeoff for the big performance improvements.
            if (str_starts_with($column, '"')) {
                return Str::beforeLast(Str::after($column, '"'), '"');
            }

            // When index parameters are defined the space in the sql grammar is the separation character
            // of the column and all params. So in case a space is available only the part before the first
            // space character is declaring the column and will be used.
            return Str::before($column, ' ');
        }, $columns);

        return parent::createIndexName($type, $columns);
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
