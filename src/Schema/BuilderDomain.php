<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Closure;
use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

trait BuilderDomain
{
    /**
     * Change a domain's constraint in the schema.
     */
    public function changeDomainConstraint(string $name, Closure|string|null $check): void
    {
        $constraint = $this->getConnection()->getSchemaGrammar()->wrap("{$name}_check");
        $name = $this->getConnection()->getSchemaGrammar()->wrap($name);
        if ($check instanceof Closure) {
            $query = tap($this->getConnection()->query(), $check);
            $check = trim(str_replace('select * where', '', Query::toSql($query)));
            $check = str_replace('"VALUE"', 'VALUE', $check);
        }

        $this->getConnection()->statement("alter domain {$name} drop constraint if exists {$constraint}");
        if (filled($check)) {
            $this->getConnection()->statement("alter domain {$name} add constraint {$constraint} check({$check})");
        }
    }

    /**
     * Create a new domain in the schema.
     */
    public function createDomain(string $name, string $type, Closure|string|null $check = null): void
    {
        if ($check instanceof Closure) {
            $query = tap($this->getConnection()->query(), $check);
            $check = trim(str_replace('select * where', '', Query::toSql($query)));
            $check = str_replace('"VALUE"', 'VALUE', $check);
        }

        $sql = match (filled($check)) {
            false => "create domain {$this->getConnection()->getSchemaGrammar()->wrap($name)} as {$type}",
            true => "create domain {$this->getConnection()->getSchemaGrammar()->wrap($name)} as {$type} check({$check})",
        };

        $this->getConnection()->statement($sql);
    }

    /**
     * Drop domains from the schema.
     */
    public function dropDomain(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop domain {$names}");
    }

    /**
     * Drop domains from the schema if they exist.
     */
    public function dropDomainIfExists(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop domain if exists {$names}");
    }
}
