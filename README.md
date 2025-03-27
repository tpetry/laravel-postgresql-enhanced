![License][icon-license]
![PHP][icon-php]
[![Latest Version on Packagist][icon-version]][href-version]
[![Downloads on Packagist][icon-downloads]][href-downloads]
[![GitHub PHPUnit Action Status][icon-tests]][href-tests]
[![GitHub PHPStan Action Status][icon-phpstantest]][href-phpstantest]
[![GitHub PhpCsFixer Action Status][icon-style]][href-style]

Laravel supports many different databases and therefore has to limit itself to the lowest common denominator of all databases. PostgreSQL, however, offers a ton more functionality which is being added to Laravel by this extension.

# Installation

You can install the package via composer:

```bash
composer require tpetry/laravel-postgresql-enhanced
```

# Versioning Policy

**This package follows the semantic versioning policy.**
**So you can always safely upgrade to a new minor and patch version without any issues.**

As Laravel is a healthy framework that is still adding new database functionality, some things may break:
For instance, in the past, Laravel introduced new builder methods that had already been part of this package for a long time - but with different semantics.
In such cases, a new major version is promptly released with a breaking change to align with Laravel's implementation.

When upgrading Laravel, you may also have to upgrade this package to a new major version when something breaks!
The minimal breaking changes of the past years are listed in the [breaking changes section](#breaking-changes).

# Features

- [IDE Autocomplete](#ide-autocomplete)
- [PHPStan](#phpstan)
- [Migration](#migration)
    - [Zero Downtime Migration](#zero-downtime-migration)
    - [Extensions](#extensions)
    - [Functions](#functions)
    - [Triggers](#triggers)
    - [Views](#views)
        - [Materialized Views](#materialized-views)
    - [Indexes](#indexes)
        - [Concurrently](#concurrently)
        - [Nulls Not Distinct](#nulls-not-distinct)
        - [Partial Indexes](#partial-indexes)
        - [Include Columns](#include-columns)
        - [If Not Exists](#if-not-exists)
        - [Storage Parameters](#storage-parameters-index)
        - [Functional Indexes / Column Options](#functional-indexes--column-options)
        - [Fulltext Indexes](#fulltext-indexes)
    - [Domain Types](#domain-types)
    - [Table Options](#table-options)
        - [Unlogged](#unlogged)
        - [Storage Parameters](#storage-parameters-table)
    - [Column Options](#column-options)
        - [Compression](#compression)
        - [Initial](#initial)
        - [Using](#using)
    - [Column Types](#column-types)
        - [Arrays](#arrays)
        - [Ranges](#ranges)
        - [Bit Strings](#bit-strings)
        - [Case Insensitive Text](#case-insensitive-text)
        - [Full Text Search](#full-text-search)
        - [Hstore](#hstore)
        - [Identity](#identity)
        - [IP Networks](#ip-networks)
        - [International Product Numbers](#international-product-numbers)
        - [Label Tree](#label-tree)
        - [Vector](#vector)
        - [XML](#xml)
- [Query](#query)
    - [Explain](#explain)
    - [Fulltext Search](#fulltext-search)
    - [Lateral Subquery Joins](#lateral-subquery-joins)
    - [Returning Data From Modified Rows](#returning-data-from-modified-rows)
    - [Common Table Expressions (CTE)](#common-table-expressions-cte)
    - [Lazy By Cursor](#lazy-by-cursor)
    - [Where Clauses](#where-clauses)
    - [Order By](#order-by)
- [Eloquent](#eloquent)
    - [Casts](#casts)
    - [Refresh Data on Save](#refresh-data-on-save)
    - [Date Formats](#date-formats)
- [Expressions](#expressions)
- [Supported Extensions](#supported-extensions)
    - [Timescale](#timescale)
## IDE Autocomplete

Laravel provides many extension capabilities, making it hard for IDEs to do proper autocomplete.
Currently, there is no generic way to tell IDEs about the added functionality of this enhanced driver.
However, this package is integrated with specific IDE combinations:

* **PhpStorm with Laravel Idea**: The [IDE Helper for Laravel](https://github.com/barryvdh/laravel-ide-helper) can detect all PostgreSQL features by running the `Generate Helper Code (Eloquent, Maco, Facades, etc.)` action from the `Laravel` -> `Code Generation` toolbar menu.

## PHPStan

This extension is adding a lot of missing PostgreSQL functionality to Laravel.
A custom set of [PHPStan](https://phpstan.org/) extensions have been developed to get full static analysis support!

All features provided this extension are automatically recognized by the [phpstan/extension-installer](https://github.com/phpstan/extension-installer).
Otherwise, you have to manually add the following path to your `includes` list in `phpstan.neon`, your config should now look like this:

```
includes:
    - ./vendor/nunomaduro/larastan/extension.neon
    - ./vendor/tpetry/laravel-postgresql-enhanced/phpstan-extension.neon
```

## Migration

### Zero-Downtime Migration
For applications with 24/7 requirements, migrations must never impact availability.
PostgreSQL provides many functionalities to execute changes on the schema without downtime.
However, sometimes a change to the schema is not tested sufficiently and locks the tables for a longer period of time in order to make the desired change.
To avoid this problem, a migration can be marked as zero-downtime migration.
If the migration exceeds a specified time limit, it is cancelled and the schema is reset to its original state.

```php
use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Schema\Concerns\ZeroDowntimeMigration;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

class Test123 extends Migration
{
    use ZeroDowntimeMigration;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('name', 128)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->string('name', 32)->change();
        });
    }
}
```

The timeout for a maximum time limit of 1.0 second can be set separately for each migration.
You can set `private float $timeout = 5.0` on the migration for a up/down timeout.
Or you can set the specific timeouts `$timeoutUp` and `$timeoutDown` to differentiate between the methods.

### Extensions

#### Create Extensions

The `Schema` facade supports the creation of extensions with the `createExtension` and `createExtensionIfNotExists` methods:
```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::createExtension('tablefunc');
Schema::createExtensionIfNotExists('tablefunc');
```

#### Dropping Extensions

To remove extensions, you may use the `dropExtension` and `dropExtensionIfExists` methods provided by the `Schema` facade:

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::dropExtension('tablefunc');
Schema::dropExtensionIfExists('tablefunc');
```

You may drop many extensions at once by passing multiple extension names:
```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::dropExtension('tablefunc', 'fuzzystrmatch');
Schema::dropExtensionIfExists('tablefunc', 'fuzzystrmatch');
```

### Functions

#### Create Functions

The `Schema` facade supports the creation of functions with the `createFunction` and `createFunctionOrReplace` methods. For the definition of your function you have to provide the name of the function, the parameters, the return type, the function's language and body:

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::createFunction(
  name: 'sales_tax',
  parameters: ['subtotal' => 'numeric'],
  return: 'numeric',
  language: 'plpgsql',
  body: '
    BEGIN
      RETURN subtotal * 0.06;
    END;
  '
);
```

A sixth parameter lets you define further options for the function. Please [read the manual](https://www.postgresql.org/docs/current/sql-createfunction.html) for the exact meaning, some of them set enable or disable ways for PostgreSQL to optimize the execution.

| Option         | Values                            | Description                                                                                                    |
|----------------|-----------------------------------|----------------------------------------------------------------------------------------------------------------|
| `calledOnNull` | bool                              | Defines whether the function should be called for NULL values.                                                 |
| `cost`         | integer                           | Defines the cost for executing the function.                                                                   |
| `leakproof`    | bool                              | Informs whether the function has side effects.                                                                 |
| `parallel`     | `restricted`, `safe`, `unsafe`    | Defines whether the function can be executed in parallel.                                                      |
| `security`     | `definer`, `invoker`              | Defines that the function will be executed with the privileges of the current user or creator of the function. |
| `volatility`   | `immutable`, `stable`, `volatile` | Informs whether the function changes database values.                                                          |

The former example can be optimized by using the special `sql:expression` language identifier created by this driver. The function body can only be one SQL expression, but it will be inlined in the query instead of executed with recent PostgreSQL versions for much better performance: 

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::createFunction('sales_tax', ['subtotal' => 'numeric'], 'numeric', 'sql:expression', 'subtotal * 0.06', [
  'parallel' => 'safe',
  'volatility' => 'immutable',
]);
```

If you want your function to return a table, you have to provide the columns as return type:

```php
Schema::createFunction('search_user', ['pattern' => 'text'], ['id' => 'int', 'email' => 'text'], 'plpgsql', "
  BEGIN
    RETURN QUERY select user_id, contactemail from users where name ilike '%' || pattern || '%';
  END;
");
```

#### Drop Functions

To remove functions, you may use the `dropFunction` and `dropFunctionIfExists` methods provided by the `Schema` facade:

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::dropFunction('sales_tax');
Schema::dropFunctionIfExists('sales_tax');
```

### Triggers

#### Create Triggers

On your `Blueprint` you can add triggers to a table.
You need to pass in a unique name, call of a function you've created before and the action that will fire the trigger:

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('projects', function (Blueprint $table): void {
    $table->trigger('rollup_quota', 'update_quota_by_projects()', 'AFTER INSERT OR DELETE');
});
```

The following table contains all of the available trigger modifiers:

| Modifier                                                                          | Description                                                                                                                                                        |
|-----------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `->forEachRow()`                                                                  | The trigger will be called for every row.                                                                                                                          |
| `->forEachStatement()`                                                            | The trigger will be called once for each statement *(default)*.                                                                                                    |
| `->transitionTables(`<br>`  old: 'oldrows',`<br>`  new: 'newrows',`<br>`)`        | The forEachStatement-trigger will provide the before/after state of the affected rows in special tables. You can omit either option if not valid for this trigger. |
| `->when('NEW.type = 4')`<br>`->when(fn ($query) => $query->where('NEW.type', 4))` | The trigger should only be called when the condition matches *(only with forEachRow)*.                                                                             |
| `->replace(true)`                                                                 | The trigger will replace an existing one defined with the same name.                                                                                               |

> [!NOTE] 
> PostgreSQL always updates rows even if nothing changed, which may affect your performance. You can add the `suppress_redundant_updates_trigger()` trigger with a `BEFORE UPDATE` action to all tables.

#### Drop Triggers

To remove trigger, you may use the `dropTrigger` and `dropTriggerIfExists` methods provided by the table's `Blueprint` class:

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('projects', function (Blueprint $table): void {
    $table->dropTrigger('update_quota');
    $table->dropTriggerIfExists('update_quota');
});
```

### Views

#### Create Views

The `Schema` facade supports the creation of views with the `createView` and `createViewOrReplace` methods. The definition of your view can be a sql query string or a query builder instance:
```php
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::createView('users_with_2fa', 'SELECT * FROM users WHERE two_factor_secret IS NOT NULL');
Schema::createViewOrReplace('users_without_2fa', DB::table('users')->whereNull('two_factor_secret'));
```

You can specify alternative names for the view's columns by passing an array as the last parameter:
```php
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::createView('users_with_2fa', DB::table('users')->select('id')->whereNull('two_factor_secret'), ['user_id']);
```

If you need to create recursive views the `createRecursiveView` and `createRecursiveViewOrReplace` methods can be used like in the former examples but you need to provide the available columns as last parameter:

```php
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

// TODO simple example explaining the concept
Schema::createRecursiveView('viewname', 'SELECT id, col1, col2 FROM ....', ['id', 'col1', 'col2']);
Schema::createRecursiveViewOrReplace('viewname', 'SELECT id, col1, col2 FROM ....', ['id', 'col1', 'col2']);
```

#### Dropping Views

To remove views, you may use the `dropView` and `dropViewIfExists` methods provided by the `Schema` facade. You don't have to distinguish normala and recursive views:

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::dropView('myview');
Schema::dropViewIfExists('myview');
```

You may drop many views at once by passing multiple view names:
```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::dropView('myview1', 'myview2');
Schema::dropViewIfExists('myview1', 'myview2');
```

#### Materialized Views

With materialized views you can populate a view with the contents of a query's results at the time the query is executed.
You can use them to cache expensive queries so they are not re-run all the time.

Materialized views are created (and dropped) the same as normal views.
You can either pass in a query builder or raw sql query.
A useful method to create materialized views for very slow queries is to create them without any data initially.
By passing the `withData: false` parameter the materialized view is created instantly and no data is stored, you need to refresh it later to contain some data.

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::createMaterializedView('users_with_2fa', 'SELECT * FROM users WHERE two_factor_secret IS NOT NULL');
Schema::createMaterializedView('users_with_2fa', DB::table('users')->whereNull('two_factor_secret'));
Schema::createMaterializedView('users_with_2fa', DB::table('users')->select('id')->whereNull('two_factor_secret'), columns: ['user_id']);

Schema::createMaterializedView('very_slow_query_materialized', 'SELECT ...', withData: false);

Schema::dropMaterializedView('users_with_2fa');
Schema::dropMaterializedViewIfExists('users_with_2fa');
```

The stored values of a created materialized view can be refreshed whenever you want to.
When passing the `concurrently: true` parameter the command will finish instantly and PostgreSQL will refresh the values in the background.
You can also change the materialized views behaviour to (not) contain any data anymore with the `withData: true` and `withData: false` parameter.

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::refreshMaterializedView('users_with_2fa');
Schema::refreshMaterializedView('users_with_2fa', concurrently: true);
Schema::refreshMaterializedView('users_with_2fa', withData: false);
Schema::refreshMaterializedView('users_with_2fa', withData: true);
```

### Indexes

#### Unique Indexes
Laravel provides uniqueness with the `$table->unique()` method but these are unique constraints instead of unique indexes.
If you want to make values unique in the table they will behave identical.
However, only for unique indexes advanced options like partial indexes, including further columns or column options are available.

To use these great features and not break compatibility with Laravel the method `uniqueIndex` has been added which can be used identical to `unique`:
```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('users', function(Blueprint $table) {
    $table->uniqueIndex('email');
});
```

#### Drop If Exists

In addition to the Laravel methods to drop indexes, methods to drop indexes if they exist have been added.
The methods `dropFullTextIfExists`, `dropIndexIfExists`, `dropPrimaryIfExists`, `dropSpatialIndexIfExists` and `dropSpatialIndexIfExists` match the semantics of their laravel originals.

#### Concurrently

With PostgreSQL, you can say goodbye to half-executed migrations on errors and the tedious effort to restore the database to a stable state.
This is all thanks to its transactional approach: either all changes of a migration to your database will succeed or will be rolled back.
Yay!
Because of that, creating an index on a big table will take a long time and block all SQL queries during that time.
You can now instruct PostgreSQL to create the index in the background without blocking any SQL query, but you must opt out of running those changes in a transaction.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('blog_visits', function (Blueprint $table) {
            $table->index(['url', 'ip_address'])->concurrently();
        });
    }
};
```

#### Nulls Not Distinct

NULL values in unique indexes are handled in a non-comprehensible way for most developers.
When you e.g. save active subscriptions, you want to limit every user to have only one active subscription by e.g. creating a unique index on `(user_id, cancelled_at)`.
But as active subscriptions don't have a `cancelled_at` timestamp, multiple rows can be entered with the same `user_id` and a `NULL` value for `cancelled_at` because two `NULL` values are never the same.
But with PostgreSQL 15 you can now instruct the database not to allow those duplicate rows by handling `NULL` values as not distinct:

```php
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::create('subscriptions', function(Blueprint $table) {
    $table->id('user_id');
    $table->timestampTz('cancelled_at');


    $table->uniqueIndex(['user_id', 'cancelled_at'])->nullsNotDistinct();
});
```

> [!NOTE] 
> For this example you could also use a unique partial index on `user_id` with limiting the rows to `cancelled_at IS NOT NULL`.

#### Partial Indexes

A partial index is an index built over a subset of a table; the subset is defined by a condition. The index contains entries only for those table rows that satisfy the condition. Partial indexes are a specialized feature, but there are several situations in which they are useful.
Take for example you want to make the email address column of your users table unique and you are using soft-deletes. This is not possible because by deleting a user and creating it again the email address is used twice. With partial indexes this can be done by limiting the index to only untrashed rows:
```php
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('users', function(Blueprint $table) {
    $table->uniqueIndex('email')->where("deleted_at IS NULL");
    // or:
    $table->uniqueIndex('email')->where(fn (Builder $condition) => $condition->whereNull('deleted_at'));
});
```

Partial Indexes are created with the `where` method on an index created by `fullText()`, `index()`, `spatialIndex()` or `uniqueIndex()`.

> [!TIP]
> The `upsert()` method will not work with partial indexes because the condition needs to also be applied to the upsert.
> You can use the `upsertPartial()` method for this:
> ```php
> User::upsertPartial($users, ['email'], ['name', 'subscriptions'], 'deleted_at is null');
> User::upsertPartial($users, ['email'], ['name', 'subscriptions'], fn($query) => $query->whereNull('deleted_at'));
> ```

#### Include Columns

A really great feature of recent PostgreSQL versions is the ability to include columns in an index as non-key columns.
A non-key column is not used for efficient lookups but PostgreSQL can use these columns to do index-only operations which won't need to load the specific columns from the table as they are already included in the index.

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('users', function(Blueprint $table) {
    // The query "SELECT firstname, lastname FROM users WHERE email = 'test@example.com'" can be executed as an index-only scan without loading the table data
    $table->index('email')->include(['firstname', 'lastname']);
});
```
Columns are included in an index with the `include` method on an index created by `index()`, `spatialIndex` or `uniqueIndex`.

#### If Not Exists

Sometimes, you fix performance issues by testing new indexes on the production database rather than pushing a new migration each time.
But when you find the perfect one, you should also make a migration for it.
Dropping the existing index to recreate it by the migration is silly.
Right?
You can now skip the index creation from the migration when the exact index already exists.

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('invoices', function(Blueprint $table) {
    $table->index(['target', 'division', 'date'])->ifNotExists();
});
```

> [!TIP]
> Indexes are determined to be identical by their (automatically-generated) name.
> Either create the index statement to run in production by temporary migrations from your development machine or use specific index names.

#### Storage Parameters (Index)

In some cases you want to specify the storage parameters of an index. If you are using gin indexes you should read the article [Debugging random slow writes in PostgreSQL](https://iamsafts.com/posts/postgres-gin-performance/) why storage parameters for a gin index are important:

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('bookmarks', function(Blueprint $table) {
    $table->index('data')->algorithm('gin')->with(['fastupdate' => false]);
});
```
Storage parameters are defined with the `with` method on an index created by `fullText()`, `index()`, `spatialIndex` or `uniqueIndex`.

#### Functional Indexes / Column Options

Sometimes an index with only column specifications is not sufficient. For maximum performance, the extended index functionalities of PostgreSQL has to be used in some cases.

* To create functional indexes the function must be bracketed and a separate index name must be specified, since an index name cannot be generated automatically from the expression.
* Column specific properties like collation, opclass, sorting or positioning of NULL values can easily be specified like in a normal SQL query directly after the column name.

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('users', function(Blueprint $table) {
    $table->uniqueIndex('(LOWER(email))', 'users_email_unique');
    $table->index(['firstname ASC NULLS FIRST', 'lastname ASC NULLS FIRST']);
    $table->index('attributes jsonb_path_ops')->algorithm('gin');
});
```

#### Fulltext Indexes

Fulltext-search in PostgreSQL is language-dependent: For better results all words are [stemmed](https://en.wikipedia.org/wiki/Stemming) to their root form.
Laravel is using the `english` language by default, for your application you may have to use a different one.
You can also use the generic `simple` language which is not doing any stemming, but your search term will then have to include the exact string to match the record.

Additionally, you can specify a relative weight for each column of the index to control the ranking.
In this example the `title` column is a more relevant information than the `description` column, so it's relative weight has been set more important (`A` precedes `B`).

For more information on all the options for fulltext-search read this article: [Fine Tuning Full Text Search with PostgreSQL 12](https://rob.conery.io/2019/10/29/fine-tuning-full-text-search-with-postgresql-12/).

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('book', function (Blueprint $table) {
    $table->fullText(['title', 'description'])
        ->language('spanish')
        ->weight(['A', 'B']);
});
```

### Domain Types

A relational database like PostgreSQL provides a lot of data types you can choose from.
But they are only generic types that should match thousands of applications.
Your specific requirements are not covered.
But with domain types, you can add application-specific types like a price column that has a specific amount of digits and is never negative:
An existing base type (`numeric(9,2)`) is aliased to a new type with an optional condition that all values have to match.
You can use that to create repeatable price columns in your tables or create completely new types like a license plate type that has to match a specific format.

#### Create A Domain Type

The Schema facade supports the creation of domain types with the `createDomain` method by passing the name, the base type and an optional SQL condition to validate any value.

```php
use Tpetry\PostgresqlEnhanced\Query\Builder;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::createDomain('price', 'numeric(9,2)');
Schema::createDomain('price', 'numeric(9,2)', 'VALUE >= 0');
Schema::createDomain('price', 'numeric(9,2)', fn (Builder $query) => $query->where('VALUE', '>=', 0));
```

#### Use Domain Types

Your created domain types can be used in a migration like every other column by using the `domain` column type and using the column name and domain type name:

```php
Schema::create('products', function (Blueprint $table): void {
  $table->id();
  $table->string('item_name');
  $table->domain('item_price', 'price');
  $table->timestampsTz();
});
```

> [!NOTE]
> You can also utilize the domain type to use e.g. column types added by extensions or not yet supported by the package.

#### Altering Domain Types

The base type of a domain type can't be changed after it has been created.
But you can change the condition to validate the values by removing it or replacing it with a new one:

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

// To drop the validation condition:
Schema::changeDomainConstraint('price', null);

// To change validation condition:
Schema::changeDomainConstraint('price', 'VALUE > 0');
Schema::changeDomainConstraint('price', fn (Builder $query) => $query->where('VALUE', '>', 0));
```

#### Dropping Domain Types

To remove domain types, you have first to drop all column using them (or change their type) and then use `dropDomain` or `dropDomainIfExists` provided by the Schema facade:

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::dropDomain('price');
Schema::dropDomainIfExists('price');
```

You may drop many domain types at once by passing multiple domain names:

```php
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::dropDomain('price', 'license_plate');
Schema::dropDomainIfExists('price', 'license_plate');
```

### Table Options

#### Unlogged

You can mark high-write load tables as unlogged if losing that data is not an issue and you want a big speed boost for write operations.
Unlogged tables are written to disk by PostgreSQL but some durability requirements to be crash-safe are skipped.
They behave like every other table and keep their data on a clean shutdown while on a server crash all data is lost.
This is a perfect option for temporary data which you are okay with to lose like e.g. sessions as every user can just login again.
You can activate and deactivate the unlogged table mode with the `unlogged` method on the table blueprint.
```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('sessions', function (Blueprint $table): void {
    // make the table unlogged
    $table->unlogged();
    
    // make the table crash-safe again
    $table->unlogged(false);
});
```

#### Storage Parameters (Table)

With storage parameters, you can fine-tune tables to your application requirements and it's specific workload.
Storage parameters and options you may want to change:

* `fillfactor` for faster UPDATE-s: [HOT updates for better performance](https://www.cybertec-postgresql.com/en/hot-updates-in-postgresql-for-better-performance/)
* `autovacuum_analyze_scale_factor` for tables with millions of rows: [Explained configuration value](https://postgresqlco.nf/doc/en/param/autovacuum_analyze_scale_factor/), [Table Maintenance after Bulk Modifications](https://sqlfordevs.com/table-maintenance-bulk-modification)

You can find more suggestions for specific workloads in [tuning autovacuum](https://www.cybertec-postgresql.com/en/tuning-autovacuum-postgresql/) guide.

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('sessions', function (Blueprint $table): void {
    $table->with([
        // Tune statistics generation for tables with millions of records
        'autovacuum_analyze_scale_factor' => 0.02,
        // Tune table for frequent UPDATE statements
        'fillfactor' => 90,
    ]);
});
```

### Column Options
#### Compression
PostgreSQL 14 introduced the possibility to specify the compression method for toast-able data types.
You can choose between the default method `pglz`, the recently added `lz4` algorithm and the value `default` to use the server default setting.
```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('books', function (Blueprint $table): void {
    // @see https://www.postgresql.org/docs/current/storage-toast.html
    $table->string('summary')->compression('lz4');
});
```

#### Initial

Sometimes a new column needs to be added and all existing rows should get an initial value.
With the `initial` modifier, you can assign a value to all present rows while all new ones will have no default value or a different one.

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('users', function (Blueprint $table): void {
    $table->boolean('acl_admin')->initial(false);
    $table->boolean('acl_read')->initial(false)->default(true);
});
```

#### Using

PostgreSQL forbids some data type changes in migrations when they violate the type system.
You can't, e.g., change a `varchar` column storing one email address into a `jsonb` array storing multiple email addresses, as PostgreSQL doesn't know how to convert between these types automatically.
You would get this error:

```
SQLSTATE[42804]: Datatype mismatch: 7 ERROR:  column "email" cannot be cast automatically to type jsonb
```

You can specify an expression how the current value has to be transformed to the new type with the `using()` modifier:

```php
Schema::table('users', function (Blueprint $table): void {
    $table->jsonb('email')->using('jsonb_build_array(email)')->change();
});
```

### Column Types

#### Arrays
The array data types store multiple values in one single column. They can be used e.g. to store multiple tag ids of categories a product belongs to.
```php
// @see https://www.postgresql.org/docs/current/arrays.html
$table->integerArray(string $column);
```

> [!NOTE]
> While PostgreSQL array types are powerful, only the integer array is supported.
> It is the sole array type with additional PostgreSQL enhancements for manipulation and querying compared to JSON columns.
> The [intarray](https://www.postgresql.org/docs/current/intarray.html) extensions provides extensive features that can be used to e.g. [store and query tags](https://tapoueh.org/blog/2013/10/denormalizing-tags/) with advanced boolean logic.

#### Ranges
The range data types store a range of values with optional start and end values. They can be used e.g. to describe the duration a meeting room is booked.
```php
// @see https://www.postgresql.org/docs/current/rangetypes.html
$table->bigIntegerRange(string $column);
$table->bigIntegerMultiRange(string $column);
$table->dateRange(string $column);
$table->dateMultiRange(string $column);
$table->decimalRange(string $column);
$table->decimalMultiRange(string $column);
$table->integerRange(string $column);
$table->integerMultiRange(string $column);
$table->timestampRange(string $column);
$table->timestampMultiRange(string $column);
$table->timestampTzRange(string $column);
$table->timestampTzMultiRange(string $column);
```

#### Bit Strings
The bit string data types store strings of 0s and 1s. They can be used to e.g. store bitmaps.
```php
// @see https://www.postgresql.org/docs/current/datatype-bit.html
$table->bit(string $column, int $length = 1);
$table->varbit(string $column, ?int $length = null);
```

#### Case Insensitive Text
The case insensitive text type is used to store a text that will be compared case insensitive. It can be used to e.g. store and compare e-mail addresses.
```php
// @see https://www.postgresql.org/docs/current/citext.html
$table->caseInsensitiveText(string $column);
```

> [!NOTE]
> You need to enable the `citext` extension with  `Schema::createExtension('citext')` or `Schema::createExtensionIfNotExists('citext')` before.

#### Full Text Search
The tsvector type is used to store a processed dictionary for full text searching.
```php
// @see https://www.postgresql.org/docs/10/datatype-textsearch.html
$table->tsvector(string $column);
```

#### IP Networks
The ip network datatype stores an ip network in cidr notation.
```php
// @see https://www.postgresql.org/docs/current/datatype-net-types.html
$table->ipNetwork(string $column);
```

#### Hstore
The hstore data type is used store key/value pairs within a single PostgreSQL value. The new json data type is better in all aspects, so hstore should only be used for compatibility with old applications.
```php
// @see https://www.postgresql.org/docs/current/hstore.html
$table->hstore(string $column);
```

> [!NOTE]
> You need to enable the `hstore` extension with `Schema::createExtensionIfNotExists('hstore')` or `Schema::createExtension('hstore')` before.

#### Identity
The identity data type is the new PostgreSQL standard for automatic generated values. You can even specify whether the database should be the only one generating them (`always = true`) preventing accidental overwrites.
They are used to define primary keys managed by the database or any other kind of automatically generated identification that needs to be unique.
```php
$table->identity(always: true)->primary();
$table->identity('uniqid');
```

#### International Product Numbers
The international product number data types are used to store common product numbers types and validate them before saving.
```php
// @see https://www.postgresql.org/docs/current/isn.html
$table->europeanArticleNumber13(string $column);
$table->internationalStandardBookNumber(string $column);
$table->internationalStandardBookNumber13(string $column);
$table->internationalStandardMusicNumber(string $column);
$table->internationalStandardMusicNumber13(string $column);
$table->internationalStandardSerialNumber(string $column);
$table->internationalStandardSerialNumber13(string $column);
$table->universalProductNumber(string $column);
```

> [!NOTE]
> You need to enable the `isn` extension with `Schema::createExtensionIfNotExists('isn')` or `Schema::createExtension('isn')` before.

#### Label Tree
The ltree data type stores a label as its position in a tree. This provides an easy way to manage a tree without performance and complexity disadvantages compared to alternative solutions.
```php
// @see https://www.postgresql.org/docs/current/ltree.html
$table->labelTree(string $column);
```

> [!NOTE]
> You need to enable the `ltree` extension with `Schema::createExtensionIfNotExists('ltree')` or `Schema::createExtension('ltree')` before.

#### Vector
The vector type can be used to store and search for embeddings created by AI providers like OpenAI.
```php
// @see https://github.com/pgvector/pgvector
$table->vector(string $column, int $dimensions = 1536);
```

> [!NOTE]
> You need to enable the `vector` extension with `Schema::createExtensionIfNotExists('vector')` or `Schema::createExtension('vector')` before.

> [!NOTE]
> The `vector` extension is not a standard PostgreSQL extension but available with most PostgreSQL cloud services.
> You can check for support with the following query: `SELECT * FROM pg_available_extensions WHERE name = 'vector'`

#### XML
The xml data type can be used to store an xml document.
```php
// @see https://www.postgresql.org/docs/current/datatype-xml.html
$table->xml(string $column);
```

## Query


### Explain
Laravel has the ability to get the database query plan for any query you are building. Just calling `explain()` on the query will get a collection with the query plan.

This behaviour has been extended to be more PostgreSQL specific. There are multiple (optional) parameters for the [explain statement](https://www.postgresql.org/docs/current/sql-explain.html), different for every version. The enhanced PostgreSQL driver will automatically activate all options available for your PostgreSQL version.

```php
DB::table('migrations')->where('batch', 1)->explain()->dd();

// Output:
// array:1 [
//  0 => """
//    Seq Scan on public.migrations  (cost=0.00..11.75 rows=1 width=524)\n
//      Output: id, migration, batch\n
//      Filter: (migrations.batch = 1)\n
//    Settings: search_path = 'public'\n
//    Planning Time: 0.370 ms
//    """
//]
```

Additionally, you can also get the query plan with executing the query. The query plan will be extended by valuable runtime information like per-operation timing and buffer read/write statistics:

```php
DB::table('migrations')->where('batch', 1)->explain(analyze:true)->dd();

// Output:
// array:1 [
//  0 => """
//    Seq Scan on public.migrations  (cost=0.00..11.75 rows=1 width=524) (actual time=0.014..0.031 rows=1 loops=1)\n
//      Output: id, migration, batch\n
//      Filter: (migrations.batch = 1)\n
//      Buffers: shared hit=1\n
//    Settings: search_path = 'public'\n
//    Planning:\n
//      Buffers: shared hit=61\n
//    Planning Time: 0.282 ms\n
//    Execution Time: 0.100 ms
//    """
//]
```

### Fulltext Search

The PostgreSQL fulltext search implementation supports a lot of knobs to fine tune the search quality of your fulltext-search.
In it's most basic form you are specifying the columns to search on and the search term to use:

```php
Book::whereFullText(['title', 'description'], 'PostgreSQL')->get();
```

But the implementation does provide a lot more functionality hidden in the third optional parameter.
For more information on all the options for fulltext-search read this article: [Fine Tuning Full Text Search with PostgreSQL 12](https://rob.conery.io/2019/10/29/fine-tuning-full-text-search-with-postgresql-12/).

#### Language

By default the columns and the search term are [stemmed](https://en.wikipedia.org/wiki/Stemming) to it's root form in the `english` language to also find results for e.g. singular or plural words.
If your application is using a different language you can change it to e.g. `spanish` or use the `simple` language which is not doing any stemming. 
```php
Book::whereFullText(['title', 'description'], 'PostgreSQL', ['language' => 'spanish'])->get();
```

#### Search Mode

You can choose from three different search modes for the fulltext-search which is defaulting to the `plainto` mode.
Depending on your requirements a search term can be handled completely different giving you a large amount of freedom to tune fulltext search for your needs.

* `plainto`: All words in the search-term have to exist at least once in the columns.
    ```php
    Book::whereFullText(['title', 'description'], 'PostgreSQL', ['mode' => 'plain'])->get();
    ```
* `phrase`: All words in the search-term have to appear in the exact same order in the columns.
    ```php
    Book::whereFullText(['title', 'description'], 'PostgreSQL database', ['mode' => 'phrase'])->get();
    ```
* `websearch`: Complex search-term which supports quoting values, the `or` keyword and `-` to exclude a word but is missing parentheses.
    ```php
    Book::whereFullText(['title', 'description'], '"PostgreSQL database" -MySQL', ['mode' => 'websearch'])->get();
    ```

#### Weighting

When you want to rank your fulltext-search results you need a way declare some columns more relevant than others.
With the weight option you set a relevance for every search column starting with an `A` and ending with a `Z`.
If you want to you can use the same relative weight multiple times to make some columns equal important.

```php
Book::whereFullText(['title', 'description'], '"PostgreSQL', ['weight' => ['A', 'B']])->get();
```

### Lateral Subquery Joins

PostgreSQL does support the advanced lateral subquery joins.
The simplest explanation is that you can access tables and their columns previously selected from, making it a dependent subquery. You will now be able to do joins equivalent to a foreach-loop in php which will offer a whole new set of possibilities.

This is a very advanced construct, you can read more about them in these articles:
- [PostgreSQL's Powerful New Join Type: LATERAL (heap.io)](https://heap.io/blog/postgresqls-powerful-new-join-type-lateral)
- [UNDERSTANDING LATERAL JOINS IN POSTGRESQL(cybertec-postgresql.com)](https://www.cybertec-postgresql.com/en/understanding-lateral-joins-in-postgresql/)
- [Iterators in PostgreSQL with Lateral Joins (crunchydata.com)](https://blog.crunchydata.com/blog/iterators-in-postgresql-with-lateral-joins)

They are used exactly like their Laravel counterparts but you are now using `crossJoinSubLateral` instead of `crossJoinSub`, `joinSubLateral` instead of `joinSub` and `leftJoinSubLateral` instead of `leftJoinSubLateral`.

A very common is case to use lateral subqueries in a for-each loop concept to e.g. get only the 3 orders with the highest price for every user: 
```php
User::select('users.email', 'orders.*')
    ->leftJoinSubLateral(
        Order::whereColumn('orders.user_id', 'users.id')
            ->orderBy('price', 'desc')
            ->limit(3),
        'orders',
    );
```

### Returning Data From Modified Rows

Sometimes it is more useful to get the affected rows data of a `INSERT`, `UPDATE`, or `DELETE` query instead of just the number of affected rows.
The PostgreSQL [RETURNING](https://www.postgresql.org/docs/current/dml-returning.html) feature changes the behaviour of data manipulation statements to `SELECT` the row's data after the manipulation.

You can use `RETURNING` when you e.g. want to get a list of users you need to update.
Instead of selecting all the users into memory, iterating over them and manipulating each one you can run the manipulation statement directly and get all the affected rows data.
A typical example is reporting which old users have been deleted:
```php
use Illuminate\Support\Facades\DB;

$inactiveUsers = DB::table('users')
    ->where('lastlogin_at', '<', now()->subYear())
    ->get();
foreach ($inactiveUsers as $inactiveUser) {
  $inactiveUser->delete();
}
dump('deleted Users', $inactiveUsers);

// do this instead:

$inactiveUsers = DB::table('users')
    ->where('lastlogin_at', '<', now()->subYear())
    ->deleteReturning();
dump('deleted Users', $inactiveUsers);
```

The following modification queries have been added (analog to their Laravel implementation) which are returning the affected rows data instead of just the number of affected rows:

* `deleteReturning`
* `insertOrIgnoreReturning`
* `insertReturning`
* `insertUsingReturning`
* `updateFromReturning`
* `updateOrInsertReturning`
* `updateReturning`
* `upsertReturning`

### Common Table Expressions (CTE)

You can use Common Table Expressions or CTEs for all select, insert, update and delete methods to write auxiliary statements for use in a larger query.
The `withExpression` method needs to be passed an alias for the CTE, a query string or object and an optional array of options for more control on the CTE.

```php
$query->withExpression($as, $query, $options = []);

$lastLoginQuery = Login::query()
    ->selectRaw('user_id, MAX(created_at) AS last_login_at')
    ->groupBy('user_id');
User::query()
    ->withExpression('users_lastlogin', $lastLoginQuery)
    ->join('users_lastlogin', 'users_lastlogin.user_id', 'users.id')
    ->where('users_lastlogin.created_at', '>=', now()->subHour());
```

In addition to the basic form of a Common Table Expression these optional settings are available to support all PostgreSQL options:

| Option       | Type     | Description                                                                                                                                                                                              |
|--------------|----------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| materialized | `bool`   | Whether the CTE should be (not) materialized. This overrides PostgreSQL's automatic materialization decision. [(Documentation)](https://www.postgresql.org/docs/current/queries-with.html#id-1.5.6.12.7) |
| recursive    | `bool`   | Whether to use a recursive CTE. [(Documentation)](https://www.postgresql.org/docs/current/queries-with.html#QUERIES-WITH-RECURSIVE)                                                                      |
| cycle        | `string` | Specify the automatic cycle detection settings for recursive queries. [(Documentation)](https://www.postgresql.org/docs/current/queries-with.html#QUERIES-WITH-CYCLE)                                    |
| search       | `string` | Specify the tree search mode setting for recursive queries. [(Documentation)](https://www.postgresql.org/docs/current/queries-with.html#QUERIES-WITH-SEARCH)                                             |

> [!NOTE]
> When you are using recursive CTEs **always** use the `cycle` option to prevent infinite running queries because of loops in the data.

### Lazy By Cursor

If you need to iterate over a large amount rows your memory may most probably not big enough.
For these operations Laravel provides the `lazy()` method which is repeatedly using offset pagination which is getting slower and slower with increasing offsets.
Or you can use the more efficient `lazyById` which is using the primary key to paginate the data which is much more efficient but still needs to execute the same query many times.

In PostgreSQL you can do all of this a lot more efficient by using cursors:
The query is executed once and the application can request more rows whenever it wants to so it hasn't to copy everything into memory all at once.

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function() {
    User::lazyByCursor()->each(function (User $user) {
        dump($user);
    });

    // Maximum 500 rows should be loaded into memory for every chunk.
    User::lazyByCursor(500)->each(function (User $user) {
        dump($user);
    });

    // Lazy loading rows also works for the query builder.
    DB::table('users')->where('active', true)->lazyByCursor()->each(function (object $user) {
        dump($user);
    });
});
```

### Where Clauses

#### Any/All

PostgreSQL provides very nifty filtering functions to check a column against multiple values without writing many `AND` or `OR` conditions.
You can say that at least one of the values needs to match the operator with the' ANY' keyword.
While for the `ALL` keyword, all values have to match.

```php
// instead of:
$query->where('invoice', 'like', 'RV-%')->orWhere('invoice', 'like', 'RZ-%');
$query->where('json', '??', 'key1')->where('json', '??', 'key2');

// you can do:
$query->whereAnyValue('invoice', 'like', ['RV-%', 'RZ-%']);
$query->whereAllValues('json', '??', ['key1', 'key2']);
```

```php
$query->whereAllValues($column, string $operator, iterable $values);
$query->whereNotAllValues($column, string $operator, iterable $values);
$query->orWhereAllValues($column, string $operator, iterable $values);
$query->orWhereNotAllValues($column, string $operator, iterable $values)
$query->whereAnyValue($column, string $operator, iterable $values);
$query->whereNotAnyValue($column, string $operator, iterable $values);
$query->orWhereAnyValue($column, string $operator, iterable $values);
$query->orWhereNotAnyValue($column, string $operator, iterable $values)
```

> [!CAUTION]
> The suffixes `Value` and `Values` had to be added to the method names since release 0.36.0 because the Laravel query builder also started to use these method names.

#### Boolean

As Laravel always casts boolean values to integers you will get a PostgreSQL errors like `operator does not exist: boolean = integer` sometimes.
In most cases PostgreSQL is intelligent enough to cast the value but when e.g. creating partial indexes you will get the error.
To resolve that problem you can use the special `whereBoolean` functions that do not cast a boolean to `0` or `1`.

```php
$query->whereBoolean($column, bool $value);
$query->whereNotBoolean($column, bool $value);
$query->orWhereBoolean($column, bool $value);
$query->orWhereNotBoolean($column, bool $value);
```

#### Like

With the `whereLike` scope you can do case-(in)sensitive like comparisons between a column and a value. 

```php
$query->whereLike($column, $value, $caseSensitive = false);
$query->orWhereLike($column, $value, $caseSensitive = false);
```

#### Between Symmetric

Laravel already provides a `whereBetween` clause, but you have to provide the values in sorted order that the smaller value is the first and the bigger one the second array item (`[4, 80]`).
With PostgreSQL's `BETWEEN SYMMETRIC` keyword you don't have to do this anymore, it will automatically reorder the values.

You can now use e.g. min/max values with the following code without having to reorder these values if the meaning has been swapped by the user when entering them:
```php
$min = $request->integer('min');
$max = $request->integer('max');

// before:
$query->whereBetween('price', [min($min, $max), max($min, $max)]);

// now:
$query->whereBetweenSymmetric('price', [$min, $max]);
```

```php
$query->whereBetweenSymmetric($column, iterable $values);
$query->whereNotBetweenSymmetric($column, iterable $values);
$query->orWhereBetweenSymmetric($column, iterable $values);
$query->orWhereNotBetweenSymmetric($column, iterable $values);
```

#### Integer Array Matches

With [arrays](#arrays), you can store a collection of integers like with JSON columns.
But in contrast to JSON columns, those collections can be queried with the [intarray extension](https://www.postgresql.org/docs/current/intarray.html) by complex rules including the operators `&` (AND), `|` (OR), and `!` (NOT).
Parentheses can be used as needed.
For example, the query `1&(2|3)` matches integer arrays that contain 1 and also include either 2 or 3.

```php
$query->whereIntegerArrayMatches($column, string $query);
$query->orWhereIntegerArrayMatches($column, string $query);

// The tags column should have values 3, 4, 5 or 6 and not 7.
$query->whereIntegerArrayMatches('tags', '3&4&(5|6)&!7');
```

### Order By

#### NULLS FIRST/LAST

By default, `NULL` values are sorted before everything in descending order and after everything in ascending order.
This may not be your preferred way of ordering when e.g. displaying in a table to users.
With the nulls first/last option, you can specify the exact behaviour you want:

```php
$query->orderBy($column, string $direction = 'asc'|'desc', string $nulls = 'default'|'first'|'last');
$query->orderByNullsFirst($column, string $direction = 'asc'|'desc', string $nulls = 'default'|'first'|'last');
$query->orderByNullsLast($column, string $direction = 'asc'|'desc', string $nulls = 'default'|'first'|'last');

// Sort the table by the age descending with all NULL values presented last.
$query->orderBy('age', 'desc', nulls: 'last');
$query->orderByNullsLast('age', 'desc');
```

> [!WARNING]
> You have to create a matching index when using a non-default sorting order - a standard one does not work!
> The exact index `$table->index('age DESC NULLS LAST')` matches the query or `$table->index('age NULLS FIRT')` because of the default ascending column order.

#### Vector Similarity

With the `orderByVectorSimilarity` method you can compare a column storing embeddings to other embeddings.

```php
$query->orderByVectorSimilarity($column, $vector, string $distance = 'cosine'|'l2');

// The five rows with the highest similarity to the provided embeddings.
$query->orderByVectorSimilarity('embeddings', [0.9569, 0.1113, 0.0107])->limit(5);
```

## Eloquent

### Casts

Some of the PostgreSQL types are represented in a string format that a Laravel application can't use natively.
To make those types usable, these casts can be used with your eloquent models:

| Type           | Cast                                                        |
|----------------|-------------------------------------------------------------|
| `integerArray` | `Tpetry\PostgresqlEnhanced\Eloquent\Casts\IntegerArrayCast` |
| `vector`       | `Tpetry\PostgresqlEnhanced\Eloquent\Casts\VectorArray`      |

Additionally, these casts exist to make using PostgreSQL more easy:

| Cast                                                                   | Description                                                                                            |
|------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------|
| `Tpetry\PostgresqlEnhanced\Eloquent\Casts\JsonForceEmptyObjectAsArray` | Decodes JSON value as array in Laravel but ensures that empty values are always stored as JSON object. |

### Refresh Data on Save

When you are using Laravel's `storedAs($expression)` feature in migrations to have dynamically computed columns in your database or triggers to update these columns, eloquent's behaviour is not doing exactly what you are expecting.
After you saved the model these computed properties are not available in your model, you need to refresh it because Laravel is only updating the primary key.

```php
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::create('example', function (Blueprint $table) {
    $table->id();
    $table->string('text');
    $table->string('text_uppercase')->storedAs('UPPER(text)');
});

$example = Example::create(['text' => 'test']);
dump($example); // ['id' => 1, 'text' => 'test']

$example->refresh();
dump($example); // ['id' => 1, 'text' => 'test', 'text_uppercase' => 'TEST']

$example->fill(['text' => 'test2'])->save();
dump($example); // ['id' => 1, 'text' => 'test2']

$example->refresh();
dump($example); // ['id' => 1, 'text' => 'test', 'text_uppercase' => 'TEST2']
```

By using the new `RefreshDataOnSave` trait the new [RETURNING statements](#returning-data-from-modified-rows) are used for saving models. Whenever Laravel saves a model any changes in the rows are automatically reflected in your model:

```php
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Concerns\RefreshDataOnSave;

class Example extends Model
{
    use RefreshDataOnSave;

    // ...
}

$example = Example::create(['text' => 'test']);
dump($example); // ['id' => 1, 'text' => 'test', 'text_uppercase' => 'TEST']

$example->fill(['text' => 'test2'])->save();
dump($example); // ['id' => 1, 'text' => 'test2', 'text_uppercase' => 'TES2T']
```

### Date Formats

Laravel migrations support more dates than the standard `Y-m-d H:i:s` format:
You can use the improved `timestampTz` date format that correctly handles the time zone or opt-in to save milliseconds if you want to.
However, standard eloquent models do not work flawless with those extended formats until you change the `$dateFormat` of your models.
But when you mix different date types in a table, you can run into different problems.
Two new traits have been added to solve this:


The new `AutomaticDateFormat` trait should be used when your table has `->timestampTz()` columns:

```php
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Concerns\AutomaticDateFormat;

class Example extends Model
{
    use AutomaticDateFormat;

    // ...
}
```

The new `AutomaticDateFormatWithMilliseconds` trait should be used when you also store milliseconds for some of the `->timestamp()` or `->timestampTz()` columns:

```php
use Illuminate\Database\Eloquent\Model;
use Tpetry\PostgresqlEnhanced\Eloquent\Concerns\AutomaticDateFormatWithMilliseconds;

class Example extends Model
{
    use AutomaticDateFormatWithMilliseconds;

    // ...
}
```

> [!CAUTION]
> When you mix columns with and without milliseconds in a table, the columns without milliseconds may behave unexpectedly to you:
> Instead of truncating the milliseconds, they are rounded by PostgreSQL.
> When the value is rounded up, your timestamp will be in the future.

# Expressions

Laravel 10 added the functionality to use pre-made expressions with the query builder like this that generate vendor-specific SQL for complex operations:

```php
BlogVisit::select([
    'url',
    new TimestampBin('created_at', DateInterval::createFromDateString('5 minutes')),
    new Count('*'),
])->groupBy(
    'url',
    new TimestampBin('created_at', DateInterval::createFromDateString('5 minutes'))
);
```

I've already released a lot of expressions with my package [tpetry/laravel-query-expressions](https://github.com/tpetry/laravel-query-expressions) that are usable on all databases supported by Laravel.
But some functionality just can't be built for all database.
So here are some PostgreSQL specific ones:

## Uuid7

You can now generate time-sorted UUIDv7 IDs directly in the database.
The drawback of using `Str::orderedUuid()` is that inserting new rows can only be done from Laravel:
You loose the ability to insert new rows manually with a GUI, simple `INSERT` queries or efficient approaches like `INSERT INTO ... SELECT`.
But all of these are still possible with IDs generated at the database.

```php
use Tpetry\PostgresqlEnhanced\Expressions\Uuid7;

Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->uuid()->default(new Uuid7())->unique();
    $table->text('text');
});        
```

# Supported Extensions

You can use any extension with this PostgreSQL you like but some have received a deeper Laravel integration.

## Timescale

Timescale is fantastic and has many features.
Therefore, it is impossible to explain everything here; consult their docs about the different features this extension provides.
Here's a list of supported features and an example showcasing its usage:

| Feature               | Actions                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
|-----------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Hypertable            | `new CreateHypertable(string $column, string\|int $interval, string $partitionFunction = null)`<br/>`new ChangeChunkTimeInterval(string\|int $interval)`                                                                                                                                                                                                                                                                                                                            |
| Chunk Skipping        | `new EnableChunkSkipping(string $column)`<br/>`new DisableChunkSkipping(string $column)`                                                                                                                                                                                                                                                                                                                                                                                            |
| Compression           | `new EnableCompression(string\|array $orderBy = null, string\|array $segmentBy = null)`<br/> `new DisableCompression()`<br/>`new CreateCompressionPolicy(string\|int $compressAfter)`<br/>`new DropCompressionPolicy()`<br/>`new CompressChunks(DateTimeInterface\|string\|int $olderThan = null, DateTimeInterface\|string\|int $newerThan = null)`<br/>`new DecompressChunks(DateTimeInterface\|string\|int $olderThan = null, DateTimeInterface\|string\|int $newerThan = null)` |
| Reordering            | `new CreateReorderPolicy(string $index)`<br/>`new CreateReorderPolicyByIndex(...$columns)`<br/>`new CreateReorderPolicyByUnique(...$columns)`<br/>`new DropReorderPolicy()`<br/>`new ReorderChunks(DateTimeInterface\|string\|int $olderThan = null, DateTimeInterface\|string\|int $newerThan = null)`                                                                                                                                                                             |
| Data Retention        | `new CreateRetentionPolicy(string\|int $dropAfter)`<br/>`new DropRetentionPolicy()`<br/>`new DropChunks(DateTimeInterface\|string\|int $olderThan = null, DateTimeInterface\|string\|int $newerThan = null)`                                                                                                                                                                                                                                                                        |
| Tiered Storage        | `new CreateTieringPolicy(string\|int $dropAfter)`<br/>`new DropTieringPolicy()`<br/>`new TierChunks(DateTimeInterface\|string\|int $olderThan = null, DateTimeInterface\|string\|int $newerThan = null)`<br/>`new UntierChunks(DateTimeInterface\|string\|int $olderThan = null, DateTimeInterface\|string\|int $newerThan = null)`                                                                                                                                                 |
| Continuous Aggregates | `new CreateRefreshPolicy(string $interval, string\|int\|null $start, string\|int\|null $end)`<br/>`new DropRefreshPolicy()`<br/>`new RefreshData(DateTimeInterface\|int\|null $start, DateTimeInterface\|int\|null $end)`                                                                                                                                                                                                                                                           |

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateCompressionPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateHypertable;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateRefreshPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateReorderPolicyByIndex;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\CreateRetentionPolicy;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\EnableChunkSkipping;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\Actions\EnableCompression;
use Tpetry\PostgresqlEnhanced\Schema\Timescale\CaggBlueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::createExtensionIfNotExists('timescaledb');

        Schema::create('visits', function (Blueprint $table) {
            $table->identity();
            $table->bigInteger('website_id');
            $table->text('url');
            $table->float('duration');
            $table->timestampTz('created_at');

            $table->primary(['id', 'created_at']);
            $table->index(['website_id', 'created_at']);

            $table->timescale(
                new CreateHypertable('created_at', '1 day'),
                new CreateReorderPolicyByIndex('website_id', 'created_at'),
                new EnableCompression(segmentBy: 'website_id'),
                new CreateCompressionPolicy('3 days'),
                new CreateRetentionPolicy('1 year'),
                new EnableChunkSkipping('id'),
            );
        });

        Schema::continuousAggregate('visits_agg', function(CaggBlueprint $table) {
            $table->as("
                SELECT
                    time_bucket('1 hour', created_at) AS bucket,
                    website_id,
                    url,
                    SUM(duration) AS duration
                FROM visits
                GROUP BY bucket, website_id, url
            ");
            $table->realtime();
            $table->index(['website_id','url']);

            $table->timescale(
                new CreateRefreshPolicy('5 minutes', '1 days', '2 hours'),
                new EnableCompression(),
                new CreateCompressionPolicy('2 days'),
            );
        });
    }
};
```

> [!WARNING]
> Indexes are not automatically created when creating hypertables or continuous aggregates.
> You have to create them manually.

# Breaking Changes

* **2.0.0**
  * Laravel 11.25 released a new `vector` migration type so the behaviour had to be aligned with Laravel's implementation:
    * A missing `$dimensions` parameter now leads to a vector type with unspecified dimension size (formerly defaulted to `$dimensions = 1536`) 
* **1.0.0**
  * Laravel 11.17 released a new `whereLike` and `orWhereLike` builder method so the behaviour had to be aligned with Laravel's implementation:
    * The value is now searched case-insensitive by default instead of case-sensitive
    * The `$caseSensitive` parameter had to be replaced by the `$caseInsensitive` parameter
* 0.39.0 -> 0.40.0
  * The Enhanced PostgreSQL Driver PHPStan extension is now automatically registered with the PHPStan Extension Installer. The manual registration of the extension needs to be removed when PHPStan crashed because the extension is registered twice.
* 0.35.0 -> 0.36.0
    * Some query builder methods had to be changed because they've now overlapped with new ones added by Laravel 10.47:
      * `whereAll` -> `whereAllValues`
      * `whereNotAll` -> `whereNotAllValues`
      * `orWhereAll` -> `orWhereAllValues`
      * `orWhereNotAll` -> `orWhereNotAllValues`
      * `whereAny` -> `whereAnyValue`
      * `whereNotAny` -> `whereNotAnyValue`
      * `orWhereAny` -> `orWhereAnyValue`
      * `orWhereNotAny` -> `orWhereNotAnyValue`
* 0.10.0 -> 0.11.0
  * The `ZeroDowntimeMigration` concern namespace moved from `Tpetry\PostgresqlEnhanced\Concerns` to `Tpetry\PostgresqlEnhanced\Schema\Concerns`.
* 0.12.0 -> 0.12.1
  * The return type of all returning statements was changed from `array` to `Collection` to replicate the `Query\Builder::get()` method signature.

# Contribution

If you want to contribute code to this package, please open an issue first. To avoid unnecessary effort for you it is very beneficial to first discuss the idea, the functionality and its API.

# Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

# Security Vulnerabilities

If you discover any security related issues, please email github@tpetry.me instead of using the issue tracker.

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[href-phpstantest]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/phpstan.yml
[href-style]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/php_cs_fixer.yml
[href-tests]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/phpunit.yml
[href-version]: https://packagist.org/packages/tpetry/laravel-postgresql-enhanced
[href-downloads]: https://packagist.org/packages/tpetry/laravel-postgresql-enhanced/stats
[icon-codestyle]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHP%20CS%20Fixer?label=Code%20Style
[icon-license]: https://img.shields.io/github/license/tpetry/laravel-postgresql-enhanced?color=blue&label=License
[icon-phpstantest]: https://img.shields.io/github/actions/workflow/status/tpetry/laravel-postgresql-enhanced/phpstan.yml?label=PHPStan
[icon-php]: https://img.shields.io/packagist/php-v/tpetry/laravel-postgresql-enhanced?color=blue&label=PHP
[icon-style]: https://img.shields.io/github/actions/workflow/status/tpetry/laravel-postgresql-enhanced/php_cs_fixer.yml?label=Code%20Style
[icon-tests]: https://img.shields.io/github/actions/workflow/status/tpetry/laravel-postgresql-enhanced/phpunit.yml?label=Tests
[icon-version]: https://img.shields.io/packagist/v/tpetry/laravel-postgresql-enhanced.svg?label=Packagist
[icon-downloads]: https://img.shields.io/packagist/dt/tpetry/laravel-postgresql-enhanced.svg?color=orange&label=Downloads
