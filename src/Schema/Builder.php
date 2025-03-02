<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Closure;
use Illuminate\Database\Schema\PostgresBuilder;
use Tpetry\PostgresqlEnhanced\PostgresEnhancedConnection;

class Builder extends PostgresBuilder
{
    use BuilderDomain;
    use BuilderExtension;
    use BuilderFunction;
    use BuilderView;
    use BuilderWipe;

    /**
     * Get the database connection instance.
     */
    public function getConnection(): PostgresEnhancedConnection
    {
        /** @var PostgresEnhancedConnection $connection */
        $connection = parent::getConnection();

        return $connection;
    }

    /**
     * Create a new command set with a Closure.
     *
     * @param string $table
     */
    protected function createBlueprint($table, ?Closure $callback = null): Blueprint
    {
        return new Blueprint($this->getConnection(), $table, $callback);
    }
}
