<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced;

use Illuminate\Database\PostgresConnection;
use Tpetry\PostgresqlEnhanced\Schema\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;

class PostgresEnhancedConnection extends PostgresConnection
{
    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder(): Builder
    {
        if (null === $this->schemaGrammar) {
            $this->useDefaultSchemaGrammar();
        }

        return new Builder($this);
    }

    /**
     * Get the default schema grammar instance.
     */
    protected function getDefaultSchemaGrammar(): Grammar
    {
        return $this->withTablePrefix(new Grammar());
    }
}
