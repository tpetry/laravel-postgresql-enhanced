<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema\Concerns;

trait ZeroDowntimeMigration
{
    /**
     * The timeout for zero downtime down migrations.
     */
    public function timeoutDown(): float
    {
        $properties = get_object_vars($this);

        return (float) ($properties['timeoutDown'] ?? $properties['timeout'] ?? 1.0);
    }

    /**
     * The timeout for zero downtime up migrations.
     */
    public function timeoutUp(): float
    {
        $properties = get_object_vars($this);

        return (float) ($properties['timeoutUp'] ?? $properties['timeout'] ?? 1.0);
    }
}
