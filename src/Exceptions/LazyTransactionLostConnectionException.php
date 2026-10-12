<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Exceptions;

use Illuminate\Database\DetectsLostConnections;
use PDOException;

/**
 * The queued queries include the `BEGIN` but not the last one triggering the execution.
 */
class LazyTransactionLostConnectionException extends PDOException
{
    use DetectsLostConnections;

    /**
     * @param array<int, string> $queuedQueries
     */
    private function __construct(
        PDOException $previous,
        private array $queuedQueries,
    ) {
        parent::__construct($previous->getMessage(), 0, $previous);

        // The code is written directly into the property because `PDOException::getCode()` can also be a string, which
        // the exception constructor forbids.
        $this->code = $previous->getCode();
        $this->errorInfo = $previous->errorInfo;
    }

    /**
     * @return array<int, string>
     */
    public function getQueuedQueries(): array
    {
        return $this->queuedQueries;
    }

    /**
     * @param array<int, string> $queuedQueries
     */
    public static function wrapIfLostConnection(PDOException $e, array $queuedQueries): self|PDOException
    {
        // Laravel's lost connection detection is only available as an instance method, so the exception is created first.
        $exception = new self($e, $queuedQueries);

        return $exception->causedByLostConnection($e) ? $exception : $e;
    }
}
