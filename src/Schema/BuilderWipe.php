<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Schema;

trait BuilderWipe
{
    public function dropAllContinuousAggregates(): void
    {
        $continuousAggregates = rescue(
            callback: fn () => $this->getConnection()->table('timescaledb_information.continuous_aggregates')->select(['view_schema', 'view_name'])->whereIn('view_schema', $this->getActiveSchemas())->get(),
            rescue: [],
            report: false,
        );
        foreach ($continuousAggregates as $continuousAggregate) {
            $name = "{$continuousAggregate->view_schema}.{$continuousAggregate->view_name}";
            if (!\in_array($name, $this->connection->getConfig('dont_drop') ?? [])) {
                $this->connection->statement("drop materialized view if exists {$this->grammar->wrap($name)} cascade");
            }
        }
    }

    public function dropAllHypertables(): void
    {
        $hypertables = rescue(
            callback: fn () => $this->getConnection()->table('timescaledb_information.hypertables')->select(['hypertable_schema', 'hypertable_name'])->whereIn('hypertable_schema', $this->getActiveSchemas())->get(),
            rescue: [],
            report: false,
        );
        foreach ($hypertables as $hypertable) {
            $name = "{$hypertable->hypertable_schema}.{$hypertable->hypertable_name}";
            if (!\in_array($name, $this->connection->getConfig('dont_drop') ?? [])) {
                $this->connection->statement("drop table if exists {$this->grammar->wrap($name)} cascade");
            }
        }
    }

    public function dropAllMaterializedViews(): void
    {
        $materializedViews = $this->getConnection()->table('pg_matviews')->select(['schemaname', 'matviewname'])->whereIn('schemaname', $this->getActiveSchemas())->get();
        foreach ($materializedViews as $materializedView) {
            $name = "{$materializedView->schemaname}.{$materializedView->matviewname}";
            if (!\in_array($name, $this->connection->getConfig('dont_drop') ?? [])) {
                $this->connection->statement("drop materialized view if exists {$this->grammar->wrap($name)} cascade");
            }
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
        parent::dropAllViews();
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
