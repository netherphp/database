<?php

namespace Nether;
use \Nether;
use \PDO;

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

Nether\Option::Define([
	'nether-database-connections' => []
]);

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class Database {
/*//
this class provides all the functionality and primary interface for interacting
with database things.

for simple code you can new Nether\Database($Alias) any time you need access.

for more complex code where you may want to perform dependency injection you
will want to use Nether\Database::Get($Alias) instead.
//*/

	static
	$DBO = [];
	/*//
	@type array
	a singleton array for holding all the Database objects for each unique
	connection that has been opened. example new Database('Default') will
	cause DBC['Default'] to contain a reference to that Database instance. you
	can use this to use a more dependency injection friendly style of coding
	if you do not want to new Database in each method you need db access. your
	unit tests can then store a mock in DBC['Default']. see the static Get
	method for info on how the other half of this works - because you do not
	want to have to array_key_exists this yourself every time you need db.
	//*/

	static
	$DBX = [];
	/*//
	@type array
	a singleton array for holding all the connections opened by the application
	for reuse. everything in this list will be the raw pdo connection.
	//*/

	static
	$ConnectTime = 0.0;
	/*//
	@type float
	this is the amount of time in seconds that your application (mainly this
	library) spent connecting to servers. high values here mean your servers
	are taking too long to establish a connection to database servers.
	//*/

	static
	$ConnectCount = 0;
	/*//
	@type int
	the number of times you have connected to a database server throughout the
	course of your application. a high number (for most apps, greater than 1)
	in this stat means you are connecting to way too many damn servers, or that
	your persistant connections are not persistanting.
	//*/

	static
	$ConnectReuse = 0;
	/*//
	@type int
	the number of times this connection has been reused. you can pretty much
	consider this a count of how many times you `new Nether\Database`d. a high
	value in this stat means the persistant connection is working well.
	//*/

	static
	$QueryTime = 0.0;
	/*//
	@type float
	this is the amount of time in seconds your application has spent querying
	databases it has connected to. a high value in this field means that you
	may be running some very slow queries.
	//*/

	static
	$QueryCount = 0;
	/*//
	@type int
	this is the number of queries your application has made to databases it has
	connected to. a high value in this field probably means your cache layer
	has most likely literally exploded. should have protected that exhaust port
	better.
	//*/

	////////////////////////////////
	////////////////////////////////

	public static function
	Get($Alias='Default') {
	/*//
	@return Nether\Database
	fetch a database object which has already been created once before. unless
	there was not, in which case create one, save it, then hand it back. this
	is the other half of what will make dependency injection work - call this
	to get your database handle instead of "new Nether\Database" - then in your
	unit tests you can Nether\Database::$DBO[$Alias] = a mock prior to testing.
	//*/

		// if we already have an item created here then we shall reuse it.
		if(array_key_exists($Alias,static::$DBO))
		return static::$DBO[$Alias];

		// else we will create a new one.
		return static::$DBO[$Alias] = new static($Alias);
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Driver = null;
	/*//
	@type \PDO
	the pdo object driving this instance.
	//*/

	public function
	GetDriver() {
	/*//
	@return \PDO
	give you access to the pdo driver object in case you need to go all lower
	level in this shit.
	//*/

		return $this->Driver;
	}

	public function
	GetDriverName() {
	/*//
	@type string
	fetch the name of the pdo driver currently in use.
	//*/

		return $this->Driver->GetAttribute(PDO::ATTR_DRIVER_NAME);
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Verse = null;
	/*//
	@type Nether\Database\Verse
	the last verse object we used.
	//*/

	public function
	GetVerse() {
	/*//
	@return Nether\Database\Verse
	the last verse object used. if no versen hath been used then one shall be
	created and remembered for you.
	//*/

		if(!$this->Verse)
		$this->Verse = $this->NewVerse();

		return $this->Verse;
	}

	public function
	NewVerse() {
	/*//
	@return Nether\Database\Verse
	begin a new query verse. remembers the last one used because that seemed
	like a feature that could be useful later.
	//*/

		$this->Verse = new Nether\Database\Verse($this);
		return $this->Verse;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	GetCoda($Class) {
	/*//
	@alias Nether\Database::NewCoda
	provide consistancy between Verse and Coda API.
	//*/

		return $this->NewCoda($Class);
	}

	public function
	NewCoda($Class) {
	/*//
	@return Nether\Database\Coda
	begin a new query coda.
	//*/

		if(strpos($Class,'\\') !== 0)
		$Class = "Nether\\Database\\Coda\\{$Class}";

		if(!class_exists($Class))
		throw new \Exception("requested coda {$Class} not found.");

		$Coda = new $Class;
		$Coda->SetDatabase($this);
		return $Coda;
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Reused = false;
	/*//
	@type boolean
	marks if the driver being used was opened by a previous object. only really
	useful for seeing if the connection recycler is working right.
	//*/

	public function
	IsReused() {
	/*//
	@return boolean
	return if this connection has been reused (true) or is a fresh
	connection (false).
	//*/

		return $this->Reused;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	__construct($Alias='Default') {
	/*//
	upon object construction we will check if the connection you requested
	has already been made, and if so, reuse that driver object automatically.
	else it will establish a new connection and shove it off so that the
	above is true the next time you create an instance of this.
	//*/

		if(array_key_exists($Alias,static::$DBX)) {
			// reuse the existing driver if available and
			// then we are done here.
			$this->Driver = static::$DBX[$Alias];
			$this->Reused = true;
			static::$ConnectReuse++;
			return;
		}

		////////
		////////

		$ConnectTime = microtime(true);
		$Config = $this->GetConnectionConfig($Alias);

		$this->Driver = new PDO(
			$Config->GetDSN(),
			$Config->Username,
			$Config->Password
		);

		$this->Driver->SetAttribute(
			// tell pdo to stfu about errors so that we can
			// check for them and present them better.
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_SILENT
		);

		////////
		////////

		static::$DBX[$Alias] = $this->Driver;
		static::$ConnectTime += microtime(true) - $ConnectTime;
		static::$ConnectCount++;
		return;
	}

	////////////////////////////////
	////////////////////////////////

	protected function
	GetConnectionConfig($Alias) {
	/*//
	@return Nether\Database\Connection
	fetch the specified configuration. if none is found then it will throw an
	exception instead.
	//*/

		$Config = Nether\Option::Get('nether-database-connections');

		if(!array_key_exists($Alias,$Config))
		throw new Nether\Database\Error\InvalidConfig($Alias);

		if($Config[$Alias] instanceof Nether\Database\Connection)
		return $Config[$Alias];

		return new Nether\Database\Connection($Config[$Alias]);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Begin() {
	/*//
	@return bool
	begin a query transaction.
	//*/

		return $this->Driver->BeginTransaction();
	}

	public function
	Commit() {
	/*//
	@return bool
	commit a query transaction.
	//*/

		return $this->Driver->Commit();
	}

	public function
	Rollback() {
	/*//
	@return bool
	rollback a query transaction.
	//*/

		return $this->Driver->Rollback();
	}

	////////////////////////////////
	////////////////////////////////

	public function
	GetAttr($Key) {
	/*//
	@return mixed
	fetch a PDO option/attribute from the current connection.
	//*/

		return $this->Driver->GetAttribute($Key);
	}

	public function
	SetAttr($Key,$Value) {
	/*//
	@return bool
	set a PDO option/attribute on the current connection.
	//*/

		return $this->Driver->SetAttribute($Key,$Value);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Escape($Value) {
	/*//
	@argv string Value
	use the PDO driver to escape data for the current connection.
	//*/

		return $this->Driver->Quote($Value);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Query($Format,$Argv=false) {
	/*//
	@return Nether\Database\Result
	//*/

		if($Argv && !is_array($Argv) && !is_object($Argv))
		throw new Nether\Database\Error\InvalidQueryInput;

		// convert to an object if not an object. this would theoretically
		// allow you to supply objects with magic methods.

		if(!is_object($Argv))
		$Argv = (object)$Argv;

		////////
		// build a dataset that directly maps to the bound parameters in the
		// query with no unused values.

		$Dataset = [];
		$SQL = (is_object($Format))?("{$Format}"):($Format);
		$Bound = static::GetNamedArgs($SQL);

		// fetch the named data.
		foreach($Bound as $Binding) {
			if(property_exists($Argv,$Binding))
			$Dataset[":{$Binding}"] = $Argv->{$Binding};

			elseif(property_exists($Argv,":{$Binding}"))
			$Dataset[":{$Binding}"] = $Argv->{":{$Binding}"};
		} unset($Binding);

		// then the anonymous data.
		foreach($Argv as $Key => $Arg) {
			if(is_int($Key))
			$Dataset[] = $Arg;
		} unset($Key,$Arg);

		////////
		// convert any arrays into bound argument listings. this is mainly to
		// make things like IN clauses stuper stafe too.

		foreach($Dataset as $Key => $Value) {
			if(!is_array($Value)) continue;

			$NewBindings = [];
			foreach($Value as $K => $V) {
				$NewBindings[] = $Binding = "{$Key}__{$K}";
				$Dataset[$Binding] = $V;
			}

			$SQL = str_replace($Key,implode(',',$NewBindings),$SQL);
			unset($Dataset[$Key]);

		} unset($Key,$Value,$K,$V,$NewBindings,$Binding);

		////////
		// and then perform the query.

		$QueryTime = microtime(true);

		if(!($Statement = $this->Driver->Prepare($SQL)))
		throw new Nether\Database\Error\QueryPrepareFailure;

		$Result = new Database\Result($this,$Statement,$Dataset);
		static::$QueryTime = microtime(true) - $QueryTime;
		static::$QueryCount++;

		return $Result;
	}

	////////////////////////////////
	////////////////////////////////

	static public function
	GetNamedArgs($Input) {
	/*//
	@argv string Input
	@return array[string, ...]
	find out all the named arguments that were in the final query.
	//*/

		preg_match_all('/:([a-z0-9]+)/i',$Input,$Match);
		return $Match[1];
	}

	////////////////////////////////
	////////////////////////////////
	////////////////////////////////
	////////////////////////////////
	////////////////////////////////
	////////////////////////////////

	// things below here need to vanish soon. they have already been replaced
	// by better things.

	public function QueryOld($fmt,$parm=null) {
	/*//
	@depreciated obviously
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
