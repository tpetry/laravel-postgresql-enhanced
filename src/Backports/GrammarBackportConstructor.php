<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Backports;

use Illuminate\Database\Connection;

/**
 * To support some features these commits from laravel needed to be backported for older versions:
 * - [12.x] Fix accessing Connection property in Grammar classes (https://github.com/laravel/framework/commit/c78fa3d0684206f721b9a3b76e8c596aa8a08cd0)
 */
trait GrammarBackportConstructor
{
    /**
     * The connection used for escaping values.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Create a new grammar instance.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}
