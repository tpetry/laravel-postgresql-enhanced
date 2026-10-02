<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Illuminate\Database\Query\Expression;
use Tpetry\PostgresqlEnhanced\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class PolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->statement('CREATE USER usr_226471');
        $this->getConnection()->statement('CREATE USER usr_400228');
        $this->getConnection()->statement('CREATE USER usr_787523');
    }

    public function testAlterPolicy(): void
    {
        $this->getConnection()->statement('create table "table_185640" (tenant_id integer)');
        $this->getConnection()->statement('create policy "policy_830166" on "table_185640" for all');
        $this->getConnection()->statement('create policy "policy_781333" on "table_185640" for all');
        $this->getConnection()->statement('create policy "policy_454949" on "table_185640" for all');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_185640', static function (Blueprint $table): void {
                $table->alterPolicy('policy_830166', 'usr_226471', 'tenant_id = 688172');
                $table->alterPolicy('policy_781333', 'usr_400228', new Expression('tenant_id = 859316'));
                $table->alterPolicy('policy_454949', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 934616));
            });
        });
        $this->assertEquals([
            'alter policy "policy_830166" on "table_185640" to "usr_226471" using (tenant_id = 688172)',
            'alter policy "policy_781333" on "table_185640" to "usr_400228" using (tenant_id = 859316)',
            'alter policy "policy_454949" on "table_185640" to "usr_787523" using ("tenant_id" = 934616)',
        ], array_column($queries, 'query'));
    }

    public function testAlterPolicyDelete(): void
    {
        $this->getConnection()->statement('create table "table_234899" (tenant_id integer)');
        $this->getConnection()->statement('create policy "policy_676048_delete" on "table_234899" for delete');
        $this->getConnection()->statement('create policy "policy_460239_delete" on "table_234899" for delete');
        $this->getConnection()->statement('create policy "policy_903750_delete" on "table_234899" for delete');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_234899', static function (Blueprint $table): void {
                $table->alterPolicyDelete('policy_676048', 'usr_226471', 'tenant_id = 663254');
                $table->alterPolicyDelete('policy_460239', 'usr_400228', new Expression('tenant_id = 512698'));
                $table->alterPolicyDelete('policy_903750', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 355055));
            });
        });
        $this->assertEquals([
            'alter policy "policy_676048_delete" on "table_234899" to "usr_226471" using (tenant_id = 663254)',
            'alter policy "policy_460239_delete" on "table_234899" to "usr_400228" using (tenant_id = 512698)',
            'alter policy "policy_903750_delete" on "table_234899" to "usr_787523" using ("tenant_id" = 355055)',
        ], array_column($queries, 'query'));
    }

    public function testAlterPolicyInsert(): void
    {
        $this->getConnection()->statement('create table "table_377166" (tenant_id integer)');
        $this->getConnection()->statement('create policy "policy_291025_insert" on "table_377166" for insert');
        $this->getConnection()->statement('create policy "policy_506897_insert" on "table_377166" for insert');
        $this->getConnection()->statement('create policy "policy_432593_insert" on "table_377166" for insert');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_377166', static function (Blueprint $table): void {
                $table->alterPolicyInsert('policy_291025', 'usr_226471', 'tenant_id = 281534');
                $table->alterPolicyInsert('policy_506897', 'usr_400228', new Expression('tenant_id = 837181'));
                $table->alterPolicyInsert('policy_432593', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 629387));
            });
        });
        $this->assertEquals([
            'alter policy "policy_291025_insert" on "table_377166" to "usr_226471" with check (tenant_id = 281534)',
            'alter policy "policy_506897_insert" on "table_377166" to "usr_400228" with check (tenant_id = 837181)',
            'alter policy "policy_432593_insert" on "table_377166" to "usr_787523" with check ("tenant_id" = 629387)',
        ], array_column($queries, 'query'));
    }

    public function testAlterPolicySelect(): void
    {
        $this->getConnection()->statement('create table "table_486743" (tenant_id integer)');
        $this->getConnection()->statement('create policy "policy_771539_select" on "table_486743" for select');
        $this->getConnection()->statement('create policy "policy_545848_select" on "table_486743" for select');
        $this->getConnection()->statement('create policy "policy_642654_select" on "table_486743" for select');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_486743', static function (Blueprint $table): void {
                $table->alterPolicySelect('policy_771539', 'usr_226471', 'tenant_id = 335511');
                $table->alterPolicySelect('policy_545848', 'usr_400228', new Expression('tenant_id = 171243'));
                $table->alterPolicySelect('policy_642654', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 232713));
            });
        });
        $this->assertEquals([
            'alter policy "policy_771539_select" on "table_486743" to "usr_226471" using (tenant_id = 335511)',
            'alter policy "policy_545848_select" on "table_486743" to "usr_400228" using (tenant_id = 171243)',
            'alter policy "policy_642654_select" on "table_486743" to "usr_787523" using ("tenant_id" = 232713)',
        ], array_column($queries, 'query'));
    }

    public function testAlterPolicyUpdate(): void
    {
        $this->getConnection()->statement('create table "table_827031" (tenant_id integer)');
        $this->getConnection()->statement('create policy "policy_441373_update" on "table_827031" for update');
        $this->getConnection()->statement('create policy "policy_177812_update" on "table_827031" for update');
        $this->getConnection()->statement('create policy "policy_508577_update" on "table_827031" for update');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_827031', static function (Blueprint $table): void {
                $table->alterPolicyUpdate('policy_441373', 'usr_226471', 'tenant_id = 690543');
                $table->alterPolicyUpdate('policy_177812', 'usr_400228', new Expression('tenant_id = 384070'));
                $table->alterPolicyUpdate('policy_508577', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 519567));
            });
        });
        $this->assertEquals([
            'alter policy "policy_441373_update" on "table_827031" to "usr_226471" using (tenant_id = 690543)',
            'alter policy "policy_177812_update" on "table_827031" to "usr_400228" using (tenant_id = 384070)',
            'alter policy "policy_508577_update" on "table_827031" to "usr_787523" using ("tenant_id" = 519567)',
        ], array_column($queries, 'query'));
    }

    public function testCreatePolicy(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::create('table_523468', static function (Blueprint $table): void {
                $table->integer('tenant_id');
                $table->createPolicy('policy_197631', 'usr_226471', 'tenant_id = 154243');
                $table->createPolicy('policy_460772', 'usr_400228', new Expression('tenant_id = 813136'));
                $table->createPolicy('policy_549300', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 582240));
            });
        });
        $this->assertEquals([
            'create table "table_523468" ("tenant_id" integer not null)',
            'create policy "policy_197631" on "table_523468" for all to "usr_226471" using (tenant_id = 154243)',
            'create policy "policy_460772" on "table_523468" for all to "usr_400228" using (tenant_id = 813136)',
            'create policy "policy_549300" on "table_523468" for all to "usr_787523" using ("tenant_id" = 582240)',
        ], array_column($queries, 'query'));
    }

    public function testCreatePolicyDelete(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::create('table_610120', static function (Blueprint $table): void {
                $table->integer('tenant_id');
                $table->createPolicyDelete('policy_982550', 'usr_226471', 'tenant_id = 247994');
                $table->createPolicyDelete('policy_109082', 'usr_400228', new Expression('tenant_id = 656077'));
                $table->createPolicyDelete('policy_591175', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 350023));
            });
        });
        $this->assertEquals([
            'create table "table_610120" ("tenant_id" integer not null)',
            'create policy "policy_982550_delete" on "table_610120" for delete to "usr_226471" using (tenant_id = 247994)',
            'create policy "policy_109082_delete" on "table_610120" for delete to "usr_400228" using (tenant_id = 656077)',
            'create policy "policy_591175_delete" on "table_610120" for delete to "usr_787523" using ("tenant_id" = 350023)',
        ], array_column($queries, 'query'));
    }

    public function testCreatePolicyInsert(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::create('table_980399', static function (Blueprint $table): void {
                $table->integer('tenant_id');
                $table->createPolicyInsert('policy_352354', 'usr_226471', 'tenant_id = 207244');
                $table->createPolicyInsert('policy_584673', 'usr_400228', new Expression('tenant_id = 103213'));
                $table->createPolicyInsert('policy_755917', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 528370));
            });
        });
        $this->assertEquals([
            'create table "table_980399" ("tenant_id" integer not null)',
            'create policy "policy_352354_insert" on "table_980399" for insert to "usr_226471" with check (tenant_id = 207244)',
            'create policy "policy_584673_insert" on "table_980399" for insert to "usr_400228" with check (tenant_id = 103213)',
            'create policy "policy_755917_insert" on "table_980399" for insert to "usr_787523" with check ("tenant_id" = 528370)',
        ], array_column($queries, 'query'));
    }

    public function testCreatePolicySelect(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::create('table_603937', static function (Blueprint $table): void {
                $table->integer('tenant_id');
                $table->createPolicySelect('policy_294523', 'usr_226471', 'tenant_id = 492221');
                $table->createPolicySelect('policy_564685', 'usr_400228', new Expression('tenant_id = 891446'));
                $table->createPolicySelect('policy_166754', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 696368));
            });
        });
        $this->assertEquals([
            'create table "table_603937" ("tenant_id" integer not null)',
            'create policy "policy_294523_select" on "table_603937" for select to "usr_226471" using (tenant_id = 492221)',
            'create policy "policy_564685_select" on "table_603937" for select to "usr_400228" using (tenant_id = 891446)',
            'create policy "policy_166754_select" on "table_603937" for select to "usr_787523" using ("tenant_id" = 696368)',
        ], array_column($queries, 'query'));
    }

    public function testCreatePolicyUpdate(): void
    {
        $queries = $this->withQueryLog(static function (): void {
            Schema::create('table_480476', static function (Blueprint $table): void {
                $table->integer('tenant_id');
                $table->createPolicyUpdate('policy_859353', 'usr_226471', 'tenant_id = 759384');
                $table->createPolicyUpdate('policy_789759', 'usr_400228', new Expression('tenant_id = 491345'));
                $table->createPolicyUpdate('policy_215816', 'usr_787523', static fn (Builder $query) => $query->where('tenant_id', 830689));
            });
        });
        $this->assertEquals([
            'create table "table_480476" ("tenant_id" integer not null)',
            'create policy "policy_859353_update" on "table_480476" for update to "usr_226471" using (tenant_id = 759384)',
            'create policy "policy_789759_update" on "table_480476" for update to "usr_400228" using (tenant_id = 491345)',
            'create policy "policy_215816_update" on "table_480476" for update to "usr_787523" using ("tenant_id" = 830689)',
        ], array_column($queries, 'query'));
    }

    public function testDropPolicy(): void
    {
        $this->getConnection()->statement('create table "table_888885" ()');
        $this->getConnection()->statement('create policy "policy_730248" on "table_888885" for all');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_888885', static function (Blueprint $table): void {
                $table->dropPolicy('policy_730248');
                $table->dropPolicyIfExists('policy_312342');
            });
        });
        $this->assertEquals([
            'drop policy "policy_730248" on "table_888885"',
            'drop policy if exists "policy_312342" on "table_888885"',
        ], array_column($queries, 'query'));
    }

    public function testDropPolicyDelete(): void
    {
        $this->getConnection()->statement('create table "table_639051" ()');
        $this->getConnection()->statement('create policy "policy_320939_delete" on "table_639051" for delete');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_639051', static function (Blueprint $table): void {
                $table->dropPolicyDelete('policy_320939');
                $table->dropPolicyDeleteIfExists('policy_275291');
            });
        });
        $this->assertEquals([
            'drop policy "policy_320939_delete" on "table_639051"',
            'drop policy if exists "policy_275291_delete" on "table_639051"',
        ], array_column($queries, 'query'));
    }

    public function testDropPolicyInsert(): void
    {
        $this->getConnection()->statement('create table "table_474782" ()');
        $this->getConnection()->statement('create policy "policy_163175_insert" on "table_474782" for insert');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_474782', static function (Blueprint $table): void {
                $table->dropPolicyInsert('policy_163175');
                $table->dropPolicyInsertIfExists('policy_913044');
            });
        });
        $this->assertEquals([
            'drop policy "policy_163175_insert" on "table_474782"',
            'drop policy if exists "policy_913044_insert" on "table_474782"',
        ], array_column($queries, 'query'));
    }

    public function testDropPolicySelect(): void
    {
        $this->getConnection()->statement('create table "table_767451" ()');
        $this->getConnection()->statement('create policy "policy_361730_select" on "table_767451" for select');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_767451', static function (Blueprint $table): void {
                $table->dropPolicySelect('policy_361730');
                $table->dropPolicySelectIfExists('policy_203648');
            });
        });
        $this->assertEquals([
            'drop policy "policy_361730_select" on "table_767451"',
            'drop policy if exists "policy_203648_select" on "table_767451"',
        ], array_column($queries, 'query'));
    }

    public function testDropPolicyUpdate(): void
    {
        $this->getConnection()->statement('create table "table_517354" ()');
        $this->getConnection()->statement('create policy "policy_663610_update" on "table_517354" for update');
        $queries = $this->withQueryLog(static function (): void {
            Schema::table('table_517354', static function (Blueprint $table): void {
                $table->dropPolicyUpdate('policy_663610');
                $table->dropPolicyUpdateIfExists('policy_463366');
            });
        });
        $this->assertEquals([
            'drop policy "policy_663610_update" on "table_517354"',
            'drop policy if exists "policy_463366_update" on "table_517354"',
        ], array_column($queries, 'query'));
    }
}
