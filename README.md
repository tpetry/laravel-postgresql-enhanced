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
  - [Column Types](#column-types)
    - [Bit Strings](#bit-strings)
    - [Case Insensitive Text](#case-insensitive-text)
    - [Hstore](#hstore)
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
