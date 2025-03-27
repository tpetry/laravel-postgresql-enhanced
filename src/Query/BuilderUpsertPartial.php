<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Query;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Tpetry\PostgresqlEnhanced\Support\Helpers\Query;

trait BuilderUpsertPartial
{
    /**
     * Insert new records or update the existing ones.
     *
     * @param string|(callable(\Illuminate\Database\Query\Builder):mixed)|(callable(\Illuminate\Contracts\Database\Query\Builder):mixed) $where
     */
    public function upsertPartial(array $values, array|string $uniqueBy, ?array $update, string|callable $where): int
    {
        if (empty($values)) {
            return 0;
        } elseif ([] === $update) {
            return (int) $this->insert($values);
        }

        if (!\is_array(reset($values))) {
            $values = [$values];
        } else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = $value;
            }
        }

        if (null === $update) {
            $update = array_keys(reset($values));
        }

        if (method_exists($this, 'applyBeforeQueryCallbacks')) {
            $this->applyBeforeQueryCallbacks();
        }

        $bindings = $this->cleanBindings([
            ...Arr::flatten($values, 1),
            ...(new Collection($update))->reject(fn ($value, $key) => \is_int($key))->all(),
        ]);

        $upsert = $this->grammar->compileUpsert($this, $values, (array) $uniqueBy, $update);
        if ($where instanceof Closure) {
            $query = $where($this->getConnection()->query());
            $where = trim(str_replace('select * where', '', Query::toSql($query)));
        }

        return $this->connection->affectingStatement(
            str_replace('do update set', "where {$where} do update set", $upsert),
            $bindings
        );
    }
}
