<?php

namespace Nether;
use \Nether;
use \PDO;

Nether\Option::Define([
	'database-connections' => [],
	'database-query-path'  => null
]);

////////////////
////////////////

class Database {

	static $DBX = [];
	/*//
	@type array
	a singleton array for holding all the connections opened by the application
	for reuse.
	//*/

	////////////////
	////////////////

	public $Driver;
	/*//
	@type PDO
	the pdo object driving this instance.
	//*/

	public $Reused = false;
	/*//
	@type boolean
	marks if the driver being used was opened by a previous object. only really
	useful for seeing if the connection recycler is working right.
	//*/

	static $ConnectTime = 0;
	static $ConnectCount = 0;
	static $ConnectReuse = 0;
	static $QueryTime = 0;
	static $QueryCount = 0;

	////////////////
	////////////////

	public function __construct($alias='Default') {

		// check if this connection is already open.
		if(array_key_exists($alias,static::$DBX)) {
			$this->Driver = static::$DBX[$alias];
			$this->Reused = true;
			++static::$ConnectReuse;
			return;
		}

		// get the requested configuration for this connection and connect.
		$ctime = microtime(true);

		$config = $this->GetConnectionConfig($alias);
		$this->Driver = new \PDO(
			$config->GetDSN(),
			$config->Username,
			$config->Password
		);

		// tell pdo to stfu, the library will do error checking.
		$this->Driver->setAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_SILENT
		);

		// keep this connection around.
		static::$DBX[$alias] = $this->Driver;

		static::$ConnectTime += microtime(true) - $ctime;
		++static::$ConnectCount;

		return;
	}

	////////////////
	////////////////

	protected function GetConnectionConfig($alias) {
	/*//
	@return Nether\Database\Connection
	return the database configuration that matches the specified alias. if it is
	not found then an exception is thrown.
	//*/

		$config = Nether\Option::Get('database-connections');

		if(!array_key_exists($alias,$config))
		throw new \Exception("no db connection found for {$alias}");

		return new Nether\Database\Connection($config[$alias]);
	}

	////////////////
	////////////////

	public function Begin() {
	/*//
	@return bool
	begin a query transaction.
	//*/

		return $this->Driver->beginTransaction();
	}

	public function Commit() {
	/*//
	@return bool
	commit a query transaction.
	//*/

		return $this->Driver->commit();
	}

	public function Rollback() {
	/*//
	@return bool
	rollback a query transaction.
	//*/

		return $this->Driver->rollBack();
	}

	public function Set($opt,$val) {
	/*//
	allow an app to set a PDO option/attribute on the database if it wants. note
	that these do not get undone next time an database object is created so if
	setting something that you only need to do for one query, be sure to unset it
	after.
	//*/

		return $this->Driver->setAttribute($opt,$val);
	}

	public function Escape($value) {
	/*//
	@argv string Value
	use the driver string escaping stuff.
	//*/
		return $this->Driver->quote($value);
	}

	////////////////
	////////////////

	public function BuildSQL() {
		return new Database\Query;
	}

	////////////////
	////////////////

	public function Query($fmt,$parm=null) {
	/*//
	@return Nether\Database\Query;
	builds a query using pdo's bound parameter stuff.
	//*/

		//echo "<p>{$fmt}</p>";

		// if given a Database\Query object and an object for the parameters then
		// fetch the named args in the query and find their matching properties
		// in the parm object.
		if(is_object($fmt) && (is_object($parm)||is_array($parm))) {
			$qarg = [];
			$parm = (object)$parm;

			foreach($fmt->GetNamedArgs() as $arg) {
				if(property_exists($parm,$arg)) $qarg[":{$arg}"] = $parm->{$arg};
				else if(property_exists($parm,":{$arg}")) $qarg["{$arg}"] = $parm->{":{$arg}"};
			}

			$parm = $qarg;
		}

		else {
			// if parm is not an array then build it as an array from all the
			// arguments that were passed to the method.
			if(!is_array($parm))
			$parm = array_slice(func_get_args(),1);
		}

		// try to prepare the statement.

		$qtime = microtime(true);

		if(!($statement = $this->Driver->prepare($fmt)))
		throw new \Exception('SQL statement was unable to be prepared.');

		// hand over a query object.
		$result =  new Nether\Database\Result($this->Driver,$statement,$parm);

		static::$QueryTime = microtime(true) - $qtime;
		static::$QueryCount++;

		return $result;
	}

}
