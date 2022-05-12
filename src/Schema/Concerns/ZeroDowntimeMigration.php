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
        if (property_exists($this, 'timeoutDown')) {
            return (float) $this->timeoutDown;
        }
        if (property_exists($this, 'timeout')) {
            return (float) $this->timeout;
        }

        return 1.0;
    }

    /**
     * The timeout for zero downtime up migrations.
     */
    public function timeoutUp(): float
    {
        if (property_exists($this, 'timeoutUp')) {
            return (float) $this->timeoutUp;
        }
        if (property_exists($this, 'timeout')) {
            return (float) $this->timeout;
        }

        return 1.0;
    }
}
