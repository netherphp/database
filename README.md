Nether Database
=============================================================================
[![nether.io](https://img.shields.io/badge/nether-database-C661D2.svg)](http://nether.io/database/)
[![Packagist](https://img.shields.io/packagist/v/netherphp/database.svg)](https://packagist.org/packages/netherphp/database)
[![Packagist](https://img.shields.io/packagist/dt/netherphp/database.svg)](https://packagist.org/packages/netherphp/database)

A lower-level database connection and query library. Provides a simple API
for connecting, querying, and digesting the results from a database server.

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

