<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Timescale;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Fluent;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\Action;
use Tpetry\PostgresqlEnhanced\Support\Helpers\MigrationIndex;
use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

class CaggBlueprint
{
    private array $commands = [];
    private bool $withData = false;

    public function __construct(
        private string $table,
        Closure $callback,
    ) {
        $callback($this);
    }

    /**
     * Create the continuous aggregate aggregating the specific query.
     */
    public function as(Builder|string $query, array $columns = []): void
    {
        $this->commands[] = function (Connection $connection, Grammar $grammar) use ($query, $columns) {
            $query = Query::toSql($query);
            $name = match ($columns) {
                [] => $grammar->wrapTable($this->table),
                default => "{$this->table} ({$grammar->columnize($columns)})",
            };

            $sql = match ($this->withData) {
                true => "create materialized view {$name} with (timescaledb.continuous, timescaledb.create_group_indexes = false) AS {$query}",
                false => "create materialized view {$name} with (timescaledb.continuous, timescaledb.create_group_indexes = false) AS {$query} with no data",
            };

            return [$sql];
        };
    }

    public function build(Connection $connection, Grammar $grammar): void
    {
        foreach ($this->commands as $command) {
            foreach ($command($connection, $grammar) as $sql) {
                $connection->statement($sql);
            }
        }
    }

    /**
     * Indicate that the given index should be dropped.
     */
    public function dropIndex(string|array $index): void
    {
        $this->dropIdx($index, false);
    }

    /**
     * Indicate that the given index should be dropped if it exists.
     */
    public function dropIndexIfExists(array|string $index): void
    {
        $this->dropIdx($index, true);
    }

    /**
     * Specify an index for the table.
     *
     * @return \Tpetry\PostgresqlEnhanced\Schema\IndexDefinition
     */
    public function index(string|array $columns, ?string $name = null, ?string $algorithm = null): Fluent
    {
        $migration = new MigrationIndex();

        /** @var \Tpetry\PostgresqlEnhanced\Schema\IndexDefinition $fluent */
        $fluent = $migration->createCommand('index', $name ?: $migration->createName('index', '', $this->table, $columns), $columns, $algorithm);

        $this->commands[] = fn (Connection $connection, Grammar $grammar) => [(new MigrationIndex())->compileCommand($grammar, $this->table, $fluent, false)];

        return $fluent;
    }

    /**
     * Make the continuous aggregate realtime.
     */
    public function realtime(bool $enabled = true): void
    {
        $this->commands[] = fn (Connection $connection, Grammar $grammar) => ["alter materialized view {$grammar->wrap($this->table)} set (timescaledb.materialized_only = {$grammar->escape(!$enabled)})"];
    }

    /**
     * Set timescale hypertable options.
     *
     * @param Action ...$actions
     */
    public function timescale(...$actions): void
    {
        foreach ($actions as $action) {
            $this->commands[] = function (Connection $connection, Grammar $grammar) use ($action) {
                return array_map(fn ($sql) => str_replace('alter table', 'alter materialized view', $sql), $action->getValue($grammar, $this->table));
            };
        }
    }

    /**
     * Create the continuous aggregate without filling it with data.
     */
    public function withData(): void
    {
        $this->withData = true;
    }

    protected function dropIdx(string|array $index, bool $ifExists): void
    {
        $this->commands[] = function (Connection $connection, Grammar $grammar) use ($index, $ifExists): array {
            if (\is_array($index)) {
                $prefix = $connection->getConfig('prefix_indexes') ? $connection->getConfig('prefix') : '';
                $table = match (str_contains($this->table, '.')) {
                    true => substr_replace($this->table, '.'.$prefix, strrpos($this->table, '.'), 1),
                    false => $prefix.$this->table,
                };

                $index = str_replace(['-', '.'], '_', strtolower($table.'_'.implode('_', $index).'_index'));
            }

            return match ($ifExists) {
                true => ["drop index if exists _timescaledb_internal.{$index}"],
                false => ["drop index _timescaledb_internal.{$index}"],
            };
        };
    }
}
