<?php

namespace Nether\Database;

use Nether\Common;

use PDO;
use Stringable;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Connection {

	public ?string
	$Name;

	public string
	$Type;

	public string
	$Hostname;

	public string
	$Database;

	public string
	$Username;

	public string
	$Password;

	public string
	$Charset;

	public bool
	$Auto;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected PDO
	$Driver;

	protected ?Verse
	$Verse;

	protected int
	$QueryCount = 0;

	protected float
	$QueryTime = 0;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(
		string $Type,
		string $Hostname,
		string $Database,
		string $Username,
		string $Password,
		string $Charset='utf8mb4',
		?string $Name=NULL,
		bool $Auto=FALSE
	) {

		$this->Type = $Type;
		$this->Hostname = $Hostname;
		$this->Database = $Database;
		$this->Username = $Username;
		$this->Password = $Password;
		$this->Charset = $Charset;
		$this->Name = $Name;
		$this->Auto = $Auto;

		return;
	}

	public function
	__ToString():
	string {

		return $this->GetDSN();
	}

	public function
	__DebugInfo():
	array {

		return [
			'Type'        => $this->Type,
			'Hostname'    => $this->Hostname,
			'Database'    => $this->Database,
			'Username'    => Common\Values::DebugProtectValue($this->Username),
			'Password'    => Common\Values::DebugProtectValue($this->Password),
			'Charset'     => $this->Charset,
			'IsConnected' => $this->IsConnected()
		];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	IsConnected():
	bool {

		return isset($this->Driver);
	}

	public function
	Connect():
	static {

		if(isset($this->Driver))
		return $this;

		$this->Driver = new PDO(
			$this->GetDSN(),
			$this->Username,
			$this->Password
		);

		$this->Driver->SetAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_SILENT
		);

		return $this;
	}

	public function
	GetDSN():
	string {

		if($this->Type === 'mysql')
		return sprintf(
			'%s:host=%s;dbname=%s;charset=%s',
			$this->Type,
			$this->Hostname,
			$this->Database,
			$this->Charset
		);

		if($this->Type === 'sqlite')
		return sprintf(
			'%s:%s',
			$this->Type,
			$this->Database
		);

		////////

		return sprintf(
			'%s:host=%s;dbname=%s, %s, %s;charset=%s',
			$this->Type,
			$this->Hostname,
			$this->Database,
			$this->Username,
			$this->Password,
			$this->Charset
		);

	}

	public function
	GetQueryCount():
	int {

		return $this->QueryCount;
	}

	public function
	GetQueryTime():
	float {

		return $this->QueryTime;
	}

	public function
	GetInsertID():
	mixed {

		return $this->Driver->LastInsertID();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////


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

		$this->Connect();

		//ob_start();
		//var_dump($this->Driver);
		//error_log(ob_get_clean());

		// convert to an object if not an object.

		if($Argv instanceof Common\Datastore)
		$Argv = $Argv->GetData();

		if(!is_array($Argv))
		$Argv = (array)$Argv;

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
		throw new Error\QueryPrepareFailure;

		// execute the query statement.

		$Result = new Result($this, $Statement, $Dataset);

		$this->QueryCount += 1;
		$this->QueryTime += microtime(TRUE) - $QueryTime;

		return $Result;
	}

	public function
	Query_BuildDataset(string $SQL, array $Argv, array &$Dataset):
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
		$Bound = NULL;
		$Dataset = [];

		preg_match_all('/:([a-z0-9_]+)/i', $SQL, $Bound);
		$Bound = $Bound[1];

		foreach($Bound as $Binding) {

			$Param = ":{$Binding}";

			// make sure all the bindings were prefixed with the colon
			// if not already.

			if(array_key_exists($Binding, $Argv))
			$Dataset[$Param] = $Argv[$Binding];

			elseif(array_key_exists($Param, $Argv))
			$Dataset[$Param] = $Argv[$Param];

			// if an expanded binding was found, find the common argument
			// that it should match with for data expansion later.

			elseif(substr($Binding, 0, 2) === '__') {
				$Key = preg_replace('/__(.+?)__\d+/', '\\1', $Binding);
				$Param = ":{$Key}";

				if(!array_key_exists($Param, $Dataset) && array_key_exists($Key, $Argv))
				$Dataset[$Param] = $Argv[$Key];

				elseif(!array_key_exists($Param, $Dataset) && array_key_exists($Param, $Argv))
				$Dataset[$Param] = $Argv[$Param];
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
	Query_ExpandDataset(string &$SQL, array $Argv, array &$Dataset):
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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	LoadFromJSON(string $Filename):
	void {
	/*//
	@date 2022-02-19
	populate the configuration from a json file.
	//*/

		if(!file_exists($Filename))
		throw new Error\ConfigFileNotFound($Filename);

		////////

		$Data = json_decode(
			file_get_contents($Filename),
			TRUE
		);

		// @todo 2022-02-19 schema check

		//Option::Set(
		//	Database::OptDatabaseConnections,
		//	$Data
		//);

		return;
	}

}
