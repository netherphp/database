<?php

namespace Nether\Database;
use \Nether;

class Query {

	protected $SQL;

	////////////////
	////////////////

	public function __construct($file=null) {

		// if no filename was specified we will silently stop assuming that we are
		// going to be using the query builder (which does not yet exist)
		if(!$file) return;
		else $this->Load($file);

		return;
	}

	public function __toString() {
		$this->GenerateSQL();
		return $this->SQL;
	}

	////////////////
	////////////////

	protected function LoadFromCache($file) {
	/*//
	@return boolean
	try and load the requested query file from cache. returns boolean if unable
	because not having a query cached is not fatal.
	//*/

		if(!class_exists('Nether\\Stash') || !class_exists('Nether\\Cache'))
		return false;

		$cache = Nether\Stash::Get('cache');
		$key = "nether-database-query-file-{$file}";

		if($c = $cache->Get($key)) {
			return $c->Data;
		} else {
			return false;
		}
	}

	protected function LoadFromDisk($file) {
	/*//
	try and load the requested query file from disk. throws exceptions if unable
	because not having a query is fatal.
	//*/

		if(!class_exists('Nether\\Stash') || !class_exists('Nether\\Cache'))
		return false;

		$cache = Nether\Stash::Get('cache');
		$key = "nether-database-query-file-{$file}";
		$path = Nether\Option::Get('database-query-path');
		$queryfile = m_repath_fs("{$path}/{$file}.sql");

		// make sure this option was defined so that we know where to look for
		// query files.
		if(!$path)
		throw new \Exception('no database-query-path defined');

		// make sure the directory exists and is readable.
		if(!is_dir($path) || !is_readable($path))
		throw new \Exception('specified database-query-path does not exist or not readable');

		// make sure the requested file exists and is readable.
		if(!file_exists($queryfile) || !is_readable($queryfile))
		throw new \Exception('specified query file not found or not readable');

		// read the query file.
		$this->SQL = file_get_contents($queryfile);

		// cache the query file.
		$cache->Set($key,$this->SQL,[
			'Appcache' => true,
			'Memcache' => false,
			'Diskcache' => false
		]);

		return;
	}

	protected function Load($file) {
	/*//
	loads a query file into the object.
	//*/

		if(!$this->LoadFromCache($file))
		$this->LoadFromDisk($file);

		return;
	}

	////////////////
	////////////////

	protected $Mode = null;
	/*//
	@type string
	the type of sql query we are building. (select, insert, update, delete).
	//*/

	protected $Fields = [];
	protected $Tables = [];
	protected $Joins = [];
	protected $Conditions = [];
	protected $Sorts = [];
	protected $Groups = [];
	protected $Count;
	protected $Offset;

	////////////////
	////////////////

	public function ResetProperties() {
		$this->SQL = null;
		$this->Fields = [];
		$this->Tables = [];
		$this->Joins = [];
		$this->Conditions = [];
		$this->Sorts = [];
		$this->Count = 0;
		$this->Offset = 0;
		return;
	}

	public function Select() {
	/*//
	@argv string Field, ...
	@return object
	put the query object into SELECT mode and mark down which fields we wanted
	to pull.
	//*/

		$this->Mode = 'SELECT';
		$this->ResetProperties();

		$this->Fields = array_merge(
			$this->Fields,
			func_get_args()
		);

		return $this;
	}

	public function Delete($table) {
	/*//
	@argv string Table
	@return object
	put the query object into DELETE mode and mark down which table to delete
	from.
	//*/

		$this->Mode = 'DELETE';
		$this->ResetProperties();

		$this->Tables[] = $table;
		return $this;
	}

	public function Update($table) {
	/*//
	@argv string Table
	@return object
	put the query into UPDATE mode and mark down which tables to update.
	//*/

		$this->Mode = 'UPDATE';
		$this->ResetProperties();

		$this->Tables[] = $table;
		return $this;
	}

	public function Insert($table) {
	/*//
	@argv string Table
	@return object
	put the query into INSERT mode and mark down which table to insert into.
	//*/

		$this->Mode = 'INSERT';
		$this->ResetProperties();

		$this->Tables[] = $table;
		return $this;
	}

	////////////////
	////////////////

	public function From() {
	/*//
	@argv string Table, ...
	@return object
	mark down which tables we want to query against.
	//*/

		$this->Tables = array_merge(
			$this->Tables,
			func_get_args()
		);

		return $this;
	}

