<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

trait BuilderExtension
{
    /**
     * Create a new extension on the schema.
     */
    public function createExtension(string $name, ?string $schema = null): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrap($name);

        $sql = match (!!$schema) {
            true => "create extension {$name} schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)}",
            false => "create extension {$name}",
        };
        $this->getConnection()->statement($sql);
    }

    /**
     * Create a new extension on the schema if it does not exist.
     */
    public function createExtensionIfNotExists(string $name, ?string $schema = null): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrap($name);

        $sql = match (!!$schema) {
            true => "create extension if not exists {$name} schema {$this->getConnection()->getSchemaGrammar()->wrap($schema)}",
            false => "create extension if not exists {$name}",
        };
        $this->getConnection()->statement($sql);
    }

    /**
     * Drop extensions from the schema.
     */
    public function dropExtension(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop extension {$names}");
    }

    /**
     * Drop extensions from the schema if they exist.
     */
    public function dropExtensionIfExists(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop extension if exists {$names}");
    }
}
