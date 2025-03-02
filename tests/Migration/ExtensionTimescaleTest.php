<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\Artisan;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Schema\Grammars\Grammar;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\Action;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\ChangeChunkTimeInterval;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CompressChunks;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateCompressionPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateHypertable;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateRefreshPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateReorderPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateReorderPolicyByIndex;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateReorderPolicyByUnique;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateRetentionPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateTieringPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DecompressChunks;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DisableChunkSkipping;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DisableCompression;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DropChunks;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DropCompressionPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DropRefreshPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DropReorderPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DropRetentionPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\DropTieringPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\EnableChunkSkipping;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\EnableCompression;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\RefreshData;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\ReorderChunks;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\TierChunks;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\UntierChunks;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\CaggBlueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class ExtensionTimescaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::createExtensionIfNotExists('timescaledb');
    }

    public function testActionChangeChunkTimeInterval(): void
    {
        $this->assertEquals(["select set_chunk_time_interval('tbl', interval '1 month')"], $this->toSql(new ChangeChunkTimeInterval('1 month')));
        $this->assertEquals(["select set_chunk_time_interval('tbl', 86400)"], $this->toSql(new ChangeChunkTimeInterval('86400')));
        $this->assertEquals(["select set_chunk_time_interval('tbl', 86400)"], $this->toSql(new ChangeChunkTimeInterval(86400)));
    }

    public function testActionCompressChunks(): void
    {
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl') c"], $this->toSql(new CompressChunks()));

        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new CompressChunks(olderThan: 5000)));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new CompressChunks(newerThan: 5000)));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new CompressChunks(olderThan: 5000, newerThan: 5000)));

        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new CompressChunks(olderThan: '5000')));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new CompressChunks(newerThan: '5000')));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new CompressChunks(olderThan: '5000', newerThan: '5000')));

        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', older_than => interval '1 month') c"], $this->toSql(new CompressChunks(olderThan: '1 month')));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', newer_than => interval '1 month') c"], $this->toSql(new CompressChunks(newerThan: '1 month')));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', older_than => interval '1 month', newer_than => interval '1 month') c"], $this->toSql(new CompressChunks(olderThan: '1 month', newerThan: '1 month')));

        $date = DateTimeImmutable::createFromFormat('U', '1736936862', new DateTimeZone('UTC'));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new CompressChunks(olderThan: $date)));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new CompressChunks(newerThan: $date)));
        $this->assertEquals(["select compress_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new CompressChunks(olderThan: $date, newerThan: $date)));
    }

    public function testActionCreateCompressionPolicy(): void
    {
        $this->assertEquals(["select add_compression_policy('tbl', compress_after => interval '1 month')"], $this->toSql(new CreateCompressionPolicy('1 month')));
        $this->assertEquals(["select add_compression_policy('tbl', compress_after => 86400)"], $this->toSql(new CreateCompressionPolicy('86400')));
        $this->assertEquals(["select add_compression_policy('tbl', compress_after => 86400)"], $this->toSql(new CreateCompressionPolicy(86400)));
    }

    public function testActionCreateHypertable(): void
    {
        $this->assertEquals(["select create_hypertable('tbl', by_range('created_at', interval '1 month'), create_default_indexes => false)"], $this->toSql(new CreateHypertable('created_at', '1 month')));
        $this->assertEquals(["select create_hypertable('tbl', by_range('created_at', interval '1 month', 'func'), create_default_indexes => false)"], $this->toSql(new CreateHypertable('created_at', '1 month', 'func')));

        $this->assertEquals(["select create_hypertable('tbl', by_range('created_at', 86400), create_default_indexes => false)"], $this->toSql(new CreateHypertable('created_at', '86400')));
        $this->assertEquals(["select create_hypertable('tbl', by_range('created_at', 86400, 'func'), create_default_indexes => false)"], $this->toSql(new CreateHypertable('created_at', '86400', 'func')));
    }

    public function testActionCreateRefreshPolicy(): void
    {
        $this->assertEquals(["select add_continuous_aggregate_policy('tbl', null, null, interval '5 minutes')"], $this->toSql(new CreateRefreshPolicy('5 minutes', null, null)));

        $this->assertEquals(["select add_continuous_aggregate_policy('tbl', 86400, 86400, interval '1 minutes')"], $this->toSql(new CreateRefreshPolicy('1 minutes', 86400, 86400)));
        $this->assertEquals(["select add_continuous_aggregate_policy('tbl', 86400, null, interval '1 minutes')"], $this->toSql(new CreateRefreshPolicy('1 minutes', 86400, null)));
        $this->assertEquals(["select add_continuous_aggregate_policy('tbl', null, 86400, interval '1 minutes')"], $this->toSql(new CreateRefreshPolicy('1 minutes', null, 86400)));

        $this->assertEquals(["select add_continuous_aggregate_policy('tbl', interval '1 day', interval '1 day', interval '1 minutes')"], $this->toSql(new CreateRefreshPolicy('1 minutes', '1 day', '1 day')));
        $this->assertEquals(["select add_continuous_aggregate_policy('tbl', interval '1 day', null, interval '1 minutes')"], $this->toSql(new CreateRefreshPolicy('1 minutes', '1 day', null)));
        $this->assertEquals(["select add_continuous_aggregate_policy('tbl', null, interval '1 day', interval '1 minutes')"], $this->toSql(new CreateRefreshPolicy('1 minutes', null, '1 day')));
    }

    public function testActionCreateReorderPolicy(): void
    {
        $this->assertEquals(["select add_reorder_policy('tbl', 'tenant_idx')"], $this->toSql(new CreateReorderPolicy('tenant_idx')));
    }

    public function testActionCreateReorderPolicyByIndex(): void
    {
        $this->assertEquals(["select add_reorder_policy('tbl', 'tbl_tenant_id_created_at_index')"], $this->toSql(new CreateReorderPolicyByIndex('tenant_id', 'created_at')));
    }

    public function testActionCreateReorderPolicyByUnique(): void
    {
        $this->assertEquals(["select add_reorder_policy('tbl', 'tbl_tenant_id_created_at_unique')"], $this->toSql(new CreateReorderPolicyByUnique('tenant_id', 'created_at')));
    }

    public function testActionCreateRetentionPolicy(): void
    {
        $this->assertEquals(["select add_retention_policy('tbl', drop_after => interval '1 month')"], $this->toSql(new CreateRetentionPolicy('1 month')));
        $this->assertEquals(["select add_retention_policy('tbl', drop_after => 86400)"], $this->toSql(new CreateRetentionPolicy('86400')));
        $this->assertEquals(["select add_retention_policy('tbl', drop_after => 86400)"], $this->toSql(new CreateRetentionPolicy(86400)));
    }

    public function testActionCreateTieringPolicy(): void
    {
        $this->assertEquals(["select add_tiering_policy('tbl', move_after => interval '1 month')"], $this->toSql(new CreateTieringPolicy('1 month')));
        $this->assertEquals(["select add_tiering_policy('tbl', move_after => 86400)"], $this->toSql(new CreateTieringPolicy('86400')));
        $this->assertEquals(["select add_tiering_policy('tbl', move_after => 86400)"], $this->toSql(new CreateTieringPolicy(86400)));
    }

    public function testActionDeompressChunks(): void
    {
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl') c"], $this->toSql(new DecompressChunks()));

        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new DecompressChunks(olderThan: 5000)));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new DecompressChunks(newerThan: 5000)));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new DecompressChunks(olderThan: 5000, newerThan: 5000)));

        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new DecompressChunks(olderThan: '5000')));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new DecompressChunks(newerThan: '5000')));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new DecompressChunks(olderThan: '5000', newerThan: '5000')));

        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', older_than => interval '1 month') c"], $this->toSql(new DecompressChunks(olderThan: '1 month')));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', newer_than => interval '1 month') c"], $this->toSql(new DecompressChunks(newerThan: '1 month')));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', older_than => interval '1 month', newer_than => interval '1 month') c"], $this->toSql(new DecompressChunks(olderThan: '1 month', newerThan: '1 month')));

        $date = DateTimeImmutable::createFromFormat('U', '1736936862', new DateTimeZone('UTC'));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new DecompressChunks(olderThan: $date)));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new DecompressChunks(newerThan: $date)));
        $this->assertEquals(["select decompress_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new DecompressChunks(olderThan: $date, newerThan: $date)));
    }

    public function testActionDisableChunkSkipping(): void
    {
        $this->assertEquals(["select disable_chunk_skipping('tbl', 'id')"], $this->toSql(new DisableChunkSkipping('id')));
    }

    public function testActionDisableCompression(): void
    {
        $this->assertEquals(['alter table "tbl" set (timescaledb.compress = false)'], $this->toSql(new DisableCompression()));
    }

    public function testActionDropChunks(): void
    {
        $this->assertEquals(["select drop_chunks('tbl')"], $this->toSql(new DropChunks()));

        $this->assertEquals(["select drop_chunks('tbl', older_than => 5000)"], $this->toSql(new DropChunks(olderThan: 5000)));
        $this->assertEquals(["select drop_chunks('tbl', newer_than => 5000)"], $this->toSql(new DropChunks(newerThan: 5000)));
        $this->assertEquals(["select drop_chunks('tbl', older_than => 5000, newer_than => 5000)"], $this->toSql(new DropChunks(olderThan: 5000, newerThan: 5000)));

        $this->assertEquals(["select drop_chunks('tbl', older_than => 5000)"], $this->toSql(new DropChunks(olderThan: '5000')));
        $this->assertEquals(["select drop_chunks('tbl', newer_than => 5000)"], $this->toSql(new DropChunks(newerThan: '5000')));
        $this->assertEquals(["select drop_chunks('tbl', older_than => 5000, newer_than => 5000)"], $this->toSql(new DropChunks(olderThan: '5000', newerThan: '5000')));

        $this->assertEquals(["select drop_chunks('tbl', older_than => interval '1 month')"], $this->toSql(new DropChunks(olderThan: '1 month')));
        $this->assertEquals(["select drop_chunks('tbl', newer_than => interval '1 month')"], $this->toSql(new DropChunks(newerThan: '1 month')));
        $this->assertEquals(["select drop_chunks('tbl', older_than => interval '1 month', newer_than => interval '1 month')"], $this->toSql(new DropChunks(olderThan: '1 month', newerThan: '1 month')));

        $date = DateTimeImmutable::createFromFormat('U', '1736936862', new DateTimeZone('UTC'));
        $this->assertEquals(["select drop_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00')"], $this->toSql(new DropChunks(olderThan: $date)));
        $this->assertEquals(["select drop_chunks('tbl', newer_than => '2025-01-15T10:27:42+00:00')"], $this->toSql(new DropChunks(newerThan: $date)));
        $this->assertEquals(["select drop_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00', newer_than => '2025-01-15T10:27:42+00:00')"], $this->toSql(new DropChunks(olderThan: $date, newerThan: $date)));
    }

    public function testActionDropCompressionPolicy(): void
    {
        $this->assertEquals(["select remove_compression_policy('tbl')"], $this->toSql(new DropCompressionPolicy()));
    }

    public function testActionDropRefreshPolicy(): void
    {
        $this->assertEquals(["select remove_continuous_aggregate_policy('tbl')"], $this->toSql(new DropRefreshPolicy()));
    }

    public function testActionDropReorderPolicy(): void
    {
        $this->assertEquals(["select remove_reorder_policy('tbl')"], $this->toSql(new DropReorderPolicy()));
    }

    public function testActionDropRetentionPolicy(): void
    {
        $this->assertEquals(["select remove_retention_policy('tbl')"], $this->toSql(new DropRetentionPolicy()));
    }

    public function testActionDropTieringPolicy(): void
    {
        $this->assertEquals(["select remove_tiering_policy('tbl')"], $this->toSql(new DropTieringPolicy()));
    }

    public function testActionEnableChunkSkipping(): void
    {
        $this->assertEquals(["select enable_chunk_skipping('tbl', 'id')"], $this->toSql(new EnableChunkSkipping('id')));
    }

    public function testActionEnableCompression(): void
    {
        $this->assertEquals(['alter table "tbl" set (timescaledb.compress = true)'], $this->toSql(new EnableCompression()));

        $this->assertEquals(['alter table "tbl" set (timescaledb.compress = true, timescaledb.compress_orderby = \'"time"\')'], $this->toSql(new EnableCompression(orderBy: 'time')));
        $this->assertEquals(['alter table "tbl" set (timescaledb.compress = true, timescaledb.compress_orderby = \'"time", "time2"\')'], $this->toSql(new EnableCompression(orderBy: ['time', 'time2'])));

        $this->assertEquals(['alter table "tbl" set (timescaledb.compress = true, timescaledb.compress_segmentby = \'"tenant_id"\')'], $this->toSql(new EnableCompression(segmentBy: 'tenant_id')));
        $this->assertEquals(['alter table "tbl" set (timescaledb.compress = true, timescaledb.compress_segmentby = \'"tenant_id", "shop_id"\')'], $this->toSql(new EnableCompression(segmentBy: ['tenant_id', 'shop_id'])));

        $this->assertEquals(['alter table "tbl" set (timescaledb.compress = true, timescaledb.compress_orderby = \'"time"\', timescaledb.compress_segmentby = \'"tenant_id"\')'], $this->toSql(new EnableCompression(orderBy: 'time', segmentBy: 'tenant_id')));
        $this->assertEquals(['alter table "tbl" set (timescaledb.compress = true, timescaledb.compress_orderby = \'"time", "time2"\', timescaledb.compress_segmentby = \'"tenant_id", "shop_id"\')'], $this->toSql(new EnableCompression(orderBy: ['time', 'time2'], segmentBy: ['tenant_id', 'shop_id'])));
    }

    public function testActionReorderChunks(): void
    {
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl') c"], $this->toSql(new ReorderChunks()));

        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new ReorderChunks(olderThan: 5000)));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new ReorderChunks(newerThan: 5000)));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new ReorderChunks(olderThan: 5000, newerThan: 5000)));

        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new ReorderChunks(olderThan: '5000')));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new ReorderChunks(newerThan: '5000')));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new ReorderChunks(olderThan: '5000', newerThan: '5000')));

        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', older_than => interval '1 month') c"], $this->toSql(new ReorderChunks(olderThan: '1 month')));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', newer_than => interval '1 month') c"], $this->toSql(new ReorderChunks(newerThan: '1 month')));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', older_than => interval '1 month', newer_than => interval '1 month') c"], $this->toSql(new ReorderChunks(olderThan: '1 month', newerThan: '1 month')));

        $date = DateTimeImmutable::createFromFormat('U', '1736936862', new DateTimeZone('UTC'));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new ReorderChunks(olderThan: $date)));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new ReorderChunks(newerThan: $date)));
        $this->assertEquals(["select reorder_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new ReorderChunks(olderThan: $date, newerThan: $date)));
    }

    public function testActionTierChunks(): void
    {
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl') c"], $this->toSql(new TierChunks()));

        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new TierChunks(olderThan: 5000)));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new TierChunks(newerThan: 5000)));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new TierChunks(olderThan: 5000, newerThan: 5000)));

        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new TierChunks(olderThan: '5000')));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new TierChunks(newerThan: '5000')));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new TierChunks(olderThan: '5000', newerThan: '5000')));

        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', older_than => interval '1 month') c"], $this->toSql(new TierChunks(olderThan: '1 month')));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', newer_than => interval '1 month') c"], $this->toSql(new TierChunks(newerThan: '1 month')));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', older_than => interval '1 month', newer_than => interval '1 month') c"], $this->toSql(new TierChunks(olderThan: '1 month', newerThan: '1 month')));

        $date = DateTimeImmutable::createFromFormat('U', '1736936862', new DateTimeZone('UTC'));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new TierChunks(olderThan: $date)));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new TierChunks(newerThan: $date)));
        $this->assertEquals(["select tier_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new TierChunks(olderThan: $date, newerThan: $date)));
    }

    public function testActionUntierChunks(): void
    {
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl') c"], $this->toSql(new UntierChunks()));

        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new UntierChunks(olderThan: 5000)));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new UntierChunks(newerThan: 5000)));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new UntierChunks(olderThan: 5000, newerThan: 5000)));

        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', older_than => 5000) c"], $this->toSql(new UntierChunks(olderThan: '5000')));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', newer_than => 5000) c"], $this->toSql(new UntierChunks(newerThan: '5000')));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', older_than => 5000, newer_than => 5000) c"], $this->toSql(new UntierChunks(olderThan: '5000', newerThan: '5000')));

        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', older_than => interval '1 month') c"], $this->toSql(new UntierChunks(olderThan: '1 month')));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', newer_than => interval '1 month') c"], $this->toSql(new UntierChunks(newerThan: '1 month')));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', older_than => interval '1 month', newer_than => interval '1 month') c"], $this->toSql(new UntierChunks(olderThan: '1 month', newerThan: '1 month')));

        $date = DateTimeImmutable::createFromFormat('U', '1736936862', new DateTimeZone('UTC'));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new UntierChunks(olderThan: $date)));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new UntierChunks(newerThan: $date)));
        $this->assertEquals(["select untier_chunk(c) from show_chunks('tbl', older_than => '2025-01-15T10:27:42+00:00', newer_than => '2025-01-15T10:27:42+00:00') c"], $this->toSql(new UntierChunks(olderThan: $date, newerThan: $date)));
    }

    public function testCreateContinuousAggregate(): void
    {
        $this->getConnection()->statement('create table hypertbl(time timestamptz)');
        $this->getConnection()->statement("select create_hypertable('hypertbl', by_range('time'))");

        $query = $this->getConnection()
            ->table('hypertbl')
            ->groupBy('bucket')
            ->select([
                $this->getConnection()->raw("time_bucket('1 day', time) bucket"),
                $this->getConnection()->raw('count(*) count'),
            ]);

        $queries = $this->withQueryLog(function () use ($query): void {
            Schema::continuousAggregate('hypertbl_agg', function (CaggBlueprint $table) use ($query): void {
                $table->as($query);
                $table->realtime();

                $table->index(['bucket'])->include('count');
                $table->dropIndex(['bucket']);
                $table->dropIndexIfExists(['bucket']);

                $table->index(['bucket'], 'idx')->where('count > 0');
                $table->dropIndex('idx');
                $table->dropIndexIfExists('idx');

                $table->timescale(new EnableCompression());
            });
        });

        $this->assertEquals([
            'create materialized view "hypertbl_agg" with (timescaledb.continuous, timescaledb.create_group_indexes = false) AS select time_bucket(\'1 day\', time) bucket, count(*) count from "hypertbl" group by "bucket" with no data',
            'alter materialized view "hypertbl_agg" set (timescaledb.materialized_only = false)',
            'create index "hypertbl_agg_bucket_index" on "hypertbl_agg" ("bucket") include ("count")',
            'drop index _timescaledb_internal.hypertbl_agg_bucket_index',
            'drop index if exists _timescaledb_internal.hypertbl_agg_bucket_index',
            'create index "idx" on "hypertbl_agg" ("bucket") where count > 0',
            'drop index _timescaledb_internal.idx',
            'drop index if exists _timescaledb_internal.idx',
            'alter materialized view "hypertbl_agg" set (timescaledb.compress = true)',
        ], array_column($queries, 'query'));
    }

    public function testDbWipeWillRemoveAllTimescaleStuff(): void
    {
        Schema::create('hypertbl', function (Blueprint $table): void {
            $table->text('tenant_id');
            $table->timestampsTz();
            $table->timescale(new CreateHypertable('created_at', '14 days'));
        });
        Schema::continuousAggregate('hypertbl_agg', function (CaggBlueprint $table): void {
            $table->as("select time_bucket('1 day', created_at) bucket, tenant_id, count(*) from hypertbl group by bucket, tenant_id");
        });

        Artisan::call('db:wipe --drop-views');

        $this->assertEquals(0, $this->getConnection()->table('timescaledb_information.hypertables')->count());
        $this->assertEquals(0, $this->getConnection()->table('timescaledb_information.continuous_aggregates')->count());
    }

    public function testDropContinuousAggregate(): void
    {
        $this->getConnection()->statement('create table hypertbl(time timestamptz)');
        $this->getConnection()->statement("select create_hypertable('hypertbl', by_range('time'))");
        $this->getConnection()->statement("create materialized view hypertbl_agg with (timescaledb.continuous) AS select time_bucket('1 day', time) bucket, count(*) from hypertbl group by bucket with no data");

        $queries = $this->withQueryLog(function (): void {
            Schema::dropContinuousAggregate('hypertbl_agg');
        });

        $this->assertEquals(['drop materialized view "hypertbl_agg"'], array_column($queries, 'query'));
    }

    public function testDropContinuousAggregateIfExists(): void
    {
        $this->getConnection()->statement('create table hypertbl(time timestamptz)');
        $this->getConnection()->statement("select create_hypertable('hypertbl', by_range('time'))");
        $this->getConnection()->statement("create materialized view hypertbl_agg with (timescaledb.continuous) AS select time_bucket('1 day', time) bucket, count(*) from hypertbl group by bucket with no data");

        $queries = $this->withQueryLog(function (): void {
            Schema::dropContinuousAggregateIfExists('hypertbl_agg');
        });

        $this->assertEquals(['drop materialized view if exists "hypertbl_agg"'], array_column($queries, 'query'));
    }

    public function testRefreshData(): void
    {
        $this->assertEquals(["call refresh_continuous_aggregate('tbl', null, null)"], $this->toSql(new RefreshData(start: null, end: null)));

        $this->assertEquals(["call refresh_continuous_aggregate('tbl', 5000, 5000)"], $this->toSql(new RefreshData(start: 5000, end: 5000)));
        $this->assertEquals(["call refresh_continuous_aggregate('tbl', 5000, null)"], $this->toSql(new RefreshData(start: 5000, end: null)));
        $this->assertEquals(["call refresh_continuous_aggregate('tbl', null, 5000)"], $this->toSql(new RefreshData(start: null, end: 5000)));

        $date = DateTimeImmutable::createFromFormat('U', '1736936862', new DateTimeZone('UTC'));
        $this->assertEquals(["call refresh_continuous_aggregate('tbl', '2025-01-15T10:27:42+00:00', '2025-01-15T10:27:42+00:00')"], $this->toSql(new RefreshData(start: $date, end: $date)));
        $this->assertEquals(["call refresh_continuous_aggregate('tbl', '2025-01-15T10:27:42+00:00', null)"], $this->toSql(new RefreshData(start: $date, end: null)));
        $this->assertEquals(["call refresh_continuous_aggregate('tbl', null, '2025-01-15T10:27:42+00:00')"], $this->toSql(new RefreshData(start: null, end: $date)));
    }

    public function testTimescaleActionsAreExecuted(): void
    {
        $action = new class implements Action {
            public function getValue(\Illuminate\Database\Grammar $grammar, string $table): array
            {
                return ["select {$grammar->escape($table)}"];
            }
        };

        $queries = $this->withQueryLog(function () use ($action): void {
            Schema::table('tbl', function (Blueprint $table) use ($action): void {
                $table->timescale($action);
            });
        });

        $this->assertEquals(["select 'tbl'"], array_column($queries, 'query'));
    }

    private function toSql(Action $action): array
    {
        $grammar = new Grammar($this->getConnection());

        return $action->getValue($grammar, 'tbl');
    }
}
