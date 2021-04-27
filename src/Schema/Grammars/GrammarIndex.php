<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

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
     * Compile a drop unique key if exists command.
     */
    public function compileDropUniqueIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "alter table {$this->wrapTable($blueprint)} drop constraint if exists {$this->wrap($command->index)}";
    }

    /**
     * Compile a partial index key command.
     */
    public function compilePartialIndex(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('create index %s on %s%s (%s) where %s',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $command->algorithm ? ' using '.$command->algorithm : '',
            $this->columnize($command->columns),
            $command->condition,
        );
    }

    /**
     * Compile a partial spatial index key command.
     */
    public function compilePartialSpatialIndex(Blueprint $blueprint, Fluent $command): string
    {
        $command->algorithm = 'gist';

        return $this->compilePartialIndex($blueprint, $command);
    }

    /**
     * Compile a partial unique key command.
     */
    public function compilePartialUnique(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf('create unique index %s on %s%s (%s) where %s',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $command->algorithm ? ' using '.$command->algorithm : '',
            $this->columnize($command->columns),
            $command->condition,
        );
    }
}
