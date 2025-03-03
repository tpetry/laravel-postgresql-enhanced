# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.2] - 2025-03-03
### Fixed
* (or)WhereNotLike query build did not use the negation in the query
* The (or)Where(Not)Like query builder methods did not cast the column to text like in Laravel (`col::text`)

## [2.3.1] - 2025-03-02
### Fixed
* Artisan `migrate:fresh` support for materialized views
* Artisan `migrate:fresh --drop-views` support for Timescale hypertables and continuous aggregates

## [2.3.0] - 2025-02-15
### Added
* Laravel 12 support

## [2.2.0] - 2025-01-25
### Added
* Timescale migration support

## [2.1.0] - 2024-12-16
### Added
* `JsonForceEmptyObjectAsArray` eloquent cast

## [2.0.2] - 2024-12-05
### Fixed
* PHP 8.4 deprecation warnings because of implicitly marked nullable parameters

## [2.0.1] - 2024-10-30
### Fixed
* Laravel 11.30 compatability (`$dimension` parameter of `vector` migration type is now optional)

## [2.0.0] - 2024-09-27
### Backward Incompatible Changes
* The dimensions value for the `vector` migration type is now required to copy the behavior of Laravel 11.25.0

## [1.1.1] - 2024-09-23
### Fixed
* Support PostgreSQL's `^@` starts with operator.

## [1.1.0] - 2024-09-23
### Fixed
* Eager loading and lazy loading prevention was not implemented for lazyByCursor()

### Added
* `orderByNullsFirst`, `orderByNullsLast` and `$nulls` parameter for `orderBy`

## [1.0.1] - 2024-08-05
### Fixed
* Migration failed when automatic aliases had been disabled in Laravel

## [1.0.0] - 2024-07-31
### Backward Incompatible Changes
* The `whereLike` and `orWhereLike` had been changed to the behavior of Laravel 11.17.0

### Fixed
* Migration failed when automatic aliases had been disabled in Laravel

## [0.40.2] - 2024-07-16
### Fixed
* Changes to adapt to bc-breaking migration behavior of Laravel 11.15.0

## [0.40.1] - 2024-07-15
### Fixed
* Changing compression mode conflicted with upstream Laravel 11.x changes

## [0.40.0] - 2024-06-28
### Added
* PHPStan Extension Installer Support
* Doctrine DBAL 4 Support (deep Laravel 11 dependency)

## [0.39.0] - 2024-05-21
### Added
* `concurrently()` modifier for index creation

## [0.38.0] - 2024-04-22
### Added
* UUIDv7 expression

## [0.37.0] - 2024-03-12
### Added
* Laravel 11 compatibility

## [0.36.0] - 2024-03-06
### Fixed
* Incompatability with Laravel 10.47.0

### Breaking Changes
* Some query builder methods had to be changed because they've now overlapped with new ones added by Laravel 10.47:
  - `whereAll` -> `whereAllValues`
  - `whereNotAll` -> `whereNotAllValues`
  - `orWhereAll` -> `orWhereAllValues`
  - `orWhereNotAll` -> `orWhereNotAllValues`
  - `whereAny` -> `whereAnyValue`
  - `whereNotAny` -> `whereNotAnyValue`
  - `orWhereAny` -> `orWhereAnyValue`
  - `orWhereNotAny` -> `orWhereNotAnyValue`

## [0.35.0] - 2024-02-26
### Added
* IDE autocomplete support for PhpStorm with Laravel Idea plugin

### Fixed
* Table prefixes had been ignored by query grammar

## [0.34.0] - 2024-01-26
### Added
* USING clause for column type migrations

## [0.33.0] - 2023-10-23
### Added
* pgvector support: vector type, eloquent cast, query builder similarity sorting

## [0.32.0] - 2023-10-10
### Added
* Alternative column names for views

## [0.31.0] - 2023-06-26
### Added
* Integer array type for migrations
* Integer array cast for eloquent
* whereIntegerArrayMatches query builder method

## [0.30.1] - 2023-06-01
### Fixed
* Support Laravel 10.13 grammar escaping for values

## [0.30.0] - 2023-05-08
### Added
* Automatic date format traits for eloquent models

## [0.29.0] - 2023-04-12
### Added
* Initial column modifier

## [0.28.0] - 2023-03-30
### Added
* Tables can be set unlogged
* Table storage parameters can be set

## [0.27.1] - 2023-03-24
### Fixed
* `rawIndex` definitions had not been handled correctly

## [0.27.0] - 2023-02-23
### Added
* Triggers can be added to tables

## [0.26.0] - 2023-02-02
### Added
* Laravel 10.x compatibility

## [0.25.1] - 2023-01-13
### Fixed
* Provide doctrine data types for all console operations to support schema inspection tools

## [0.25.0] - 2023-01-10
### Added
* Domain types support for migrations

## [0.24.0] - 2023-01-09
### Added
* Extensions can be added to specific schemas

## [0.23.1] - 2023-01-03
### Fixed
* The recursive keyword was added multiple times when multiple recursive CTEs had been added

### Changed
* Adding the same CTE multiple times now only keeps the last one

## [0.23.0] - 2022-11-11
### Added
* Methods with query param now use Laravel 9 query contract for doctype to do better PHPStan type checking
 
