Nether Database
=============================================================================
[![nether.io](https://img.shields.io/badge/nether-database-C661D2.svg)](http://nether.io/database/)
[![Build Status](https://travis-ci.org/netherphp/database.svg)](https://travis-ci.org/netherphp/database)
[![Packagist](https://img.shields.io/packagist/v/netherphp/database.svg)](https://packagist.org/packages/netherphp/database)
[![Packagist](https://img.shields.io/packagist/dt/netherphp/database.svg)](https://packagist.org/packages/netherphp/database)

A database connection and query library.



Install
-------------------------------------------------------------------------------

Composer yourself a netherphp/database of version 2.* and any dependencies will
be automatically handled.

```json
{
	"require": {
		"netherphp/database": "2.*"
	}
}
```



Define Connections.
-------------------------------------------------------------------------------

You define connections by creating an array of configs in Nether Option under
the `nether-database-connections` key. So you're creating an array of arrays
here. The array named "Default" will be the connection used by default if you
did not specify one to the constructor of the database object. The Type field
is any valid PDO driver you have installed. For example, "mysql".

```php
<?php

Nether\Option::Set('nether-database-connections',[
	'Default' => [
		'Type'     => '%TYPE',
		'Hostname' => '%HOSTNAME',
		'Username' => '%USERNAME',
		'Password' => '%PASSWORD',
		'Database' => '%DATABASE'
	]
]);
```



Connect And Query
-------------------------------------------------------------------------------

```php
<?php

$DB = new Nether\Database(<string Alias>);
// if no database alias is specified then the connection named "Default"
// be used as... the default.

// if the connection has not yet been made, it will be established the first
// time `new Nether\Database` is called. it will then be kept open for the
// remainder of the application for any other future calls to
// `new Nether\Database` to reuse.

$Result = $DB->Query(
	'SELECT Stuff FROM Table WHERE Something=:Something AND Else=:Else;',
	[ ':Something' => $_GET['Something'], ':Else' => $_GET['Else'] ]
);

// queries are automatically sql injection protected via the PDO bound
// arugment system.

while($Row = $Result->Next()) {
	echo $Row->Stuff, PHP_EOL;
}

// when the result object hits the end of the results, it will automatically
// free the resources unless it is told not to prior to iteration.
```



Verses (Query Builder)
-------------------------------------------------------------------------------

```php
<?php

$DB = new Nether\Database;
$Arg = [ ':Something' => $_GET['Something'], ':Else' => $_GET['Else'] ];

// you can craft a new verse from your connection. this is the suggested way to
// do it so that the verse is aware of the connection and can pass it along to
// things that need it later.

// the sql builder is still in an early state, it currently only really builds
// mysql (yes, there are differences) and mainly for simple queries. but it is
// still easier to format than a giant string in code. additional databases can
// be supported by extending Nether Database's Verse Compiler.

$SQL = $DB
->NewVerse()
->Select('FromTable')
->Values('Field1, Field2, Field3')
->Where('Something=:Something');

// most of the methods accept either a single string or an array of them.

$SQL = $DB
->NewVerse()
->Select('FromTable')
->Values(['Field1','Field2','Field3'])
->Where([
	'SearchMain' => 'Something=:Something'
]);

// if you even use an associative array like did in the WHERE above, you
// can overwrite a specific condition or fieldset later on in the future
// without starting the query over.

$SQL->Where([
	'SearchMain' => 'Something!=:Something'
]);

// and then you just hand the entire thing over to the database object.

$Result = $DB->Query($SQL,$Arg);
while($Row = $Result->Next()) {
	echo $Row->Field1, ': ', $Row->Field2, PHP_EOL;
}
```



Codas (Query Fragments)
-------------------------------------------------------------------------------

Some clauses are really hard to write proceedurally in code in a way that is
clean and readable. Codas are fragments of clauses which can extend verses in
a way that is easier to program than editing strings. Here are some examples
that are ready for use.

```php
<?php

$DB = new Nether\Database;
$SQL = $DB->NewVerse();
$Arg = [ ':ObjectIDs' => [42,69,720,1080] ];

$SQL
->Select('FromTable')
->Values(['Field1','Field2','Field3'])
->Where('ObjectID IN(:ObjectIDs)');

$Result = $DB->Query($SQL,$Arg);
```

The first pass of building this query would generate a string like this.

```sql
SELECT Field1,Field2,Field3
FROM FromTable
WHERE ObjectID IN(:ObjectIDs)
```

But this query will not work. The bound parameter system will not propery
encapsulate every element of the list. However since Nether Database will see
that you gave it an array for the value of :ObjectIDs, it will treat it as a
coda and expand it like so:

```sql
SELECT Field1,Field2,Field3
FROM FromTable
Where ObjectID IN(:ObjectIDs__0,:ObjectIDs__1,:ObjectIDs__2,:ObjectIDs__3)
```

And internally (and only internally), it will flatten your $Arg array to look
like this:

```ruby
Array(
	[:ObjectIDs__0] => int(42),
	[:ObjectIDs__1] => int(69),
	[:ObjectIDs__2] => int(720),
	[:ObjectIDs__3] => int(1080)
)
```

Which will now make the ObjectIDs list in the Arg variable work with the bound
argument system, thusly protecting every single element in it from injection
just the same as normal bound arguments.
