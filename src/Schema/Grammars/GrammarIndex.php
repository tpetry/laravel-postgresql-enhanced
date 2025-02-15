<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use Tpetry\PostgresqlEnhanced\Support\Helpers\MigrationIndex;

trait GrammarIndex
{
    /**
     * Compile a drop fulltext index if exists command.
     */
    public function compileDropFullTextIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileDropIndexIfExists($blueprint, $command);
    }

    /**
     * Compile a drop index if exists command.
     */
    public function compileDropIndexIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "drop index if exists {$this->wrap($command['index'])}";
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
        return "drop index {$this->wrap($command['index'])}";
    }

    /**
     * Compile a drop unique key if exists command.
     */
    public function compileDropUnique2IfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "drop index if exists {$this->wrap($command['index'])}";
    }

    /**
     * Compile a drop unique key if exists command.
     */
    public function compileDropUniqueIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return "alter table {$this->wrapTable($blueprint)} drop constraint if exists {$this->wrap($command['index'])}";
    }

    /**
     * Compile a fulltext index key command.
     */
    public function compileFulltext(Blueprint $blueprint, Fluent $command): string
    {
        $command['algorithm'] ??= 'gin';

        return (new MigrationIndex())->compileCommand($this, $blueprint->getTable(), $command, false);
    }

    /**
     * Compile a plain index key command.
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command): string
    {
        return (new MigrationIndex())->compileCommand($this, $blueprint->getTable(), $command, false);
    }

    /**
     * Compile a spatial index key command.
     */
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command): string
    {
        $command['algorithm'] = 'gist';

        return (new MigrationIndex())->compileCommand($this, $blueprint->getTable(), $command, false);
    }

    /**
     * Compile a unique key command.
     */
    public function compileUnique2(Blueprint $blueprint, Fluent $command): string
    {
        return (new MigrationIndex())->compileCommand($this, $blueprint->getTable(), $command, true);
    }
}