## [0.22.0] - 2022-11-03
### Added
* Functions created in migrations can also return tables
* Unique indexes with NULLS NOT DISTINCT options

## [0.21.0] - 2022-10-18
### Added
* `whereAll` clause for query builder
* `whereAny` clause for query builder
* `whereBoolean` clause for query builder
* `whereBetweenSymmetric` clause for query builder

## [0.20.1] - 2022-09-09
### Fixed
* Manually created `\Illuminate\Database\Query\Builder` instances failed when trying to process CTE expressions

## [0.20.0] - 2022-08-23
### Added
* Creating and deleting functions in migrations

## [0.19.0] - 2022-07-12
### Added
* Column Types:
    * bigIntegerMultiRange
    * dateMultiRange
    * decimalMultiRange
    * integerMultiRange
    * timestampMultiRange
    * timestampTzMultiRange

## [0.18.0] - 2022-07-08
### Added
* Common Table Expressions

## [0.17.0] - 2022-06-30
### Added
* PostgreSQL returning statements for eloquent builder:
    * `deleteReturning`
    * `forceDeleteReturning`
    * `insertOrIgnoreReturning`
    * `insertReturning`
    * `insertUsingReturning`
    * `updateFromReturning`
    * `updateOrInsertReturning`
    * `updateReturning`
    * `upsertReturning`

## [0.16.0] - 2022-06-26
### Added
* `whereLike` clause for query builder

## [0.15.1] - 2022-06-09
### Fixed
* Rolling back migrations failed with type error since 0.14.0

## [0.15.0] - 2022-05-24
### Added
* Support for left later joins with an 'ON true' condition

## [0.14.0] - 2022-05-12
### Added
* PHPStan support for all extensions to Laravel

## [0.13.0] - 2022-03-08
### Added
* `lazyByCursor` for `Query\Builder` and `Eloquent\Builder`

## [0.12.1] - 2022-02-15
### Fixed
* Changed returning statements to return a collection instead of array like Query\Builder::get()

### Breaking Change
* The return type of all returning statements was changed from array to Collection to replicate the Query\Builder::get() method signature.

## [0.12.0] - 2022-02-10
### Added
* Materialized views

## [0.11.0] - 2022-02-04
### Added
* PostgreSQL returning statements for query builder:
  * `deleteReturning`
  * `insertOrIgnoreReturning`
  * `insertReturning`
  * `insertUsingReturning`
  * `updateFromReturning`
  * `updateOrInsertReturning`
  * `updateReturning`
  * `upsertReturning`
* Eloquent concern `RefreshDataOnSave`

### Breaking Change
* The `ZeroDowntimeMigration` concern namespace moved from `Tpetry\PostgresqlEnhanced\Concerns` to `Tpetry\PostgresqlEnhanced\Schema\Concerns`

## [0.10.0] - 2022-01-27
### Added
* Laravel 9 compatibility

## [0.9.0] - 2022-01-20
### Added
* If exists fulltext index dropping
* Partial index support for fulltext indexes
* Storage parameters support for fulltext indexes
* Column weighting for fulltext indexes and query builder

## [0.8.0] - 2021-12-28
### Added
* Lateral Subquery Joins for Query Builder

## [0.7.1] - 2021-12-22
### Fixed
* PostgreSQL specific explain output on Eloquent\Builder instances

## [0.7.0] - 2021-12-16
### Added
* PostgreSQL specific explain output on Query\Builder instances

## [0.6.1] - 2021-10-28
### Fixed
- Zero Downtime Migration support for Laravel 6.x and 7.x

## [0.6.0] - 2021-10-28
### Added
- Zero Downtime Migrations

### Changed
- The internal grammar name for the PostgreSQL types has been changed to their native names

## [0.5.0] - 2021-10-12
### Added
- Identity column type
- Tsvector column type
- Functional Indexes
- Column Index Options
- Column compression modifier

## [0.4.0] - 2021-09-18
### Added
- Index Storage Parameters
- Index Include Columns

### Fixed
- Query Builder to raw query string did use integer instead of boolean literal for boolean values

### Changed
- Partial indexes are no longer extra functions of the table blueprint, they are now index options

## [0.3.1] - 2021-07-16
### Fixed
- Query builder to sql helper's typing did not allow eloquent builder

## [0.3.0] - 2021-04-27
### Added
- If exists index dropping
- Partial indexes

## [0.2.1] - 2021-04-22
### Fixed
- Service Provider no longer extends DatabaseServiceProvider (#1)

## [0.2.0] - 2021-04-17
### Added
- Extension Management
- View Management

## [0.1.0] - 2021-04-14
### Added
- Column Types:
  - bigIntegerRange
  - bit
  - caseInsensitiveText
  - dateRange
  - decimalRange
  - europeanArticleNumber13
  - hstore
  - integerRange
  - internationalStandardBookNumber
  - internationalStandardBookNumber13
  - internationalStandardMusicNumber
  - internationalStandardMusicNumber13
  - internationalStandardSerialNumber
  - internationalStandardSerialNumber13
  - ipNetwork
  - labelTree
  - timestampRange
  - timestampTzRange
  - universalProductNumber
  - varbit
  - xml
