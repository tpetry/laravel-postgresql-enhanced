<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Database\Schema\IndexDefinition as BaseIndexDefinition;

/**
 * @internal This class is not used. It only exists to teach Laravel projects using PHPStan or IDEs supporting auto-suggest about added functionality.
 */
class IndexDefinition extends BaseIndexDefinition
{
    /**
     * Create index concurrently to current migration (PostgreSQL).
     */
    public function concurrently(): self
    {
        return $this;
    }

    /**
     * Only create index if it does not exist yet (PostgreSQL).
     */
    public function ifNotExists(): self
    {
        return $this;
    }

    /**
     * Include non-key columns in the index (PostgreSQL).
     *
     * @param string|array<int, string> $columns
     */
    public function include(string|array $columns): self
    {
        return $this;
    }

    /**
     * Mark NULLs as not distinct values (PostgreSQL).
     */
    public function nullsNotDistinct(): self
    {
        return $this;
    }

    /**
     * Specify fulltext index weight for columns (PostgreSQL).
     *
     * @param array<int, string> $labels
     */
    public function weight(array $labels): self
    {
        return $this;
    }

    /**
     * Build a partial index by specifying the rows that should be included (PostgreSQL).
     *
     * @param string|(callable(\Illuminate\Database\Query\Builder):mixed)|(callable(\Illuminate\Contracts\Database\Query\Builder):mixed) $columns
     */
    public function where(string|callable $columns): self
    {
        return $this;
    }

    /**
     * Specify index parameters to fine-tune its configuration (PostgreSQL).
     *
     * @param array<string, bool|float|int|string> $options
     */
    public function with(array $options): self
    {
        return $this;
    }
}
