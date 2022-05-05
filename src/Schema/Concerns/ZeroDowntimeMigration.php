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
        return $this->timeoutDown ?? $this->timeout ?? 1.0;
    }

    /**
     * The timeout for zero downtime up migrations.
     */
    public function timeoutUp(): float
    {
        return $this->timeoutUp ?? $this->timeout ?? 1.0;
    }
}