	public function LeftJoin() {
	/*//
	@argv string Table, ...
	@return object
	mark down tables to LEFT JOIN against.
	//*/

		foreach(func_get_args() as $arg)
		$this->Joins[] = "LEFT JOIN {$arg}";

		return $this;
	}

	public function InnerJoin() {
	/*//
	@argv string Table, ...
	@return object
	mark down tables to INNER JOIN against.
	//*/

		foreach(func_get_args() as $arg)
		$this->Joins[] = "INNER JOIN {$arg}";

		return $this;
	}

	public function RightJoin() {
	/*//
	@argv string Table,
	@return object
	mark down tables to RIGHT JOIN against.
	//*/

		foreach(func_get_args() as $arg)
		$this->Joins[] = "RIGHT JOIN {$arg}";

		return $this;
	}

	public function NaturalJoin() {
	/*//
	@argv string Table, ...
	@return object
	mark down tables to NATURAL JOIN against.
	//*/

		foreach(func_get_args() as $arg)
		$this->Joins[] = "NATURAL JOIN {$arg}";

		return $this;
	}

	public function NaturalLeftJoin() {
	/*//
	@argv string Table, ...
	@return object
	mark down tables to NATURAL LEFT JOIN against.
	//*/

		foreach(func_get_args() as $arg)
		$this->Joins[] = "NATURAL LEFT JOIN {$arg}";

		return $this;
	}

	public function NaturalRightJoin() {
	/*//
	@argv string Table, ...
	@return object
	mark down tables to NATURAL right JOIN against.
	//*/

		foreach(func_get_args() as $arg) {
			$this->Joins[] = "NATURAL RIGHT JOIN {$arg}";
		}

		return $this;
	}

	public function Where() {
	/*//
	@argv string Condition, ...
	@return object
	mark down a condition for the WHERE clause.
	//*/

		$this->Conditions = array_merge(
			$this->Conditions,
			func_get_args()
		);

		return $this;
	}

	public function AndWhere() {
	/*//
	@argv string Condition
	@return object
	mark down an additional condition for the WHERE clause via AND.
	//*/

		foreach(func_get_args() as $arg) {
			$this->Where("AND {$arg}");
		}

		return $this;
	}

	public function OrWhere() {
	/*//
	@argv string Condition
	@return object
	mark down an additional condition for the WHERE clause via OR.
	//*/

		foreach(func_get_args() as $arg) {
			$this->Where("OR {$arg}");
		}

		return $this;
	}

	public function OrderBy() {
	/*//
	@argv string Sort, ...
	@return object
	mark down sort constructs.
	//*/

		$this->Sorts = array_merge(
			$this->Sorts,
			func_get_args()
		);

		return $this;
	}

	public function GroupBy() {
	/*//
	@argv string Group, ...
	@return object
	mark down group constructs.
	//*/

		$this->Groups = array_merge(
			$this->Groups,
			func_get_args()
		);

		return $this;
	}

	public function Set() {
	/*//
	@argv string SetStatement, ...
	@return object
	mark down the SET statements for this query.
	//*/

		foreach(func_get_args() as $arg) {
			if(is_array($arg))
			$this->Fields = array_merge(
				$this->Fields,
				$arg
			);

			else
			$this->Fields[] = $arg;
		}

		return $this;
	}

	public function Limit($count,$offset=null) {
	/*//
	@argv int Count, int Offset
	@return object
	mark down the count and offset for the LIMIT statement.
	//*/

		$this->Count = $count;
		$this->Offset = $offset;

		return $this;
	}

	public function Values() {
	/*//
	@argv array ArgPairList
	@argv string Field, mixed Value
	mark down the fields/values for an INSERT query.
	//*/

		$argc = func_num_args();
		$argv = func_get_args();

		switch($argc) {
			case 1: {
				if(is_array($argv[0])) {
					$this->Fields = array_merge($this->Fields,$argv[0]);
				}
				break;
			}
			case 2: {
				$this->Fields[$argv[0]] = $argv[1];
				break;
			}
		}

	}

	////////////////
	////////////////

	public function GetNamedArgs() {
		$this->GenerateSQL();

		preg_match_all('/:([a-z0-9]+)/i',$this->SQL,$match);
		return $match[1];
	}

	////////////////
	////////////////

