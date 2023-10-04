<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

trait BuilderView
{
    /**
     * Create a materialized view on the schema.
     */
    public function createMaterializedView(string $name, QueryBuilder|string $query, bool $withData = true, array $columns = null): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrapTable($name);
        if (null !== $columns) {
            $columns = $this->getConnection()->getSchemaGrammar()->columnize($columns);
            $name = "{$name} ({$columns})";
        }
        $query = Query::toSql($query);
        $withData = $withData ? 'with data' : 'with no data';
        $this->getConnection()->statement("create materialized view {$name} as {$query} {$withData}");
    }

    /**
     * Create a recursive view on the schema.
     */
    public function createRecursiveView(string $name, QueryBuilder|string $query, array $columns): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrapTable($name);
        $columns = $this->getConnection()->getSchemaGrammar()->columnize($columns);
        $query = Query::toSql($query);
        $this->getConnection()->statement("create recursive view {$name} ({$columns}) as {$query}");
    }

    /**
     * Create or replace a recursive view on the schema.
     */
    public function createRecursiveViewOrReplace(string $name, QueryBuilder|string $query, array $columns): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrapTable($name);
        $columns = $this->getConnection()->getSchemaGrammar()->columnize($columns);
        $query = Query::toSql($query);
        $this->getConnection()->statement("create or replace recursive view {$name} ({$columns}) as {$query}");
    }

    /**
     * Create a view on the schema.
     */
    public function createView(string $name, QueryBuilder|string $query, array $columns = null): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrapTable($name);
        if (null !== $columns) {
            $columns = $this->getConnection()->getSchemaGrammar()->columnize($columns);
            $name = "{$name} ({$columns})";
        }
        $query = Query::toSql($query);
        $this->getConnection()->statement("create view {$name} as {$query}");
    }

    /**
     * Create or replace a view on the schema.
     */
    public function createViewOrReplace(string $name, QueryBuilder|string $query, array $columns = null): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrapTable($name);
        if (null !== $columns) {
            $columns = $this->getConnection()->getSchemaGrammar()->columnize($columns);
            $name = "{$name} ({$columns})";
        }
        $query = Query::toSql($query);
        $this->getConnection()->statement("create or replace view {$name} as {$query}");
    }

    /**
     * Drop materialized views from the schema.
     */
    public function dropMaterializedView(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop materialized view {$names}");
    }

    /**
     * Drop materialized views from the schema if they exist.
     */
    public function dropMaterializedViewIfExists(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop materialized view if exists {$names}");
    }

    /**
     * Drop views from the schema.
     */
    public function dropView(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop view {$names}");
    }

    /**
     * Drop views from the schema if they exist.
     */
    public function dropViewIfExists(string ...$name): void
    {
        $names = $this->getConnection()->getSchemaGrammar()->namize($name);
        $this->getConnection()->statement("drop view if exists {$names}");
    }

    /**
     * Refresh materialized of the schema.
     */
    public function refreshMaterializedView(string $name, bool $concurrently = false, bool $withData = true): void
    {
        $name = $this->getConnection()->getSchemaGrammar()->wrap($name);
        $withData = $withData ? 'with data' : 'with no data';
        match ($concurrently) {
            true => $this->getConnection()->statement("refresh materialized view concurrently {$name} {$withData}"),
            false => $this->getConnection()->statement("refresh materialized view {$name} {$withData}"),
        };
    }
}
