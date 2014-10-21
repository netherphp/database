<?php

namespace Nether\Database;

use \Exception;
use \Nether;

Nether\Option::Define([
	'nether-database-verse-compiler' => 'Nether\\Database\\Verse\\MySQL'
]);

class Verse {

	const ModeSelect = 1;
	const ModeInsert = 2;
	const ModeUpdate = 3;
	const ModeDelete = 4;

	const JoinLeft = 1;
	const JoinRight = 2;
	const JoinInner = 4;
	const JoinOuter = 8;
	const JoinNatural = 16;

	const WhereAnd = 1;
	const WhereOr = 2;
	const WhereNot = 4;

	const SortAsc = 1;
	const SortDesc = 2;

	protected $Compiler;
	/*//
	@type string
	the name of the class that should compile the query into an SQL string.
	//*/

	////////////////
	////////////////

	public function __construct() {
		$this->Compiler = Nether\Option::Get('nether-database-verse-compiler');
		$this->ResetQueryProperties();
		return;
	}

	public function __toString() {
	/*//
	when used in a string context this object should automatically compile the
	query we have generated.
	//*/

		return $this->GetSQL();
	}

	////////////////
	////////////////

	protected $Pretty = false;
	/*//
	@type bool
	make the query a little more readable for human eyes.
	//*/

	protected $Mode;
	/*//
	@type int
	define the type of query we are going to produce. (select, insert, etc.)
	//*/

	protected $Fields;
	/*//
	@type array
	store field definitions for various query types. select will use it as a
	flat list of fields to fetch. update and insert will use it as a key-value
	pair for the set/value statements.
	//*/

	protected $Tables;
	/*//
	@type array
	store the table defintions for various query types. all query types will
	use this as a flat list of tables to hit.
	//*/

	protected $Joins;
	/*//
	@type array
	store the join definitions for various query types. it will contain a list
	of objects that better define each join.
	//*/

	protected $Conditions;
	/*//
	@type array
	store the conditions for various query types. it will contain a list of all
	the things for where clauses.
	//*/

	protected $Sorts;
	/*//
	@type array
	store the sort parameters for queries. it will contain a list of all the
	things for order by clauses.
	//*/

	protected $Groups;
	/*//
	@type array
	store the grouping conditions for queries. it will contain a list of all the
	things for group by clauses.
	//*/

	protected $Limit;
	/*//
	@type int
	store the count for limit clauses.
	//*/

	protected $Offset;
	/*//
	@type int
	store the count for offset clauses.
	//*/

	public function GetMode() { return $this->Mode; }
	public function GetFields() { return $this->Fields; }
	public function GetTables() { return $this->Tables; }
	public function GetJoins() { return $this->Joins; }
	public function GetConditions() { return $this->Conditions; }
	public function GetSorts() { return $this->Sorts; }
	public function GetGroups() { return $this->Groups; }
	public function GetLimit() { return (int)$this->Limit; }
	public function GetOffset() { return (int)$this->Offset; }

	public function SetPretty($bool) {
		$this->Pretty = (bool)$bool;
		return $this;
	}

	////////////////
	////////////////

	public function GetSQL() {
	/*//
	@return string
	fetch the compiled sql query that we have described in this verse.
	//*/

		$sql = new $this->Compiler($this);
		$string = $sql->Get();

		if($this->Pretty) {
			$string = preg_replace(
				'/ (FROM|INTO|WHERE|AND|OR|LIMIT|OFFSET|ORDER|GROUP|LEFT|RIGHT|NATURAL|INNER)/',
				"\n\\1",
				$string
			);

			$string = preg_replace(
				'/^(AND|OR )/ms',
				"\t\\1",
				$string
			);
		}

		return $string;
	}

	protected function ResetQueryProperties() {
	/*//
	reset all the properties of this verse incase this instance is reused to
	generate multiple sql queries.
	//*/

		$this->Fields = [];
		$this->Tables = [];
		$this->Joins = [];
		$this->Conditions = [];
		$this->Sorts = [];
		$this->Groups = [];
		$this->Limit = false;
		$this->Offset = false;

		return;
	}

	////////////////
	////////////////

	public function Select($arg=null) {
	/*//
	@argv string Table
	@argv array TableList
	@return self
	begin a new verse in the style of SELECT, defining what tables from which
	we want to pull data from.
	//*/

		$this->Mode = static::ModeSelect;
		$this->ResetQueryProperties();

		if($arg) {
			if(is_array($arg)) $this->Tables = array_merge($this->Tables,$arg);
			else $this->Tables[] = $arg;
		}

		return $this;
	}

	public function Update($arg=null) {
	/*//
	@argv string Table
	@argv array TableList
	@return self
	begin a new verse in the style of UPDATE, defining what tables in which to
	update data in.
	//*/

		$this->Mode = static::ModeUpdate;
		$this->ResetQueryProperties();

		if($arg) {
			if(is_array($arg)) $this->Tables = array_merge($this->Tables,$arg);
			else $this->Tables[] = $arg;
		}

		return $this;
	}

