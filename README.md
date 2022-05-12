
[![License][icon-license]][href-license]
[![PHP][icon-php]][href-php]
[![PHP][icon-phpstanconfig]][href-phpstanconfig]
[![Latest Version on Packagist][icon-version]][href-version]
[![GitHub PHPUnit Action Status][icon-tests]][href-tests]
[![GitHub PHPStan Action Status][icon-phpstantest]][href-phpstantest]
[![GitHub PhpCsFixer Action Status][icon-style]][href-style]

Laravel supports many different databases and therefore has to limit itself to the lowest common denominator of all databases. PostgreSQL, however, offers a ton more functionality which is being added to Laravel by this extension.

# Installation

You can install the package via composer:

```bash
composer require tpetry/laravel-postgresql-enhanced
```

# Features

- [PHPStan](#phpstan)
- [Migration](#migration)
    - [Zero Downtime Migration](#zero-downtime-migration)
    - [Extensions](#extensions)
    - [Views](#views)
        - [Materialized Views](#materialized-views)
    - [Indexes](#indexes)
        - [Partial Indexes](#partial-indexes)
        - [Include Columns](#include-columns)
        - [Storage Parameters](#storage-parameters)
        - [Functional Indexes / Column Options](#functional-indexes--column-options)
        - [Fulltext Indexes](#fulltext-indexes)
    - [Column Options](#column-options)
        - [Compression](#compression)
    - [Column Types](#column-types)
        - [Bit Strings](#bit-strings)
        - [Case Insensitive Text](#case-insensitive-text)
        - [Full Text Search](#full-text-search)
        - [Hstore](#hstore)
        - [Identity](#identity)
        - [IP Networks](#ip-networks)
        - [International Product Numbers](#international-product-numbers)
        - [Label Tree](#label-tree)
        - [Ranges](#ranges)
        - [XML](#xml)
- [Query](#query)
    - [Explain](#explain)
    - [Fulltext Search](#fulltext-search)
    - [Lateral Subquery Joins](#lateral-subquery-joins)
    - [Returning Data From Modified Rows](#returning-data-from-modified-rows)
    - [Lazy By Cursor](#lazy-by-cursor)
- [Eloquent](#eloquent)
    - [Refresh Data on Save](#refresh-data-on-save)

## PHPStan

This extension is adding a lot of missing PostgreSQL functionality to Laravel.
If you are using [PHPStan](https://phpstan.org/) to statically analyze your code, you may get errors because PHPStan doesn't know of the functionality added to Laravel:

```
 ------ ----------------------------------------------------------------------------------- 
  Line   Console/Commands/DeleteOldUsers.php                                                           
 ------ ----------------------------------------------------------------------------------- 
  36     Call to an undefined method Illuminate\Database\Query\Builder::deleteReturning().  
 ------ ----------------------------------------------------------------------------------- 
```

To solve this problem a custom set of PHPStan extensions have been developed to get full static analysis support for Laravel 9!
You should first install [Larastan](https://github.com/nunomaduro/larastan) to get PHPStan support for Laravel and then activate the PostgreSQL PHPStan extension.
Just add the following path to your `includes` list in `phpstan.neon`, your config should now look like this:

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

### Views

#### Create Views

The `Schema` facade supports the creation of views with the `createView` and `createViewOrReplace` methods. The definition of your view can be a sql query string or a query builder instance:
```php
use Illuminate\Support\Facades\DB;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::createView('users_with_2fa', 'SELECT * FROM users WHERE two_factor_secret IS NOT NULL');
Schema::createViewOrReplace('users_without_2fa', DB::table('users')->whereNull('two_factor_secret'));
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

To use these great features and not break compatability with Laravel the method `uniqueIndex` has been added which can be used identical to `unique`:
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

#### Storage Parameters

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
    $table->unique('(LOWER(email))', 'users_email_unique');
    $table->index(['firstname ASC NULLS FIRST', 'lastname ASC NULLS FIRST'])
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

### Column Options
#### Compression
PostgreSQL 14 introduced the possibility to specify the compression method for toast-able data types.
You can choose between the default method `pglz`, the recently added `lz4` algorithm and the value `default` to use the server default setting.
```php
// @see https://www.postgresql.org/docs/current/storage-toast.html
$table->string('col')->compression('lz4');
```

### Column Types

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

> You need to enable the `isn` extension with `Schema::createExtensionIfNotExists('isn')` or `Schema::createExtension('isn')` before.

#### Label Tree
The ltree data type stores a label as its position in a tree. This provides an easy way to manage a tree without performance and complexity disadvantages compared to alternative solutions.
```php
// @see https://www.postgresql.org/docs/current/ltree.html
$table->labelTree(string $column);
```

> You need to enable the `ltree` extension with `Schema::createExtensionIfNotExists('ltree')` or `Schema::createExtension('ltree')` before.

#### Ranges
The range data types store a range of values with optional start and end values. They can be used e.g. to describe the duration a meeting room is booked.
```php
// @see https://www.postgresql.org/docs/current/rangetypes.html
$table->bigIntegerRange(string $column);
$table->dateRange(string $column);
$table->decimalRange(string $column);
$table->integerRange(string $column);
$table->timestampRange(string $column);
$table->timestampTzRange(string $column);
```

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
    ->crossJoinSubLateral(
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

## Eloquent

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

# Breaking Changes

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

[href-license]: LICENSE.md
[href-php]: https://packagist.org/packages/tpetry/laravel-postgresql-enhanced
[href-phpstanconfig]: phpstan.neon.dist
[href-phpstantest]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/phpstan.yml
[href-style]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/php_cs_fixer.yml
[href-tests]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/phpunit.yml
[href-version]: https://packagist.org/packages/tpetry/laravel-postgresql-enhanced
[icon-codestyle]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHP%20CS%20Fixer?label=Code%20Style
[icon-license]: https://img.shields.io/github/license/tpetry/laravel-postgresql-enhanced?color=blue&label=License
[icon-phpstanconfig]: https://img.shields.io/badge/PHPStan%20Level-5-blue
[icon-phpstantest]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHPStan?label=PHPStan
[icon-php]: https://img.shields.io/packagist/php-v/tpetry/laravel-postgresql-enhanced?color=blue&label=PHP
[icon-style]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHP%20CS%20Fixer?label=Code%20Style
[icon-tests]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHPUnit?label=Tests
[icon-version]: https://img.shields.io/packagist/v/tpetry/laravel-postgresql-enhanced.svg?label=Packagist
[logo]: .art/teaser.png
