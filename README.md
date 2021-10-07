<p align="center">

![][logo]

</p>

[![License][icon-license]][href-license]
[![PHP][icon-php]][href-php]
[![PHP][icon-psalmconfig]][href-psalmconfig]
[![Latest Version on Packagist][icon-version]][href-version]
[![GitHub PHPUnit Action Status][icon-tests]][href-tests]
[![GitHub Psalm Action Status][icon-psalmtest]][href-psalmtest]
[![GitHub PhpCsFixer Action Status][icon-style]][href-style]

Laravel supports many different databases and therefore has to limit itself to the lowest common denominator of all databases. PostgreSQL, however, offers a ton more functionality which is being added to Laravel by this extension.

# Installation

You can install the package via composer:

```bash
composer require tpetry/laravel-postgresql-enhanced
```

# Features

- [Migration](#migration)
    - [Extensions](#extensions)
    - [Views](#views)
    - [Indexes](#indexes)
        - [Partial Indexes](#partial-indexes)
        - [Include Columns](#include-columns)
        - [Storage Parameters](#storage-parameters)
        - [Functional Indexes / Column Options](#functional-indexes--column-options)
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

## Migration

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
Schema::createView('viewname', 'SELECT id, col1, col2 FROM ....', ['id', 'col1', 'col2']);
Schema::createViewOrReplace('viewname', 'SELECT id, col1, col2 FROM ....', ['id', 'col1', 'col2']);
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

Schema::dropExtension('myview1', 'myview2');
Schema::dropExtensionIfExists('myview1', 'myview2');
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
The methods `dropIndexIfExists`, `dropPrimaryIfExists`, `dropSpatialIndexIfExists` and `dropSpatialIndexIfExists` match the semantics of their laravel originals.

#### Partial Indexes

A partial index is an index built over a subset of a table; the subset is defined by a condition. The index contains entries only for those table rows that satisfy the condition. Partial indexes are a specialized feature, but there are several situations in which they are useful.
Take for example you want to make the email address column of your users table unique and you are using soft-deletes. This is not possible because by deleting a user and creating it again the email address is used twice. With partial indexes this can be done by limiting the index to only untrashed rows:
```php
use Illuminate\Database\Query\Builder;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

Schema::table('users', function(Blueprint $table) {
    $table->uniqueIndex('email')->partial("deleted_at IS NULL");
    // or:
    $table->uniqueIndex('email')->partial(fn (Builder $condition) => $condition->whereNull('deleted_at'));
});
```

Partial Indexes are created with the `partial` method on an index created by `index()`, `spatialIndex` or `uniqueIndex`.

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
Storage parameters are defined with the `with` method on an index created by `index()`, `spatialIndex` or `uniqueIndex`.

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

#### Label Tree
The ltree data type stores a label as its position in a tree. This provides an easy way to manage a tree without performance and complexity disadvantages compared to alternative solutions.
```php
// @see https://www.postgresql.org/docs/current/ltree.html
$table->labelTree(string $column);
```

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
[href-psalmconfig]: psalm.xml.dist
[href-psalmtest]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/psalm.yml
[href-style]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/php_cs_fixer.yml
[href-tests]: https://github.com/tpetry/laravel-postgresql-enhanced/actions/workflows/phpunit.yml
[href-version]: https://packagist.org/packages/tpetry/laravel-postgresql-enhanced
[icon-codestyle]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHP%20CS%20Fixer?label=Code%20Style
[icon-license]: https://img.shields.io/github/license/tpetry/laravel-postgresql-enhanced?color=blue&label=License
[icon-psalmconfig]: https://img.shields.io/badge/Psalm%20Level-4-blue
[icon-psalmtest]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/Psalm?label=Psalm
[icon-php]: https://img.shields.io/packagist/php-v/tpetry/laravel-postgresql-enhanced?color=blue&label=PHP
[icon-style]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHP%20CS%20Fixer?label=Code%20Style
[icon-tests]: https://img.shields.io/github/workflow/status/tpetry/laravel-postgresql-enhanced/PHPUnit?label=Tests
[icon-version]: https://img.shields.io/packagist/v/tpetry/laravel-postgresql-enhanced.svg?label=Packagist
[logo]: .art/teaser.png
