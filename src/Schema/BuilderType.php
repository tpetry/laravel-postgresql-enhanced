<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

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
     * Rename a type in the schema.
     */
    public function changeTypeName(string $name, string $newName): void
    {
        $this->changeType($name, "rename to {$newName}");
    }

    /**
     * Add a new value to enum type in the schema.
     */
    public function changeTypeToAddEnumValue(string $name, string $newValue): void
    {
        $this->changeType($name, "add value if not exists '{$newValue}'");
    }

    /**
     * Rename a value in enum type in the schema.
     */
    public function changeEnumTypeValueName(string $name, string $existingValue, string $newValue): void
    {
        $this->changeType($name, "rename value '{$existingValue}' to '{$newValue}'");
    }

    /**
     * Create a new data type in the schema.
     */
    public function createType(string $name, string $type): void
    {
        $sql = match (filled($type)) {
            false => "create type {$name}",
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
