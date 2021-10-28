<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Concerns;

trait ZeroDowntimeMigration
{
    /**
     * The timeout for zero downtime down migrations.
     *
     * @psalm-suppress RedundantCondition
     * @psalm-suppress TypeDoesNotContainType
     */
    public function timeoutDown(): float
    {
        return $this->timeoutDown ?? $this->timeout ?? 1.0;
    }

    /**
     * The timeout for zero downtime up migrations.
     *
     * @psalm-suppress RedundantCondition
     * @psalm-suppress TypeDoesNotContainType
     */
    public function timeoutUp(): float
    {
        return $this->timeoutUp ?? $this->timeout ?? 1.0;
    }
}
