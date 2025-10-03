<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

use Illuminate\Support\Collection;

trait BuilderWipe
{
    public function dropAllContinuousAggregates(): void
    {
        if (!$this->getConnection()->table('information_schema.tables')->where('table_schema', 'timescaledb_information')->where('table_name', 'continuous_aggregates')->exists()) {
            return;
        }

        $continuousAggregates = $this->findRelations($this->getConnection()->table('timescaledb_information.continuous_aggregates')->select(['view_schema as schema', 'view_name as name'])->whereIn('view_schema', $this->getActiveSchemas())->get());
        foreach ($continuousAggregates as $continuousAggregate) {
            $this->connection->statement("drop materialized view if exists {$this->grammar->wrap($continuousAggregate['schema_qualified_name'])} cascade");
        }
    }

    public function dropAllHypertables(): void
    {
        if (!$this->getConnection()->table('information_schema.tables')->where('table_schema', 'timescaledb_information')->where('table_name', 'hypertables')->exists()) {
            return;
        }

        $hypertables = $this->findRelations($this->getConnection()->table('timescaledb_information.hypertables')->select(['hypertable_schema as schema', 'hypertable_name as name'])->whereIn('hypertable_schema', $this->getActiveSchemas())->get());
        foreach ($hypertables as $hypertable) {
            $this->connection->statement("drop table if exists {$this->grammar->wrap($hypertable['schema_qualified_name'])} cascade");
        }
    }

    public function dropAllMaterializedViews(): void
    {
        $materializedViews = $this->findRelations(
            $this->getConnection()->table('pg_matviews')->select(['schemaname as schema', 'matviewname as name'])->whereIn('schemaname', $this->getActiveSchemas())->get()
        );
        foreach ($materializedViews as $materializedView) {
            $this->connection->statement("drop materialized view if exists {$this->grammar->wrap($materializedView['schema_qualified_name'])} cascade");
        }
    }

    public function dropAllTables(): void
    {
        $this->dropAllHypertables();
        parent::dropAllTables();
    }

    public function dropAllViews(): void
    {
        $this->dropAllContinuousAggregates();
        $this->dropAllMaterializedViews();

        $views = $this->findRelations($this->connection->table('pg_views')->select(['schemaname as schema', 'viewname as name'])->whereIn('schemaname', $this->getActiveSchemas())->get(), [
            // PostGIS
            'geometry_columns',
            'geography_columns',
            // pg_buffercache
            'pg_buffercache',
            // pgRouting
            'raster_columns',
            'raster_overviews',
        ]);
        if (filled($views)) {
            $this->connection->statement("drop view {$this->grammar->columnize(array_column($views, 'schema_qualified_name'))} cascade");
        }
    }

    /**
     * @return array<array{schema_qualified_name: string}>
     */
    private function findRelations(Collection $relations, array $exclude = []): array
    {
        $excludedRelations = array_merge($this->connection->getConfig('dont_drop') ?? [], $exclude);

        $avilableRelations = array_map(fn ($relation) => [
            'name' => $relation->name,
            'schema_qualified_name' => "{$relation->schema}.{$relation->name}",
        ], $relations->all());

        return array_filter($avilableRelations, function ($relation) use ($excludedRelations) {
            return blank(array_intersect([$relation['name'], $relation['schema_qualified_name']], $excludedRelations));
        });
    }

    /**
     * @return string[]
     */
    private function getActiveSchemas(): array
    {
        return rescue(
            callback: fn () => $this->getCurrentSchemaListing(), // Laravel >= 12.x
            rescue: [$this->connection->getConfig('schema') ?? 'public'], // Laravel < 12.x
            report: false,
        );
    }
}
