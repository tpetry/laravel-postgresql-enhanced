<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Expressions;

use DateTimeInterface;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;

class Uuid7 implements Expression
{
    public function __construct(
        private readonly ?DateTimeInterface $time = null,
    ) {
    }

    public function getValue(Grammar $grammar): string
    {
        // The UUIDv7 algorithm in pure PostgreSQL SQL is copied from:
        // https://gist.github.com/fabiolimace/515a0440e3e40efeb234e12644a6a346#file-uuidv7-sql

        $c_milli_factor = '10^3::numeric'; // 1000
        $c_micro_factor = '10^6::numeric'; // 1000000
        $c_scale_factor = '4.096::numeric'; // 4.0 * (1024 / 1000)
        $c_version = "x'0000000000007000'::bit(64)"; // RFC-4122 version: b'0111...'
        $c_variant = "x'8000000000000000'::bit(64)"; // RFC-4122 variant: b'10xx...'

        $v_time = match ($this->time) {
            null => 'extract(epoch from statement_timestamp())',
            default => "extract(epoch from '{$this->time->format('Y-m-d H:i:s.uP')}'::timestamptz)",
        };

        $v_unix_t = "trunc({$v_time} * {$c_milli_factor})";
        $v_unix_t_hex = "lpad(to_hex({$v_unix_t}::bigint), 12, '0')";

        $v_rand_a = "((({$v_time} * {$c_micro_factor}) - ({$v_unix_t} * {$c_milli_factor})) * {$c_scale_factor})";
        $v_rand_a_hex = "lpad(to_hex(({$v_rand_a}::bigint::bit(64) | {$c_version})::bigint), 4, '0')";

        $v_rand_b = '(random()::numeric * 2^62::numeric)';
        $v_rand_b_hex = "lpad(to_hex(({$v_rand_b}::bigint::bit(64) | {$c_variant})::bigint), 16, '0')";

        $v_output_bytes = "decode({$v_unix_t_hex} || {$v_rand_a_hex} || {$v_rand_b_hex}, 'hex')";

        return "encode({$v_output_bytes}, 'hex')::uuid";
    }
}
