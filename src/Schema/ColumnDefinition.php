<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Schema\ColumnDefinition as BaseColumnDefinition;

class ColumnDefinition extends BaseColumnDefinition
{
    /**
     * Specify the compression method for TOASTed values (PostgreSQL).
     */
    public function compression(string $algorithm): self
    {
        return $this;
    }

    /**
     * Sets an initial value to the column (PostgreSQL).
     */
    public function initial(mixed $value): self
    {
        return $this;
    }

    /**
     * Specify casting expression when changing the column type (PostgreSQL).
     */
    public function using(string|Expression $expression): self
    {
        return $this;
    }
}
