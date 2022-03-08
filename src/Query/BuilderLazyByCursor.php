<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Generator;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait BuilderLazyByCursor
{
    public function lazyByCursor(int $chunkSize = 1000): LazyCollection
    {
        if ($chunkSize < 1) {
            throw new InvalidArgumentException('The chunk size should be at least 1');
        }
        if (0 === $this->getConnection()->transactionLevel()) {
            throw new InvalidArgumentException('The lazyByCursor method can only be run within a transaction');
        }

        $cursor = 'cursor_'.strtolower(Str::random());

        return new LazyCollection(function () use ($chunkSize, $cursor): Generator {
            try {
                $this->getConnection()->statement("declare {$cursor} no scroll cursor for {$this->toSql()}", $this->getBindings());
                while (true) {
                    $results = $this->getConnection()->select("fetch forward {$chunkSize} from {$cursor}");
                    foreach ($results as $result) {
                        yield $result;
                    }

                    if (\count($results) < $chunkSize) {
                        break;
                    }
                }
            } finally {
                $this->getConnection()->statement("close {$cursor}");
            }
        });
    }
}
