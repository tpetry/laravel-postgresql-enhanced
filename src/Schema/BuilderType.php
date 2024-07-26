<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

trait BuilderType
{
    /**
     * Change a type in the schema.
     */
    public function changeType(string $name, ?string $alteration = null): void
    {
        $sql = match (filled($alteration)) {
            false => "alter type {$name}",
            true => "alter type {$name} {$alteration}",
        };

        $this->getConnection()->statement($sql);
    }

    /**
     * Create a new data type in the schema.
     */
    public function createType(string $name, string $type): void
    {
        $sql = match (filled($type)) {
            false => "create type {$this->getConnection()->getSchemaGrammar()->wrap($name)}",
            true => "create type {$this->getConnection()->getSchemaGrammar()->wrap($name)} as {$type}",
        };

        $this->getConnection()->statement($sql);
    }

    /**
     * Drop types from the schema.
     */
    public function dropType(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop type {$names}");
    }

    /**
     * Drop types from the schema if they exist.
     */
    public function dropTypeIfExists(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop type if exists {$names}");
    }
}
