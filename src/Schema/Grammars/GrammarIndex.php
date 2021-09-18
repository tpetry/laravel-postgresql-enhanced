<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

trait GrammarIndex
{
    /**
     * Compile a drop index if exists command.
     */
    public function compileDropIndexIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "drop index if exists {$this->wrap($command->index)}";
    }

    /**
     * Compile a drop primary key if exists command.
     */
    public function compileDropPrimaryIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "alter table {$this->wrapTable($blueprint)} drop constraint if exists {$this->wrap("{$blueprint->getTable()}_pkey")}";
    }

    /**
     * Compile a drop spatial index if exist command.
     */
    public function compileDropSpatialIndexIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileDropIndexIfExists($blueprint, $command);
    }

    /**
     * Compile a drop unique key command.
     */
    public function compileDropUnique2(Blueprint $blueprint, Fluent $command): string
    {
        return "drop index {$this->wrap($command->index)}";
    }

    /**
     * Compile a drop unique key if exists command.
     */
    public function compileDropUnique2IfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "drop index if exists {$this->wrap($command->index)}";
    }

    /**
     * Compile a drop unique key if exists command.
     */
    public function compileDropUniqueIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "alter table {$this->wrapTable($blueprint)} drop constraint if exists {$this->wrap($command->index)}";
    }

    /**
     * Compile a plain index key command.
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command): string
    {
        return $this->genericCompileCreateIndex($blueprint, $command, false);
    }

    /**
     * Compile a spatial index key command.
     */
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command): string
    {
        return $this->genericCompileCreateIndex($blueprint, $command->algorithm('gist'), false);
    }

    /**
     * Compile a unique key command.
     */
    public function compileUnique2(Blueprint $blueprint, Fluent $command): string
    {
        return $this->genericCompileCreateIndex($blueprint, $command, true);
    }

    private function genericCompileCreateIndex(Blueprint $blueprint, Fluent $command, bool $unique): string
    {
        // If the index is partial index using a closure a dummy query builder is provided to the closure. The query is
        // then transformed to a static query and the select part is removed to only keep the condition.
        if ($command->where instanceof Closure) {
            $query = ($command->where)(DB::query());
            $command->where = trim(str_replace('select * where', '', Query::toSql($query)));
        }

        // If the storage parameters for the index are provided in array form they need to be serialized to PostgreSQL's
        // string format.
        if (\is_array($command->with)) {
            $with = array_map(fn (mixed $value) => match ($value) {
                true => 'on',
                false => 'off',
                default => (string) $value,
            }, $command->with);
            $with = array_map(fn (string $value, string $key) => "{$key} = {$value}", $with, array_keys($with));
            $command->with = implode(', ', $with);
        }

        $index = [
            $unique ? 'create unique index' : 'create index',
            $this->wrap($command->index),
            'on',
            $this->wrapTable($blueprint),
            $command->algorithm ? "using {$command->algorithm}" : '',
            '('.$this->columnize($command->columns).')',
            $command->include ? 'include ('.implode(',', $this->wrapArray(Arr::wrap($command->include))).')' : '',
            $command->with ? "with ({$command->with})" : '',
            $command->where ? "where {$command->where}" : '',
        ];
        $sql = implode(' ', array_filter($index, fn ($part) => $part));

        return $sql;
    }
}
