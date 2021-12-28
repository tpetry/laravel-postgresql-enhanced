<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait BuilderExplain
{
    public function explain(bool $analyze = false): Collection
    {
        $version = $this->getConnection()->selectOne('SHOW server_version')->server_version;
        $options = match (true) {
            $analyze && version_compare($version, '13') >= 0 => 'ANALYZE TRUE, BUFFERS TRUE, SETTINGS TRUE, VERBOSE TRUE, WAL TRUE',
            $analyze && version_compare($version, '12') >= 0 => 'ANALYZE TRUE, BUFFERS TRUE, SETTINGS TRUE, VERBOSE TRUE',
            $analyze => 'ANALYZE TRUE, BUFFERS TRUE, VERBOSE TRUE',
            version_compare($version, '12') >= 0 => 'SETTINGS TRUE, SUMMARY TRUE, VERBOSE TRUE',
            default => 'SUMMARY TRUE, VERBOSE TRUE',
        };

        return (new Collection($this->getConnection()->select("EXPLAIN ({$options}) {$this->toSql()}", $this->getBindings())))
            ->map(fn ($row) => Arr::first($row))
            ->reduce(function (?Collection $carry, string $item) {
                if (null === $carry) {
                    return new Collection([$item]);
                }

                return new Collection(["{$carry[0]}\n{$item}"]);
            });
    }
}
