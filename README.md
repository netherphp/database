database
========

A database connection and query tool.

dirty how to (before i forget myself)
=====================================

setting up a new connection
---------------------------

	Nether\Option::Set('database-connections',[
		'Default' => [
			'Type'     => '%TYPE',
			'Hostname' => '%HOSTNAME',
			'Username' => '%USERNAME',
			'Password' => '%PASSWORD',
			'Database' => '%DATABASE'
		]
	]);

connecting to a defined database
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
		[':something' => 'awesome']
	);

	while($row = $result->Next()) {
		echo $row->stuff, PHP_EOL;
	}

	// when the result object hits the end of the results, it will automatically
	// free the resources unless it is told not to prior to iteration.

simple query builder
--------------------

	$sql = new Nether\Database\Query;
	$arg = [':something' => $_GET['something']];

	// queries are automatically sql injection protected via the PDO bound
	// arugment system.

	$sql
	->Select('stuff')
	->From('table')
	->Where('something=:something');

	// the sql builder is still in an early state, it currently only really
	// builds mysql (yes, there are differences) and mainly for simple queries.
	// but it is still easier to format than a giant string in code.

	$result = $db->Query($sql,$arg);
	while($row = $result->Next()) {
		echo $row->stuff, PHP_EOL;
	}


