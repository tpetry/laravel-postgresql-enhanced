# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.12.1] - 2022-02-15
### Fixed
* Changed returning statements to return a collection instead of array like Query\Builder::get()

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
* Laravel 9 compatability

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
