# Nether Database
[![Packagist](https://img.shields.io/packagist/v/netherphp/database.svg?style=for-the-badge)](https://packagist.org/packages/netherphp/database)
[![Build Status](https://img.shields.io/github/actions/workflow/status/netherphp/database/phpunit.yml?style=for-the-badge)](https://github.com/netherphp/database/actions)
[![codecov](https://img.shields.io/codecov/c/gh/netherphp/database?style=for-the-badge&token=VQC48XNBS2)](https://codecov.io/gh/netherphp/database)

A lower-level database connection and query library. Provides a simple API for connecting, querying, and digesting the results from a database server.


## Requirements

* PHP 8.1+
* PDO


## Supported Servers

### Basic Connect/Query

* Anything PDO on the system can connect to.
* And you wrote SQL that executes upon it.
* See [Quickstart Guide](https://github.com/netherphp/database/wiki/Quickstart-Guide) for visual examples.

### SQL Generator (Verse)

To use the SQL abstractor there will need to be a compiler written for that
server to make it generate using the proper and best keywords available
supported by that server.

* MySQL / MariaDB (PDO Driver: 'mysql')
* See [Verse SQL Generator](https://github.com/netherphp/database/wiki/Verse-SQL-Generator) for examples.



## Command Line Interface

This library also sets up a `netherdb` command in `vendor/bin` to help get
various tasks done.

* See [NetherDB Command Line](https://github.com/netherphp/database/wiki/NetherDB-Command-Line) for examples.


## Additional Libraries

To do its work Nether Database will also include the following NetherPHP libraries. There is no danger of your application suddenly becoming a "NetherPHP" application though. These are all utility which are there for you to use as well, if desired, as they will be about anyway.

* netherphp/option (configuration management)
* netherphp/console (cli library)
* netherphp/object (prototyping)



# Classes & Interfaces

## `Database\Prototype`

This class adds basic database search and manipulation on top of the `Common\Prototype` features. Use it as a base class to get all the functionaility of `Common` and `Database` together.

### `Database\Prototype::Find(iterable $Filters)`

This method provides the basic paginated search ability. Given a set of filters classes that extend this class can decide what to do about them. There are also some special filters that apply across all:

> `'Page' => 1, 'Limit' => 20`

These are the main pagination filters.

> `'Seed' => 1234`

A seed to use for any RNG based operations. If fed a value that only changes once per day, you can query with a `Sort` of `random` and it will return the same rows that entire day.

> `'Remappers' => [ callable, ... ]`

Give it a callable, or array of, and it will be used as a `Remap` callable upon the result datastore. Useful for searching on a class that links two objects together, but mapping the result down to only the parts of the data that were needed.

> `'Resolvers' => [ callable, ... ]`

Normally each row of the result set is instantiated to be an object of the class that performed the search. When given a list of callables, it will give them the row as it came out of the database. If it returns the name of a valid class (string) it will be used to instantiate this row. If it returns NULL the next callable in the list will be given a shot.

This can be useful to optimise some searches on a parent class that can spit out a collection of various child typed rows.

> `'Sort' => 'how'`

Select the sort method to use in this search. This class provides default implementations for `pk-az`, `pk-za`, and `random`. More sorts can be provided by child classes.

> `'Debug' => TRUE`

When enabled this will attach the query result object to the collection so it can be inspected.

