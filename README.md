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

