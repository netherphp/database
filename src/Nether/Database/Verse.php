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

	const InsertNormal = 0;
	const InsertIgnore = 1;
	const InsertUpdate = 2;

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

	protected
	$Compiler = null;
	/*//
	@type string
	the name of the class that should compile the query into an SQL string.
	//*/

	////////////////
	////////////////

	// database passthru stuffs. these are mainly to simplify how you write
	// your code that needs to deal with the database.

	protected
	$Database = null;
	/*//
	@type string
	the type that defines the type of database we are going to compile to.
	this is the type from the database instance that created the verse.
	//*/

	public function
	GetDatabase() {
	/*//
	@return Nether\Database
	//*/

		return $this->Database;
	}

	public function
	SetDatabase(Nether\Database $DB) {
	/*//
	@argv Nether\Database DB
	@return self
	//*/

		$this->Database = $DB;
		return $this;
	}

	public function
	NewCoda($ClassName) {
	/*//
	@argv String ClassName
	pass the request for a new coda to the underlying database.
	//*/

		if(!$this->Database instanceof Nether\Database)
		throw new \Exception('unable to create coda without a database assigned to the verse.');

		return $this->Database->NewCoda($ClassName);
	}

	public function
	Query($Argv=[]) {
	/*//
	@argv String ClassName
	pass the request to query to the underlying database.
	//*/

		if(!$this->Database instanceof Nether\Database)
		throw new Exception('unable to query withotu a database assigned to the verse.');

		return $this->Database->Query($this,$Argv);
	}

	////////////////
	////////////////

	public function
	__Construct($DB=null) {

		if($DB) $this->Database = $DB;

		$this->Compiler = Nether\Option::Get('nether-database-verse-compiler');
		$this->ResetQueryProperties();
		return;
	}

	public function
	__ToString() {
	/*//
	when used in a string context this object should automatically compile the
	query we have generated.
	//*/

		return $this->GetSQL();
	}

	////////////////
	////////////////

	protected
	$Pretty = false;
	/*//
	@type bool
	make the query a little more readable for human eyes.
	//*/

	public function
	GetPretty() {
		return $this->Pretty;
	}

	public function
	SetPretty($Bool) {
		$this->Pretty = (bool)$Bool;
		return $this;
	}

	////////////////
	////////////////

	protected
	$Mode = 0;
	/*//
	@type int
	define the type of query we are going to produce. (select, insert, etc.)
	//*/

	public function
	GetMode() {
		return $this->Mode;
	}

	////////////////
	////////////////

	protected
	$Flags = 0;
	/*//
	@type int
	define special flags that modify the overall behaviour of a query. these
	flags are specific to the various modes like SELECT, INSERT, etc.
	//*/

	public function
	GetFlags() {
		return $this->Flags;
	}

	////////////////
	////////////////

	protected
	$Fields = null;
	/*//
	@type array
	store field definitions for various query types. select will use it as a
	flat list of fields to fetch. update and insert will use it as a key-value
	pair for the set/value statements.
	//*/

	public function
	GetFields() {
		return $this->Fields;
	}

	////////////////
	////////////////

	protected
	$Tables = null;
	/*//
	@type array
	store the table defintions for various query types. all query types will
	use this as a flat list of tables to hit.
	//*/

	public function
	GetTables() {
		return $this->Tables;
	}

	////////////////
	////////////////

	protected
	$Joins = null;
	/*//
	@type array
	store the join definitions for various query types. it will contain a list
	of objects that better define each join.
	//*/

	public function
	GetJoins() {
		return $this->Joins;
	}

	////////////////
	////////////////

	protected
	$Conditions = null;
	/*//
	@type array
	store the conditions for various query types. it will contain a list of all
	the things for where clauses.
	//*/

	public function
	GetConditions() {
	/*//
	return the conditions, baking any codas.
	//*/

		foreach($this->Conditions as $Cond)
		if($Cond instanceof Coda)
		$Cond->SetDatabase($this->Database);

		return $this->Conditions;
	}

	protected
	$Havings = NULL;
	/*//
	@type Array
	store having conditions. it will contain a list of all the things for the
	having clauses.
	//*/

	public function
	GetHavings() {
		return $this->Havings;
	}

	////////////////
	////////////////

	protected
	$Sorts = null;
	/*//
	@type array
	store the sort parameters for queries. it will contain a list of all the
	things for order by clauses.
	//*/

	public function
	GetSorts() {
		return $this->Sorts;
	}

	////////////////
	////////////////

	protected
	$Groups = null;
	/*//
	@type array
	store the grouping conditions for queries. it will contain a list of all the
	things for group by clauses.
	//*/

	public function
	GetGroups() {
		return $this->Groups;
	}

	////////////////
	////////////////

	protected
	$Limit = 0;
	/*//
	@type int
	store the count for limit clauses.
	//*/

	public function
	GetLimit() {
		return (int)$this->Limit;
	}

	////////////////
	////////////////

	protected
	$Offset = 0;
	/*//
	@type int
	store the count for offset clauses.
	//*/

	public function
	GetOffset() {
		return (int)$this->Offset;
	}

	////////////////
	////////////////

	public function
	GetSQL() {
	/*//
	@return string
	fetch the compiled sql query that we have described in this verse.
	//*/

		$SQL = new $this->Compiler($this);
		$String = $SQL->Get();

		if($this->Pretty)
		$String = preg_replace(
			'/ (FROM|INTO|WHERE|AND|OR|LIMIT|OFFSET|ORDER|GROUP|LEFT|RIGHT|NATURAL|INNER|VALUES)/',
			"\n\\1",
			$String
		);

		return $String;
	}

	public function
	GetNamedArgs() {
	/*//
	@deprecated moved
	@return array[string, ...]
	find out all the named arguments that were in the final query.
	//*/

		$SQL = $this->GetSQL();

		preg_match_all('/:([a-z0-9]+)/i',$SQL,$Match);
		return $Match[1];
	}

	protected function
	ResetQueryProperties() {
	/*//
	reset all the properties of this verse incase this instance is reused to
	generate multiple sql queries.
	//*/

		$this->Fields = [];
		$this->Tables = [];
		$this->Joins = [];
		$this->Conditions = [];
		$this->Havings = [];
		$this->Sorts = [];
		$this->Groups = [];
		$this->Limit = false;
		$this->Offset = false;

		return;
	}

	protected function
	MergeValues(array &$Pool, $Addl) {
	/*//
	@argv array Pool, mixed Additions
	merge values into the pool. such description, wow.
	so you give this a pool, the pool is the array of values you want to end
	up with. this thing must be an array. then you give it an additional item
	you want to add. if the additional item is an array it will do an array
	merge so that you can overwrite named (associative array) values. if it is
	not then it is just appended to the array pool.
	//*/

		if(is_array($Addl)) {
			foreach($Addl as $Key => $Query) {
				if(is_numeric($Key)) $Pool[] = $Query;
				else $Pool[$Key] = $Query;
			}
		} else {
			$Pool[] = $Addl;
		}

		return;
	}

	protected function
	MergeFlaggedValues(Array &$Pool, $Addl, $Flag) {
	/*//
	@argv array Pool, mixed Additions, Int Flags
	//*/

		if(is_array($Addl)) {
			foreach($Addl as $Key => $Query) {
				if(is_numeric($Key))
				$Pool[] = (object)[ 'Flags'=>$Flag, 'Query'=>$Query ];

				else
				$Pool[$Key] = (object)[ 'Flags'=>$Flag, 'Query'=>$Query ];
			}
		} else {
			$Pool[] = (object)[ 'Flags'=>$Flag, 'Query'=>$Addl ];
		}

		return;
	}

	////////////////
	////////////////

	public function
	Select($Arg=null, $Flags=0, $KeepData=FALSE) {
	/*//
	@argv string Table
	@argv array TableList
	@return self
	begin a new verse in the style of SELECT, defining what tables from which
	we want to pull data from.
	//*/

		$this->Mode = static::ModeSelect;
		$this->Flags = $Flags;

		if(!$KeepData)
		$this->ResetQueryProperties();

		$this->MergeValues($this->Tables,$Arg);
		return $this;
	}

	public function
	Update($Arg=null, $Flags=0, $KeepData=FALSE) {
	/*//
	@argv string Table
	@argv array TableList
	@return self
	begin a new verse in the style of UPDATE, defining what tables in which to
	update data in.
	//*/

		$this->Mode = static::ModeUpdate;
		$this->Flags = $Flags;

		if(!$KeepData)
		$this->ResetQueryProperties();

		$this->MergeValues($this->Tables,$Arg);
		return $this;
	}

	public function
	Insert($Arg=null, $Flags=0, $KeepData=FALSE) {
	/*//
	@argv string Table
	@return self
	begin a new verse in the style of INSERT, defining what tables in which to
	insert into.
	//*/

		$this->Mode = static::ModeInsert;
		$this->Flags = $Flags;

		if(!$KeepData)
		$this->ResetQueryProperties();

		if($Arg) {
			if(!is_string($Arg))
			throw new Exception('INSERT only expects one table.');

			$this->Tables = [ $Arg ];
		}

		return $this;
	}

	public function
	Delete($Arg=null, $Flags=0, $KeepData=FALSE) {
	/*//
	@argv string Table
	@argv array TableList
	@return self
	begin a new verse in the style of DELETE, defining what tables from which
	to delete from.
	//*/

		$this->Mode = static::ModeDelete;
		$this->Flags = $Flags;

		if(!$KeepData)
		$this->ResetQueryProperties();

		$this->MergeValues($this->Tables,$Arg);
		return $this;
	}

	////////////////
	////////////////

	public function
	Table($Arg) {
	/*//
	@argv string Table
	@argv string TableList
	@return self
	define what tables this verse should muck around with.
	//*/

		$this->MergeValues($this->Tables,$Arg);
		return $this;
	}

	public function
	From($Arg) {
	/*//
	@alias self::Table
	provide bc and context for select/delete.
	//*/

		return $this->Table($Arg);
	}

	public function
	Into($Arg) {
	/*//
	@alias self::Table
	provide bc and context for update/insert.
	//*/

		return $this->Table($Arg);
	}

	public function
	Join($Arg, $Flags=self::JoinLeft) {
	/*//
	@argv string Table, int JoinFlags default self::JoinLeft
	@argv array TableList, int JoinFlags default self::JoinLeft
	@return self
	define what tables should be joined into this verse and how they should be
	joined to the main query.
	//*/

		$this->MergeFlaggedValues($this->Joins,$Arg,$Flags);
		return $this;
	}

	public function
	Where($Arg, $Flags=self::WhereAnd) {
	/*//
	@argv string Condition, int CondFlags default self::WhereAnd
	@argv array CondList, int CondFlags default self::WhereAnd
	@return self
	define what conditions should be imposed in this verse and how they should
	chain into eachother. whereception is not currently well supported by the
	sql compiler yet.
	//*/

		$this->MergeFlaggedValues($this->Conditions,$Arg,$Flags);
		return $this;
	}

	public function
	Having($Arg, $Flags=self::WhereAnd) {
	/*//
	@argv string Condition, int CondFlags default self::WhereAnd
	@argv array CondList, int CondFlags default self::WhereAnd
	@return self
	define what conditions should be imposed in this verse and how they should
	chain into eachother. whereception is not currently well supported by the
	sql compiler yet.
	//*/

		$this->MergeFlaggedValues($this->Havings,$Arg,$Flags);
		return $this;
	}

	public function
	Sort($Arg, $Flags=self::SortAsc) {
	/*//
	@argv string Sort, int SortFlags default self::OrderAsc
	@argv array SortList, int SortFlags default self::OrderAnd
	@return self
	define what conditions should be imposed in this verse and how they should
	chain into eachother. whereception is not currently well supported by the
	sql compiler yet.
	//*/

		$this->MergeFlaggedValues($this->Sorts,$Arg,$Flags);
		return $this;
	}

	public function
	OrderBy($Arg, $Flags=self::OrderAsc) {
	/*//
	@alias self::Sort
	provide bc and context for select queries.
	//*/

		return $this->Sort($Arg,$Flags);
	}

	public function
	Group($Arg) {
	/*//
	@argv string GroupCondition
	@argv string GroupConditionList
	//*/

		$this->MergeValues($this->Groups,$Arg);
		return $this;
	}

	public function
	GroupBy($Arg) {
	/*//
	@alias self::Group
	provide bc and context for grouping.
	//*/

		return $this->Group($Arg);
	}

	public function
	Limit($Count) {
	/*//
	@argv int Count
	@return self
	how many items to limit the result of this verse by.
	//*/

		$this->Limit = (int)$Count;
		return $this;
	}

	public function
	Offset($Offset) {
	/*//
	@argv int Offset
	@return self
	how many items to offset the result of this verse by.
	//*/

		$this->Offset = (int)$Offset;
		return $this;
	}

	public function
	Fields($Arg) {
	/*//
	@argv array FieldList
	define what fields this verse should operate against. some queries (select)
	will use this as a flat list. others (insert/update) will use it a key value
	list.
	//*/

		$this->MergeValues($this->Fields,$Arg);
		return $this;
	}

	public function
	Values($Argv) {
	/*//
	@alias self::Fields
	provide bc and context for insert queries.
	//*/

		return $this->Fields($Argv);
	}

	public function
	Set($Argv) {
	/*//
	@alias self::Fields
	provide bc and context for update queries.
	//*/

		return $this->Fields($Argv);
	}

}
