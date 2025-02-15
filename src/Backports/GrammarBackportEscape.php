<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Backports;

/**
 * To support some features these commits from laravel needed to be backported for older versions:
 * - [10.x] Escaping functionality within the Grammar (https://github.com/laravel/framework/commit/e953137280cdf6e0fe3c3e4c49d7209ad86c92c0).
 */
trait GrammarBackportEscape
{
    /**
     * Escapes a value for safe SQL embedding.
     *
     * @param string|float|int|bool|null $value
     * @param bool $binary
     */
    public function escape($value, $binary = false): string
    {
        return $this->connection->escape($value, $binary);
    }
}
