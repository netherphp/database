<?php

namespace Nether;

use Nether;
use Nether\Database\Error;

use Nether\Option;
use Nether\Database\Verse;
use Nether\Database\Connection;
use Nether\Database\Result;
use Nether\Database\Struct\LogQuery;

use PDO;
use Stringable;

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

Option::Define([
	'Nether.Database.Connections' => NULL,
	'Nether.Database.LogQueries'  => FALSE
]);

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class Database {
/*//
@date 2014-04-23
//*/

	static public array
	$DBO = [];
	/*//
	a singleton array for holding all the Database objects for each unique
	connection that has been opened.
	//*/

	static public array
	$DBX = [];
	/*//
	a singleton array for holding all the PDO objects for each unique
	connection that has been opened.
	//*/

	static public float
	$ConnectTime = 0.0;
	/*//
	the amount of time in seconds that spent trying to dns and connect to
	database servers.
	//*/

	static public int
	$ConnectCount = 0;
	/*//
	the number of times a socket to a database server has been opened.
	//*/

	static public int
	$ConnectReuse = 0;
	/*//
	the number of times a socket has been reused.
	//*/

	static public float
	$QueryTime = 0.0;
	/*//
	the amount of time in seconds the application has spent sending
	queries and waiting for the result.
	//*/

	static public int
	$QueryCount = 0;
	/*//
	this is the number of queries the application has made.
	//*/

	static public array
	$QueryLog = [];
	/*//
	log of all queries made if enabled.
	//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public static function
	Get(string $Alias='Default'):
	static {
	/*//
	@date 2014-04-23
	get a database connection by configuration alias. uses a cache to try
	and help with performance and resource use.
	//*/

		// if we already have an item created here then we shall reuse it.

		if(array_key_exists($Alias, static::$DBO))
		if(static::$DBO[$Alias] instanceof Nether\Database)
		return static::$DBO[$Alias];

		// else we will create a new one.

		return (static::$DBO[$Alias] = new static($Alias));
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected string
	$Alias;
	/*//
	@date 2022-02-18
	configuration alias for this database connection.
	//*/

	protected PDO
	$Driver;
	/*//
	@date 2022-02-18
	the pdo object driving this instance.
	//*/

	protected bool
	$Reused = FALSE;
	/*//
	@date 2022-02-18
	if this object is reusing a previous connection.
	//*/

	protected ?Verse
	$Verse = NULL;
	/*//
	@date 2022-02-18
	reference to the last Verse that was used.
	//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	OptDatabaseConnections = 'Nether.Database.Connections',
	OptLogQueries = 'Nether.Database.LogQueries';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Alias='Default') {
	/*//
	@date 2022-02-18
	//*/

		$this->Alias = $Alias;

		////////

		// see if a connection can be reused and if so do so.

		if(array_key_exists($Alias, static::$DBX))
		if(static::$DBX[$Alias] instanceof PDO) {
			$this->Driver = static::$DBX[$Alias];
			$this->Reused = TRUE;
			static::$ConnectReuse++;
			return;
		}

		////////

		// connect to a server.

		$ConnectTime = microtime(TRUE);
		$Config = $this->GetConnectionConfig($Alias);

		$this->Driver = new PDO(
			$Config->GetDSN(),
			$Config->Username,
			$Config->Password
		);

		// tell pdo to shut the hell up so we can handle the errors
		// more gracefully.

		$this->Driver->SetAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_SILENT
		);

		////////

		// register this connection.

		static::$DBX[$Alias] = $this->Driver;
		static::$ConnectTime += microtime(TRUE) - $ConnectTime;
		static::$ConnectCount++;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetAlias():
	string {
	/*//
	@date 2022-02-20
	//*/

		return $this->Alias;
	}

	public function
	GetDriver():
	PDO {
	/*//
	@date 2022-02-18
	get access to the pdo driver object.
	//*/

		return $this->Driver;
	}

	public function
	GetDriverName():
	string {
	/*//
	@date 2022-02-18
	fetch the name of the pdo driver currently in use.
	//*/

		return $this->Driver->GetAttribute(PDO::ATTR_DRIVER_NAME);
	}

	public function
	GetVerse():
	Verse {
	/*//
	@date 2022-02-18
	get the previous verse, or a new one if none.
	//*/

		if($this->Verse)
		return $this->Verse;

		return $this->NewVerse();
	}

	public function
	NewVerse():
	Verse {
	/*//
	@date 2022-02-18
	begin a new verse.
	//*/

		$this->Verse = new Verse($this);
		return $this->Verse;
	}

	public function
	NewVerseInsert(string $ClassName):
	Verse {
	/*//
	@date 2022-02-18
	//*/

		$this->Verse = Verse::FromMetaInsert($ClassName, $this);
		return $this->Verse;
	}

	public function
	NewVerseSelect(string $ClassName):
	Verse {
	/*//
	@date 2022-02-18
	//*/

		$this->Verse = Verse::FromMetaSelect($ClassName, $this);
		return $this->Verse;
	}

	public function
	NewVerseDelete(string $ClassName):
	Verse {
	/*//
	@date 2022-02-18
	//*/

		$this->Verse = Verse::FromMetaDelete($ClassName, $this);
		return $this->Verse;
	}

	public function
	NewVerseUpdate(string $ClassName):
	Verse {
	/*//
	@date 2022-02-18
	//*/

		$this->Verse = Verse::FromMetaUpdate($ClassName, $this);
		return $this->Verse;
	}

	public function
	NewVerseCreate(string $ClassName):
	Verse {
	/*//
	@date 2022-02-18
	//*/

		$this->Verse = Verse::FromMetaCreate($ClassName, $this);
		return $this->Verse;
	}

	public function
	NewVerseDropTable(string $ClassName):
	Verse {
	/*//
	@date 2022-02-18
	//*/

		$this->Verse = Verse::FromMetaDropTable($ClassName, $this);
		return $this->Verse;
	}

	public function
	IsReused():
	bool {
	/*//
	@date 2022-02-18
	if this object is reusing a previous connection.
	//*/

		return $this->Reused;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetConnectionConfig(string $Alias):
	Connection {
	/*//
	@date 2022-02-18
	fetch the specified configuration. if none is found then it will throw an
	exception instead.
	//*/

		$Config = Option::Get(static::OptDatabaseConnections);

		// complain if not found.

		if(!is_array($Config))
		throw new Nether\Database\Error\InvalidConfig($Alias);

		if(!array_key_exists($Alias, $Config))
		throw new Nether\Database\Error\InvalidConfig($Alias);

		// if config was already using connection object just return it.

		if($Config[$Alias] instanceof Connection)
		return $Config[$Alias];

		// else convert basic array into connection object.

		return new Connection($Config[$Alias]);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Begin():
	bool {
	/*//
	@date 2022-02-18
	begin a query transaction.
	//*/

		return $this->Driver->BeginTransaction();
	}

	public function
	Commit():
	bool {
	/*//
	@date 2022-02-18
	commit a query transaction.
	//*/

		return $this->Driver->Commit();
	}

	public function
	Rollback():
	bool {
	/*//
	@date 2022-02-18
	rollback a query transaction.
	//*/

		return $this->Driver->Rollback();
	}

	public function
	Close():
	void {
	/*//
	@date 2022-02-18
	close the database connection.
	//*/

		// kill db object cache.

		static::$DBO[$this->Alias] = NULL;
		unset(static::$DBO[$this->Alias]);

		// kill pdo.

		$this->Driver = NULL;
		static::$DBX[$this->Alias] = NULL;
		unset(static::$DBX[$this->Alias]);

		return;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	GetAttr(string $Key):
	mixed {
	/*//
	@date 2022-02-18
	fetch a PDO option/attribute from the current connection.
	//*/

		return $this->Driver->GetAttribute($Key);
	}

	public function
	SetAttr(string $Key, mixed $Value):
	bool {
	/*//
	@date 2022-02-18
	set a PDO option/attribute on the current connection.
	//*/

		return $this->Driver->SetAttribute($Key, $Value);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Escape(string $Value):
	string {
	/*//
	@date 2022-02-18
	use the PDO driver to escape data for the current connection.
	//*/

		return $this->Driver->Quote($Value);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Query(string $Format, array|object $Argv=[]):
	Result {
	/*//
	@date 2022-02-18
	execute a query and return a result.
	//*/

		$Statement = NULL;
		$SQL = NULL;
		$Dataset = [];
		$QueryTime = 0;

		// convert to an object if not an object.

		if(!is_object($Argv))
		$Argv = (object)$Argv;

		// handle if we gave it an object that implements stringable.
		// mostly for use with Verse but you could send your own object
		// in here as well.

		if($Format instanceof Stringable)
		$SQL = (string)$Format;

		else
		$SQL = $Format;

		// build a dataset that directly maps to the bound parameters in
		// the query with no unused values.

		$this->Query_BuildDataset($SQL, $Argv, $Dataset);
		$this->Query_ExpandDataset($SQL, $Argv, $Dataset);

		// prepare the query statement.

		$QueryTime = microtime(TRUE);
		$Statement = $this->Driver->Prepare($SQL);

		if(!$Statement)
		throw new Nether\Database\Error\QueryPrepareFailure;

		// execute the query statement.

		$Result = new Result($this, $Statement, $Dataset);

		if(Option::Get(static::OptLogQueries))
		static::$QueryLog[] = new LogQuery(
			Time: $Result->GetTime(),
			Query: $Result->GetQuery(),
			Input: $Result->GetArgs(),
			Count: $Result->GetCount(),
			Trace: static::GetDebugTrace()
		);

		static::$QueryTime += microtime(TRUE) - $QueryTime;
		static::$QueryCount++;

		return $Result;
	}

	public function
	Query_BuildDataset(string $SQL, object $Argv, array &$Dataset):
	void {
	/*//
	@modifies $Dataset
	@date 2022-02-18
	fetch an array which describes all the bound data in this query and
	relate it to the arguments which were passed to query. expands arrays
	that are bound to a single param into multiple params, and detects
	pre-expanded arguments and binds them to their argument.
	//*/

		$Binding = NULL;
		$Param = NULL;
		$Arg = NULL;
		$Key = NULL;

		$Bound = static::GetNamedArgs($SQL);
		$Dataset = [];

		foreach($Bound as $Binding) {

			$Param = ":{$Binding}";

			// make sure all the bindings were prefixed with the colon
			// if not already.

			if(property_exists($Argv, $Binding))
			$Dataset[$Param] = $Argv->{$Binding};

			elseif(property_exists($Argv, $Param))
			$Dataset[$Param] = $Argv->{$Param};

			// if an expanded binding was found, find the common argument
			// that it should match with for data expansion later.

			elseif(substr($Binding, 0, 2) === '__') {
				$Key = preg_replace('/__(.+?)__\d+/', '\\1', $Binding);
				$Param = ":{$Key}";

				if(!array_key_exists($Param, $Dataset) && property_exists($Argv, $Key))
				$Dataset[$Param] = $Argv->{$Key};

				elseif(!array_key_exists($Param, $Dataset) && property_exists($Argv, $Param))
				$Dataset[$Param] = $Argv->{$Param};
			}

		}

		// arguments which were not bound to a named argument just get
		// added on to the dataset letting the keys inc as they may. this
		// is to support the older ? style of binding token.

		foreach($Argv as $Key => $Arg)
		if(is_int($Key))
		$Dataset[] = $Arg;

		return;
	}

	public function
	Query_ExpandDataset(string &$SQL, object $Argv, array &$Dataset):
	void {
	/*//
	@modifies $SQL
	@modifies $Dataset
	@date 2022-02-18
	iterate over a dataset to find any arrays that need to be expanded into
	flat values to match up with any expanded bindings. so if there is a
	parameter named :ObjectID and it is an array in the dataset, it will be
	expanded into :__ObjectID__0, :__ObjectID__1, etc.
	//*/

		$Key = NULL;
		$Value = NULL;
		$NewKey = NULL;
		$K = NULL;
		$V = NULL;

		foreach($Dataset as $Key => $Value) {
			if(!is_array($Value))
			continue;

			// explode an array into multiple tokens.

			$NewKey = str_replace(':', ':__', $Key);
			$NewBindings = [];

			foreach($Value as $K => $V) {
				$NewBindings[] = $Binding = "{$NewKey}__{$K}";
				$Dataset[$Binding] = $V;
			}

			// update the query string and dataset array for the updated
			// exploded bindings.

			$SQL = str_replace(
				$Key,
				implode(',', $NewBindings),
				$SQL
			);

			unset($Dataset[$Key]);
		}

		return;
	}

	////////////////////////////////
	////////////////////////////////

	static public function
	GetNamedArgs(string $Input):
	array {
	/*//
	@date 2022-02-18
	find the named arguments that were in the final query.
	//*/

		$Match = NULL;

		preg_match_all(
			'/:([a-z0-9_]+)/i',
			$Input,
			$Match
		);

		return $Match[1];
	}

	static public function
	GetDebugTrace():
	array {
	/*//
	@date 2022-02-18
	@todo 2022-02-18 optimize this to not use array_shift.
	fetch some info we can use in the query log to descirbe where a query was
	made from.
	//*/

		$Trace = NULL;

		$Result = debug_backtrace();
		$Length = count($Result);
		$Iter = 0;
		$Trace = NULL;
		$Output = [];

		for($Iter = 2; $Iter < $Length; $Iter++) {
			$Trace = (array)$Result[$Iter];

			if(array_key_exists('class', $Trace) && array_key_exists('object', $Trace))
			$Output[] = "{$Trace['class']}->{$Trace['function']}";

			elseif(array_key_exists('class', $Trace))
			$Output[] = "{$Trace['class']}::{$Trace['function']}";

			else
			$Output[] = $Trace['function'];
		}

		return $Output;
	}

}
