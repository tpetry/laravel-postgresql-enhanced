<?php

declare(strict_types=1);

namespace Tpetry\PostgresqlEnhanced\Tests\Migration;

use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Tests\TestCase;

class FunctionTest extends TestCase
{
    public function testCreateFunctionCalledOnNullFalse(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_220422', ['p914738' => 'int'], 'int', 'plpgsql', 'begin select abs(p914738);end', [
                'calledOnNull' => false,
            ]);
        });
        $this->assertEquals(['create function "test_220422"("p914738" int) returns int language plpgsql returns null on null input as $$ begin select abs(p914738);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionCalledOnNullTrue(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_557041', ['p473781' => 'int'], 'int', 'plpgsql', 'begin select abs(p473781);end', [
                'calledOnNull' => true,
            ]);
        });
        $this->assertEquals(['create function "test_557041"("p473781" int) returns int language plpgsql called on null input as $$ begin select abs(p473781);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionCost(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_986688', ['p415305' => 'int'], 'int', 'plpgsql', 'begin select abs(p415305);end', [
                'cost' => 100,
            ]);
        });
        $this->assertEquals(['create function "test_986688"("p415305" int) returns int language plpgsql cost 100 as $$ begin select abs(p415305);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLanguagePlpgsql(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_666644', ['p700746' => 'int'], 'int', 'plpgsql', 'begin select abs(p700746);end');
        });
        $this->assertEquals(['create function "test_666644"("p700746" int) returns int language plpgsql as $$ begin select abs(p700746);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLanguageSql(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') < 0) {
            $this->markTestSkipped('SQL function bodies are first supported with PostgreSQL 14.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_515491', ['p903046' => 'int'], 'int', 'sql', 'select abs(p903046)');
        });
        $this->assertEquals(['create function "test_515491"("p903046" int) returns int language sql begin atomic; select abs(p903046); end'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLanguageSqlExpression(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') < 0) {
            $this->markTestSkipped('SQL function bodies are first supported with PostgreSQL 14.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_892788', ['p436580' => 'int'], 'int', 'sql:expression', 'abs(p436580)');
        });
        $this->assertEquals(['create function "test_892788"("p436580" int) returns int language sql return (abs(p436580))'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLanguageSqlExpressionPg13(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') >= 0) {
            $this->markTestSkipped('SQL function bodies are supported with PostgreSQL 14 and will be preferred.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_978064', ['p250541' => 'int'], 'int', 'sql:expression', 'abs(p250541)');
        });
        $this->assertEquals(['create function "test_978064"("p250541" int) returns int language sql as $$ select (abs(p250541)) $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLanguageSqlPg13(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') >= 0) {
            $this->markTestSkipped('SQL function bodies are supported with PostgreSQL 14 and will be preferred.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_283558', ['p406352' => 'int'], 'int', 'sql', 'select abs(p406352)');
        });
        $this->assertEquals(['create function "test_283558"("p406352" int) returns int language sql as $$ select abs(p406352) $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLeakproofFalse(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_189431', ['p716712' => 'int'], 'int', 'plpgsql', 'begin select abs(p716712);end', [
                'leakproof' => false,
            ]);
        });
        $this->assertEquals(['create function "test_189431"("p716712" int) returns int language plpgsql not leakproof as $$ begin select abs(p716712);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionLeakproofTrue(): void
    {
        $isSuperuser = $this->getConnection()->selectOne('SELECT usesuper FROM pg_user wHERE usename = current_user')->usesuper;
        if (!$isSuperuser) {
            $this->markTestSkipped('Only superusers can define a leakproof function.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_308376', ['p228365' => 'int'], 'int', 'plpgsql', 'begin select abs(p228365);end', [
                'leakproof' => true,
            ]);
        });
        $this->assertEquals(['create function "test_308376"("p228365" int) returns int language plpgsql leakproof as $$ begin select abs(p228365);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceCalledOnNullFalse(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_718461', ['p996109' => 'int'], 'int', 'plpgsql', 'begin select abs(p996109);end', [
                'calledOnNull' => false,
            ]);
        });
        $this->assertEquals(['create or replace function "test_718461"("p996109" int) returns int language plpgsql returns null on null input as $$ begin select abs(p996109);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceCalledOnNullTrue(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_538601', ['p400009' => 'int'], 'int', 'plpgsql', 'begin select abs(p400009);end', [
                'calledOnNull' => true,
            ]);
        });
        $this->assertEquals(['create or replace function "test_538601"("p400009" int) returns int language plpgsql called on null input as $$ begin select abs(p400009);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceCost(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_284015', ['p578067' => 'int'], 'int', 'plpgsql', 'begin select abs(p578067);end', [
                'cost' => 100,
            ]);
        });
        $this->assertEquals(['create or replace function "test_284015"("p578067" int) returns int language plpgsql cost 100 as $$ begin select abs(p578067);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceLanguagePlpgsql(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_867585', ['p359360' => 'int'], 'int', 'plpgsql', 'begin select abs(p359360);end');
        });
        $this->assertEquals(['create or replace function "test_867585"("p359360" int) returns int language plpgsql as $$ begin select abs(p359360);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceLanguageSql(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') < 0) {
            $this->markTestSkipped('SQL function bodies are first supported with PostgreSQL 14.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_921804', ['p434093' => 'int'], 'int', 'sql', 'select abs(p434093)');
        });
        $this->assertEquals(['create or replace function "test_921804"("p434093" int) returns int language sql begin atomic; select abs(p434093); end'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceLanguageSqlExpression(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') < 0) {
            $this->markTestSkipped('SQL function bodies are first supported with PostgreSQL 14.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_135707', ['p690173' => 'int'], 'int', 'sql:expression', 'abs(p690173)');
        });
        $this->assertEquals(['create or replace function "test_135707"("p690173" int) returns int language sql return (abs(p690173))'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceLanguageSqlExpressionPg13(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') >= 0) {
            $this->markTestSkipped('SQL function bodies are supported with PostgreSQL 14 and will be preferred.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_626780', ['p149769' => 'int'], 'int', 'sql:expression', 'abs(p149769)');
        });
        $this->assertEquals(['create or replace function "test_626780"("p149769" int) returns int language sql as $$ select (abs(p149769)) $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceLanguageSqlPg13(): void
    {
        if (version_compare($this->getConnection()->serverVersion(), '14') >= 0) {
            $this->markTestSkipped('SQL function bodies are supported with PostgreSQL 14 and will be preferred.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_737995', ['p591006' => 'int'], 'int', 'sql', 'select abs(p591006)');
        });
        $this->assertEquals(['create or replace function "test_737995"("p591006" int) returns int language sql as $$ select abs(p591006) $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceLeakproofFalse(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_650702', ['p556343' => 'int'], 'int', 'plpgsql', 'begin select abs(p556343);end', [
                'leakproof' => false,
            ]);
        });
        $this->assertEquals(['create or replace function "test_650702"("p556343" int) returns int language plpgsql not leakproof as $$ begin select abs(p556343);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceLeakproofTrue(): void
    {
        $isSuperuser = $this->getConnection()->selectOne('SELECT usesuper FROM pg_user wHERE usename = current_user')->usesuper;
        if (!$isSuperuser) {
            $this->markTestSkipped('Only superusers can define a leakproof function.');
        }

        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_163088', ['p349206' => 'int'], 'int', 'plpgsql', 'begin select abs(p349206);end', [
                'leakproof' => true,
            ]);
        });
        $this->assertEquals(['create or replace function "test_163088"("p349206" int) returns int language plpgsql leakproof as $$ begin select abs(p349206);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceParallelRestricted(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_400929', ['p177294' => 'int'], 'int', 'plpgsql', 'begin select abs(p177294);end', [
                'parallel' => 'restricted',
            ]);
        });
        $this->assertEquals(['create or replace function "test_400929"("p177294" int) returns int language plpgsql parallel restricted as $$ begin select abs(p177294);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceParallelSafe(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_654446', ['p895672' => 'int'], 'int', 'plpgsql', 'begin select abs(p895672);end', [
                'parallel' => 'safe',
            ]);
        });
        $this->assertEquals(['create or replace function "test_654446"("p895672" int) returns int language plpgsql parallel safe as $$ begin select abs(p895672);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceParallelUnsafe(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_431469', ['p555860' => 'int'], 'int', 'plpgsql', 'begin select abs(p555860);end', [
                'parallel' => 'unsafe',
            ]);
        });
        $this->assertEquals(['create or replace function "test_431469"("p555860" int) returns int language plpgsql parallel unsafe as $$ begin select abs(p555860);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceSecurityDefiner(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_464123', ['p844489' => 'int'], 'int', 'plpgsql', 'begin select abs(p844489);end', [
                'security' => 'definer',
            ]);
        });
        $this->assertEquals(['create or replace function "test_464123"("p844489" int) returns int language plpgsql security definer as $$ begin select abs(p844489);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionOrReplaceSecurityInvoker(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunctionOrReplace('test_792953', ['p182321' => 'int'], 'int', 'plpgsql', 'begin select abs(p182321);end', [
                'security' => 'invoker',
            ]);
        });
        $this->assertEquals(['create or replace function "test_792953"("p182321" int) returns int language plpgsql security invoker as $$ begin select abs(p182321);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionParallelRestricted(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_122680', ['p5619102' => 'int'], 'int', 'plpgsql', 'begin select abs(p5619102);end', [
                'parallel' => 'restricted',
            ]);
        });
        $this->assertEquals(['create function "test_122680"("p5619102" int) returns int language plpgsql parallel restricted as $$ begin select abs(p5619102);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionParallelSafe(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_332316', ['p696287' => 'int'], 'int', 'plpgsql', 'begin select abs(p696287);end', [
                'parallel' => 'safe',
            ]);
        });
        $this->assertEquals(['create function "test_332316"("p696287" int) returns int language plpgsql parallel safe as $$ begin select abs(p696287);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionParallelUnsafe(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_731603', ['p823743' => 'int'], 'int', 'plpgsql', 'begin select abs(p823743);end', [
                'parallel' => 'unsafe',
            ]);
        });
        $this->assertEquals(['create function "test_731603"("p823743" int) returns int language plpgsql parallel unsafe as $$ begin select abs(p823743);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionSecurityDefiner(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_684890', ['p378312' => 'int'], 'int', 'plpgsql', 'begin select abs(p378312);end', [
                'security' => 'definer',
            ]);
        });
        $this->assertEquals(['create function "test_684890"("p378312" int) returns int language plpgsql security definer as $$ begin select abs(p378312);end $$'], array_column($queries, 'query'));
    }

    public function testCreateFunctionSecurityInvoker(): void
    {
        $queries = $this->withQueryLog(function (): void {
            Schema::createFunction('test_122881', ['p464600' => 'int'], 'int', 'plpgsql', 'begin select abs(p464600);end', [
                'security' => 'invoker',
            ]);
        });
        $this->assertEquals(['create function "test_122881"("p464600" int) returns int language plpgsql security invoker as $$ begin select abs(p464600);end $$'], array_column($queries, 'query'));
    }

    public function testDropFunction(): void
    {
        $this->getConnection()->statement("create function test_151316(integer) returns int as 'select $1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunction('test_151316');
        });
        $this->assertEquals(['drop function "test_151316"'], array_column($queries, 'query'));
    }

    public function testDropFunctionIfExists(): void
    {
        $this->getConnection()->statement("create function test_216089(integer) returns int as 'select $1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunctionIfExists('test_216089');
        });
        $this->assertEquals(['drop function if exists "test_216089"'], array_column($queries, 'query'));
    }

    public function testDropFunctionIfExistsWithArguments(): void
    {
        $this->getConnection()->statement("create function test_675622(integer) returns int as 'select $1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunctionIfExists('test_675622', ['integer']);
        });
        $this->assertEquals(['drop function if exists "test_675622"(integer)'], array_column($queries, 'query'));
    }

    public function testDropFunctionIfExistsWithEmptyArguments(): void
    {
        $this->getConnection()->statement("create function test_780129() returns int as 'select 1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunctionIfExists('test_780129', []);
        });
        $this->assertEquals(['drop function if exists "test_780129"()'], array_column($queries, 'query'));
    }

    public function testDropFunctionWithArguments(): void
    {
        $this->getConnection()->statement("create function test_355700(integer) returns int as 'select $1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunction('test_355700', ['integer']);
        });
        $this->assertEquals(['drop function "test_355700"(integer)'], array_column($queries, 'query'));
    }

    public function testDropFunctionWithEmptyArguments(): void
    {
        $this->getConnection()->statement("create function test_421087() returns int as 'select 1' language sql");
        $queries = $this->withQueryLog(function (): void {
            Schema::dropFunction('test_421087', []);
        });
        $this->assertEquals(['drop function "test_421087"()'], array_column($queries, 'query'));
    }
}