	public function Insert($arg=null) {
	/*//
	@argv string Table
	@return self
	begin a new verse in the style of INSERT, defining what tables in which to
	insert into.
	//*/

		$this->Mode = static::ModeInsert;
		$this->ResetQueryProperties();

		if($arg) {
			if(!is_string($arg))
			throw new Exception('INSERT only expects one table.');

			$this->Tables = [$arg];
		}

		return $this;
	}

	public function Delete($arg=null) {
	/*//
	@argv string Table
	@argv array TableList
	@return self
	begin a new verse in the style of DELETE, defining what tables from which
	to delete from.
	//*/

		$this->Mode = static::ModeDelete;
		$this->ResetQueryProperties();

		if($arg) {
			if(is_array($arg)) $this->Tables = array_merge($this->Tables,$arg);
			else $this->Tables[] = $arg;
		}

		return $this;
	}

	////////////////
	////////////////

	public function Table($arg) {
	/*//
	@argv string Table
	@argv string TableList
	@return self
	define what tables this verse should muck around with.
	//*/

		if(is_array($arg)) $this->Tables = array_merge($this->Tables,$arg);
		else $this->Tables[] = $arg;

		return $this;
	}

	public function From($arg) {
	/*//
	@alias self::Table
	provide bc and context for select/delete.
	//*/

		return $this->Table($arg);
	}

	public function Into($arg) {
	/*//
	@alias self::Table
	provide bc and context for update/insert.
	//*/

		return $this->Table($arg);
	}

	public function Join($arg,$flags=self::JoinLeft) {
	/*//
	@argv string Table, int JoinFlags default self::JoinLeft
	@argv array TableList, int JoinFlags default self::JoinLeft
	@return self
	define what tables should be joined into this verse and how they should be
	joined to the main query.
	//*/

		if(is_array($arg)) {
			foreach($arg as $jkey => $jquery) {
				if(is_numeric($jkey)) $this->Join($jquery,$flags);
				else $this->Join[$jkey] = (object)[ 'Flags'=>$flags, 'Query'=>$jquery ];
			}
		} else {
			$this->Joins[] = (object)[ 'Flags'=>$flags, 'Query'=>$arg ];
		}

		return $this;
	}

	public function Where($arg,$flags=self::WhereAnd) {
	/*//
	@argv string Condition, int CondFlags default self::WhereAnd
	@argv array CondList, int CondFlags default self::WhereAnd
	@return self
	define what conditions should be imposed in this verse and how they should
	chain into eachother. whereception is not currently well supported by the
	sql compiler yet.
	//*/

		if(is_array($arg)) {
			foreach($arg as $wkey => $wquery) {
				if(is_numeric($wkey)) $this->Where($wquery,$flags);
				else $this->Conditions[$wkey] = (object)[ 'Flags'=>$flags, 'Query'=>$wquery ];
			}
		} else {
			$this->Conditions[] = (object)[ 'Flags'=>$flags, 'Query'=>$arg ];
		}

		return $this;
	}

	public function Sort($arg,$flags=self::OrderAsc) {
	/*//
	@argv string Sort, int SortFlags default self::OrderAsc
	@argv array SortList, int SortFlags default self::OrderAnd
	@return self
	define what conditions should be imposed in this verse and how they should
	chain into eachother. whereception is not currently well supported by the
	sql compiler yet.
	//*/

		if(is_array($arg)) {
			foreach($arg as $skey => $squery) {
				if(is_numeric($skey)) $this->Sort($squery,$flags);
				else $this->Sorts[$skey] = (object)[ 'Flags'=>$flags, 'Query'=>$squery ];
			}
		} else {
			$this->Sorts[] = (object)[ 'Flags'=>$flags, 'Query'=>$arg ];
		}

		return $this;
	}

	public function OrderBy($arg,$flags=self::OrderAsc) {
	/*//
	@alias self::Sort
	provide bc and context for select queries.
	//*/

		return $this->Sort($arg,$flags);
	}

	public function Limit($count) {
	/*//
	@argv int Count
	@return self
	how many items to limit the result of this verse by.
	//*/

		$this->Limit = (int)$count;
		return $this;
	}

	public function Offset($offset) {
	/*//
	@argv int Offset
	@return self
	how many items to offset the result of this verse by.
	//*/

		$this->Offset = (int)$offset;
		return $this;
	}

	public function Fields(array $argv) {
	/*//
	@argv array FieldList
	define what fields this verse should operate against. some queries (select)
	will use this as a flat list. others (insert/update) will use it a key value
	list.
	//*/

		$this->Fields = array_merge($argv);
		return $this;
	}

	public function Values(array $argv) {
	/*//
	@alias self::Fields
	provide bc and context for insert queries.
	//*/

		return $this->Fields($argv);
	}

	public function Set(array $argv) {
	/*//
	@alias self::Fields
	provide bc and context for update queries.
	//*/

		return $this->Fields($argv);
	}

}