	protected function GenerateSQL() {
	/*//
	given all the data so far generate an SQL query string that can be used by
	any database utility which accepts SQL. lol. SQL. SQL SQL, SQL. the generated
	string is stored in the SQL property on this class. which is private. that is
	why this class also has __toString. hint bloody hint.
	//*/

		// make sure all the WHERE conditions are joinable.
		$first = true;
		foreach($this->Conditions as $key => $val) {
			if($first) { $first = false; continue; }

			if(!preg_match('/^ ?(?:AND|OR) /i',$val))
			$this->Conditions[$key] = "AND {$val}";
		}

		switch($this->Mode) {
			case 'SELECT': { $this->GenerateSelectSQL(); break; }
			case 'UPDATE': { $this->GenerateUpdateSQL(); break; }
			case 'INSERT': { $this->GenerateInsertSQL(); break; }
			case 'DELETE': { $this->GenerateDeleteSQL(); break; }
			default: { $this->SQL = ''; }
		}

		return;
	}

	protected function GenerateSelectSQL() {
	/*//
	generate a SELECT style query.
	//*/

		$this->SQL = sprintf('SELECT %s ',join(',',$this->Fields));
		$this->SQL .= sprintf('FROM %s ',join(',',$this->Tables));

		if(count($this->Joins))
		$this->SQL .= sprintf('%s ',join(' ',$this->Joins));

		if(count($this->Conditions))
		$this->SQL .= sprintf('WHERE %s ',join(' ',$this->Conditions));

		if(count($this->Groups))
		$this->SQL .= sprintf('GROUP BY %s ',join(',',$this->Groups));

		if(count($this->Sorts))
		$this->SQL .= sprintf('ORDER BY %s ',join(',',$this->Sorts));

		if($this->Count) {
			if($this->Offset) $this->SQL .= sprintf('LIMIT %d,%d ',$this->Offset,$this->Count);
			else $this->SQL .= sprintf('LIMIT %d ',$this->Count);
		}

		$this->SQL = trim($this->SQL).'';
		return;
	}

	protected function GenerateUpdateSQL() {

		// determine if we need to merge the field array. this means we can use
		// set() two different ways
		//
		// - set('something=:alias')
		// - set(['something'=>':alias'])
		//
		// and they cannot be mixed. if you start using set(string) then commit
		// to calling them all for this query. if you start using set(assoc)
		// then commit to that. you'll get fucked queries if you do not, which
		// is fine. don't be a tool.

		$assocfields = true;
		foreach($this->Fields as $key => $val) {
			if(is_numeric($key)) {
				$assocfields = false;
				break;
			}
		}

		if($assocfields) {
			$fields = [];

			foreach($this->Fields as $field => $alias)
			$fields[] = "{$field}={$alias}";

			$this->Fields = $fields;
			unset($fields,$field,$alias);
		}

		unset($assocfields,$key,$val);

		////////
		////////

		$this->SQL = sprintf('UPDATE %s ',join(',',$this->Tables));
		$this->SQL .= sprintf('SET %s ',join(',',$this->Fields));

		if(count($this->Conditions))
		$this->SQL .= sprintf('WHERE %s ',join(' ',$this->Conditions));

		if($this->Count) {
			if($this->Offset) $this->SQL .= sprintf('LIMIT %d,%d ',$this->Count,$this->Offset);
			else $this->SQL .= sprintf('LIMIT %d ',$this->Count);
		}

		$this->SQL = trim($this->SQL).'';
		return;
	}

	protected function GenerateInsertSQL() {

		$keylist = array_keys($this->Fields);
		$arglist = array_values($this->Fields);

		$this->SQL = sprintf(
			'INSERT INTO %s (%s) VALUES (%s)',
			join(',',$this->Tables),
			join(',',$keylist),
			join(',',$arglist)
		);

		return;
	}

	protected function GenerateDeleteSQL() {

		$this->SQL = sprintf('DELETE FROM %s ',join(',',$this->Tables));

		if(count($this->Conditions))
		$this->SQL .= sprintf('WHERE %s ',join(' ',$this->Conditions));

		if($this->Count) {
			if($this->Offset) $this->SQL .= sprintf('LIMIT %d,%d ',$this->Count,$this->Offset);
			else $this->SQL .= sprintf('LIMIT %d ',$this->Count);
		}

		$this->SQL = trim($this->SQL).'';
		return;
	}


}
