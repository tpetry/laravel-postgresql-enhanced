<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

trait AutomaticDateFormat
{
    /**
     * Get the format for database stored dates.
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:sP';
    }

    /**
     * Return a timestamp as DateTime object.
     */
    protected function asDateTime(mixed $value): CarbonInterface
    {
        try {
            return parent::asDateTime($value);
        } catch (InvalidArgumentException $e) {
            // Laravel 6 has problems when trying to parse a timestamp without timezone value with the timezone flag.
            // Therefore, the value is parsed again without the time zone to catch that edge case.
            if (version_compare(App::version(), '7.0.0', '<') && \is_string($value)) {
                return rescue(
                    callback: fn () => Date::createFromFormat('Y-m-d H:i:s', $value),
                    rescue: fn () => throw $e,
                    report: false,
                );
            }

            throw $e;
        }
    }
}
