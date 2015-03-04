Nether Database
=====================================
[![Code Climate](https://codeclimate.com/github/netherphp/database/badges/gpa.svg)](https://codeclimate.com/github/netherphp/database)

A database connection and query tool.

Requires (autofilled by composer)
-------------------------------------
* Nether\Object
* Nether\Option

Install
-------------------------------------
Composer yourself a netherphp/database of version 1.*

	{
		"require": {
			"netherphp/database": "1.*"
		}
	}

Dirty How-To, before I forget.
=====================================
Define Connections.
-------------------------------------

	Nether\Option::Set('database-connections',[
		'Default' => new Nether\Database\Connection([
			'Type'     => '%TYPE',
			'Hostname' => '%HOSTNAME',
			'Username' => '%USERNAME',
			'Password' => '%PASSWORD',
			'Database' => '%DATABASE'
		])
	]);

The Type field is any valid PDO driver you have installed. For example, "mysql".

Connect And Query
--------------------------------

	$db = new Nether\Database(<string Alias>);
	// if no database alias is specified then the connection named "Default"
	// be used as... the default.

	// if the connection has not yet been made, it will be established the first
	// time `new Nether\Database` is called. it will then be kept open for the
	// remainder of the application for any other future calls to
	// `new Nether\Database` to reuse.

	$result = $db->Query(
		'SELECT stuff FROM table WHERE something=:something;',
		[':something' => $_GET['something']]
	);

	// queries are automatically sql injection protected via the PDO bound
	// arugment system.

	while($row = $result->Next()) {
		echo $row->stuff, PHP_EOL;
	}

	// when the result object hits the end of the results, it will automatically
	// free the resources unless it is told not to prior to iteration.

Verses (Query Builder)
--------------------------------

	$sql = new Nether\Database\Verse;
	$arg = [':something' => $_GET['something']];

	// or, if you have the database object hand you can craft a new verse from
	// that instead.

	$db = new Nether\Database;
	$sql = $db->NewVerse();
	$arg = [':something' => $_GET['something']];

	// the sql builder is still in an early state, it currently only really
	// builds mysql (yes, there are differences) and mainly for simple queries.
	// but it is still easier to format than a giant string in code.

	$sql = $db
	->NewVerse()
	->Select('from_table_name')
	->Values('field1, field2, field3')
	->Where('something=:something');

	// most of the methods accept either a single string or an array of them.

	$sql = $db
	->NewVerse()
	->Select('from_table_name')
	->Values(['field1','field2','field3'])
	->Where([
		'SearchMain' => 'something=:something'
	]);

	// if you even use an associative array like did in the WHERE above, you
	// can overwrite a specific condition or fieldset later on in the future
	// without starting the query over.

	$sql->Where([
		'SearchMain' => 'something!=:something'
	]);

	// and then you just hand the entire thing over to the database object.

	$result = $db->Query($sql,$arg);
	while($row = $result->Next()) {
		echo $row->stuff, PHP_EOL;
	}
