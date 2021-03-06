<?php

namespace Nether;
use \Nether;
use \PDO;

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

Nether\Option::Define([
	'nether-database-connections' => [],
	'nether-database-query-log'   => false
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
	cause DBO['Default'] to contain a reference to that Database instance. you
	can use this to use a more dependency injection friendly style of coding
	if you do not want to new Database in each method you need db access. your
	unit tests can then store a mock in DBO['Default']. see the static Get
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

	static
	$QueryLog = [];
	/*//
	@type array
	an array holding all the log entries thusfar if enabled.
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
		if(static::$DBO[$Alias] instanceof Nether\Database)
		return static::$DBO[$Alias];

		// else we will create a new one.
		return static::$DBO[$Alias] = new static($Alias);
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Alias = NULL;
	/*//
	@date 2018-06-22
	//*/

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

		$this->Alias = $Alias;

		if(array_key_exists($Alias,static::$DBX)) {
			if(static::$DBX[$Alias] instanceof PDO) {
				// reuse the existing driver if available and
				// then we are done here.
				$this->Driver = static::$DBX[$Alias];
				$this->Reused = true;
				static::$ConnectReuse++;
				return;
			}
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

	public function
	Close() {
	/*//
	@return void
	@date 2018-06-22
	close a database connection. technically it is only an attempt to close.
	the probably most dumb thing about pdo is that it ref counts with no
	method to forecably kill off connections. if you have any living instances
	then the db will not really close. but that problem exists regardless of
	you use nether or not. in this case though we at least will force a new
	connection next invocation.
	//*/

		// kill pdo.
		$this->Driver = NULL;
		static::$DBX[$this->Alias] = NULL;
		unset(static::$DBX[$this->Alias]);

		// kill db object cache.
		static::$DBO[$this->Alias] = NULL;
		unset(static::$DBO[$this->Alias]);

		return;
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

		$SQL = (is_object($Format))?("{$Format}"):($Format);
		$Dataset = [];

		$this->Query_BuildDataset($SQL,$Argv,$Dataset);
		$this->Query_ExpandDataset($SQL,$Argv,$Dataset);

		////////
		// and then perform the query.

		$QueryTime = microtime(true);

		if(!($Statement = $this->Driver->Prepare($SQL)))
		throw new Nether\Database\Error\QueryPrepareFailure;

		$Result = new Database\Result($this,$Statement,$Dataset);
		if(Nether\Option::Get('nether-database-query-log')) {
			static::$QueryLog[] = (object)[
				'Time' => round($Result->GetTime(),4),
				'Query' => $Result->GetQuery(),
				'Input' => $Result->GetArgs(),
				'Count' => $Result->GetCount(),
				'Trace' => static::GetDebugTrace()
			];
		}

		static::$QueryTime += microtime(true) - $QueryTime;
		static::$QueryCount++;

		return $Result;
	}

	public function
	Query_BuildDataset(&$SQL,&$Argv,&$Dataset) {
	/*//
	fetch an array which describes all the bound data in this query and
	relate it to the arguments which were passed to query. expands arrays
	that are bound to a single param into multiple params, and detects
	pre-expanded arguments and binds them to their argument.
	//*/

		$Bound = static::GetNamedArgs($SQL);
		$Dataset = [];

		////////////////////////////////
		// handle named arguments

		foreach($Bound as $Binding) {

			// if a argument without the prefix was passed then auto prefix
			// it in the dataset to match the binding.
			if(property_exists($Argv,$Binding)) {
				$Dataset[":{$Binding}"] = $Argv->{$Binding};
			}

			// if a 1:1 binding to arugment was found then just use it flat.
			elseif(property_exists($Argv,":{$Binding}")) {
				$Dataset[":{$Binding}"] = $Argv->{":{$Binding}"};
			}

			// if an expanded binding was found, find the common argument
			// that it should match with for data expansion later.
			elseif(substr($Binding,0,2) === '__') {
				$Key = preg_replace('/__(.+?)__\d+/','\\1',$Binding);

				if(!array_key_exists(":{$Key}",$Dataset) && property_exists($Argv,$Key))
				$Dataset[":{$Key}"] = $Argv->{$Key};

				elseif(!array_key_exists(":{$Key}",$Dataset) && property_exists($Argv,":{$Key}"))
				$Dataset[":{$Key}"] = $Argv->{":{$Key}"};
			}

		} unset($Binding);

		////////////////////////////////
		// handle anonymous arguments.

		foreach($Argv as $Key => $Arg) {
			if(is_int($Key))
			$Dataset[] = $Arg;
		} unset($Key,$Arg);

		return $Dataset;
	}

	public function
	Query_ExpandDataset(&$SQL,&$Argv,&$Dataset) {
	/*//
	iterate over a dataset to find any arrays that need to be expanded into
	flat values to match up with any expanded bindings. so if there is a
	parameter named :ObjectID and it is an array in the dataset, it will be
	expanded into :__ObjectID__0, :__ObjectID__1, etc.
	//*/

		foreach($Dataset as $Key => $Value) {
			if(!is_array($Value)) continue;
			$NewKey = str_replace(':',':__',$Key);

			$NewBindings = [];
			foreach($Value as $K => $V) {
				$NewBindings[] = $Binding = "{$NewKey}__{$K}";
				$Dataset[$Binding] = $V;
			}

			$SQL = str_replace($Key,implode(',',$NewBindings),$SQL);
			unset($Dataset[$Key]);
		} unset($Key,$Value,$K,$V,$NewBindings,$Binding);

		return $Dataset;
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

		preg_match_all('/:([a-z0-9_]+)/i',$Input,$Match);
		return $Match[1];
	}

	static public function
	GetDebugTrace() {
	/*//
	fetch some info we can use in the query log to descirbe where a query was
	made from.
	//*/

		$Result = debug_backtrace();
		$Output = [];

		array_shift($Result);
		array_shift($Result);
		foreach($Result as $Trace) {
			if(array_key_exists('class',$Trace) && array_key_exists('object',$Trace))
			$Output[] = "{$Trace['class']}->{$Trace['function']}";

			elseif(array_key_exists('class',$Trace))
			$Output[] = "{$Trace['class']}::{$Trace['function']}";

			else
			$Output[] = $Trace['function'];
		}

		return $Output;
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
