<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Eloquent\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;

trait AutomaticDateFormatWithMilliseconds
{
    /**
     * Get the format for database stored dates.
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s.uP';
    }

    /**
     * Return a timestamp as DateTime object.
     */
    protected function asDateTime(mixed $value): CarbonInterface
    {
        try {
            return parent::asDateTime($value);
        } catch (InvalidArgumentException $e) {
            // Laravel 6 has problems when trying to parse a timestamp and the date string does not match exactly the
            // expected format. Missing optional identifiers like milliseconds or the timezone will throw errors.
            if (version_compare(App::version(), '7.0.0', '<') && \is_string($value)) {
                $parsed = rescue(fn () => Date::createFromFormat('Y-m-d H:i:s.u', $value), report: false);
                if ($parsed) {
                    return $parsed;
                }

                $parsed = rescue(fn () => Date::createFromFormat('Y-m-d H:i:sP', $value), report: false);
                if ($parsed) {
                    return $parsed;
                }

                $parsed = rescue(fn () => Date::createFromFormat('Y-m-d H:i:s', $value), report: false);
                if ($parsed) {
                    return $parsed;
                }
            }

            throw $e;
        }
    }
}
