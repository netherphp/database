<?php

namespace Nether\Database;

use Nether\Database\Result;
use Nether\Database\Struct\FlaggedQueryValue;
use Nether\Database\Struct\TableClassInfo;

use Stringable;
use Exception;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Verse
implements Stringable {
/*//
@date 2014-10-21
provides a walkable interface to construct an query programatically
and execute it against the database.
//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	ModeSelect    = 1,
	ModeInsert    = 2,
	ModeUpdate    = 3,
	ModeDelete    = 4,
	ModeCreate    = 5,
	ModeDropTable = 6,
	ModeSetVar    = 7;

	const
	SelectNormal    = 0,
	SelectCalcFound = 1;

	const
	InsertNormal      = 0,
	InsertIgnore      = (1 << 0),
	InsertUpdate      = (1 << 1),
	InsertReuseUnique = (1 << 2);

	const
	JoinLeft    = (1 << 0),
	JoinRight   = (1 << 1),
	JoinInner   = (1 << 2),
	JoinOuter   = (1 << 3),
	JoinNatural = (1 << 4);

	const
	WhereAnd = 1,
	WhereOr  = 2,
	WhereNot = 4;

	const
	SortAsc  = 1,
	SortDesc = 2;

	const
	OptConnectionDefault = 'Nether.Database.Verse.ConnectionDefault',
	OptVerseCompiler = 'Nether.Database.Verse.Compiler';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public string
	$Compiler;
	/*//
	@date 2022-02-17
	name of the class to construct compilers out of.
	//*/

	public ?Connection
	$Database;
	/*//
	@date 2022-02-17
	reference to the database connection.
	//*/

	public bool
	$Pretty = FALSE;
	/*//
	@date 2022-02-17
	make the query a little more readable for human eyes.
	//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected int
	$Mode;
	/*//
	@date 2022-02-17
	define the type of query we are going to produce. (select, insert, etc.)
	//*/

	protected int
	$Flags;
	/*//
	@date 2022-02-17
	define special flags that modify the overall behaviour of a query. these
	flags are specific to the various modes like SELECT, INSERT, etc.
	//*/

	protected array
	$Tables;
	/*//
	@date 2022-02-17
	store the table defintions for various query types. all query types will
	use this as a flat list of tables to hit.
	//*/

	protected array
	$Joins;
	/*//
	@date 2022-02-17
	store the join definitions for various query types. it will contain a list
	of objects that better define each join.
	//*/

	protected array
	$Fields;
	/*//
	@date 2022-02-17
	store field definitions for various query types. select will use it as a
	flat list of fields to fetch. update and insert will use it as a key-value
	pair for the set/value statements.
	//*/

	protected array
	$Conditions;
	/*//
	@date 2022-02-17
	store the conditions for various query types. it will contain a list of all
	the things for where clauses.
	//*/

	protected array
	$Havings;
	/*//
	@date 2022-02-17
	store having conditions. it will contain a list of all the things for the
	having clauses.
	//*/

	protected array
	$Sorts;
	/*//
	@date 2022-02-17
	store the sort parameters for queries. it will contain a list of all the
	things for order by clauses.
	//*/

	protected array
	$Groups;
	/*//
	@date 2022-02-17
	store the grouping conditions for queries. it will contain a list of all the
	things for group by clauses.
	//*/

	protected int
	$Limit;
	/*//
	@date 2022-02-17
	how many rows to limit this query to.
	//*/

	protected int
	$Offset;
	/*//
	@date 2022-02-17
	how many rows to offset this query by.
	//*/

	protected ?string
	$PrimaryKey;
	/*//
	@date 2022-02-18
	the name of the primary key for the table.
	//*/

	protected string
	$Charset;
	/*//
	@date 2022-02-17
	charset to use for this table.
	//*/

	protected string
	$Collate;
	/*//
	@date 2022-02-17
	collation to use for this table.
	//*/

	protected string
	$Engine;
	/*//
	@date 2022-02-17
	db engine to use for this table.
	//*/

	protected array
	$ForeignKeys;
	/*//
	@date 2022-02-17
	list of foreign keys for this table.
	//*/

	protected array
	$Indexes;
	/*//
	@date 2022-02-17
	list of indexes for this table.
	//*/

	protected string
	$Comment;
	/*//
	@date 2022-02-17
	comment to use for this table.
	//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(?Connection $Database=NULL, ?string $Compiler=NULL) {
	/*//
	@date 2020-11-24
	//*/

		$this->Database = $Database ?? $this->GetDefaultConnection();
		$this->Compiler = $Compiler ?? $this->GetDefaultCompiler();

		$this->ResetQueryProperties(TRUE);
		return;
	}

	public function
	__ToString() {
	/*//
	@implements Stringable
	@date 2020-11-24
	//*/

		return $this->GetSQL();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// primary query mode selection.

	public function
	Select(string $Arg, int $Flags=0, bool $Reset=TRUE):
	static {
	/*//
	@date 2022-02-17
	begin a new verse in the style of SELECT, defining what table which
	we want to pull data from.
	//*/

		$this->Mode = static::ModeSelect;
		$this->Flags = $Flags;

		if($Reset)
		$this->ResetQueryProperties();

		$this->MergeValues($this->Tables, $Arg);
		return $this;
	}

	public function
	Update(string $Arg, int $Flags=0, bool $Reset=TRUE):
	static {
	/*//
	@date 2022-02-17
	begin a new verse in the style of UPDATE, defining what tables which to
	update data in.
	//*/

		$this->Mode = static::ModeUpdate;
		$this->Flags = $Flags;

		if($Reset)
		$this->ResetQueryProperties();

		$this->MergeValues($this->Tables, $Arg);
		return $this;
	}

	public function
	Insert(string $Arg, int $Flags=0, bool $Reset=TRUE):
	static {
	/*//
	@date 2022-02-17
	begin a new verse in the style of INSERT, defining what tables which to
	insert into.
	//*/

		$this->Mode = static::ModeInsert;
		$this->Flags = $Flags;

		if($Reset)
		$this->ResetQueryProperties();

		$this->Tables = [ $Arg ];
		return $this;
	}

	public function
	Delete(string $Arg, int $Flags=0, bool $Reset=TRUE):
	static {
	/*//
	@date 2022-02-17
	begin a new verse in the style of DELETE, defining what tables which
	to delete from.
	//*/

		$this->Mode = static::ModeDelete;
		$this->Flags = $Flags;

		if($Reset)
		$this->ResetQueryProperties();

		$this->MergeValues($this->Tables, $Arg);
		return $this;
	}

	public function
	Create(string $Arg, bool $Reset=TRUE):
	static {
	/*//
	@date 2021-08-24
	begin a new verse in the style of CREATE, defining what table to create.
	//*/

		$this->Mode = static::ModeCreate;

		if($Reset)
		$this->ResetQueryProperties();

		$this->Tables = [ $Arg ];
		return $this;
	}

	public function
	DropTable(string $Arg, bool $Reset=TRUE):
	static {
	/*//
	@date 2022-02-19
	begin a new verse in the style of DROP TABLE, defining what table to drop.
	//*/

		$this->Mode = static::ModeDropTable;

		if($Reset)
		$this->ResetQueryProperties();

		$this->Tables = [ $Arg ];
		return $this;
	}

	public function
	VarSet(string|array $Fields, bool $Reset=TRUE):
	static {

		$this->Mode = static::ModeSetVar;

		if($Reset)
		$this->ResetQueryProperties();

		if($Fields)
		$this->Fields($Fields);

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Table(string|array $Table, bool $Reset=FALSE):
	static {
	/*//
	@date 2022-02-17
	define what tables this verse should muck around with.
	//*/


		if($Reset)
		$this->Tables = [];

		$this->MergeValues($this->Tables, $Table);
		return $this;
	}

	public function
	Join(string|array $Table, int $Flags=self::JoinLeft):
	static {
	/*//
	@date 2022-02-17
	define what tables should be joined into this verse and how they should be
	joined to the main query.
	//*/

		$this->MergeFlaggedValues($this->Joins, $Table, $Flags);
		return $this;
	}

	public function
	Where(string|array $Condition, int $Flags=self::WhereAnd):
	static {
	/*//
	@date 2022-02-17
	define what conditions should be imposed in this verse and how they should
	chain into eachother. whereception is not currently well supported by the
	sql compiler yet.
	//*/

		$this->MergeFlaggedValues($this->Conditions, $Condition, $Flags);
		return $this;
	}

	public function
	Having(string|array $Condition, int $Flags=self::WhereAnd):
	static {
	/*//
	@date 2022-02-17
	define what conditions should be imposed in this verse and how they should
	chain into eachother. whereception is not currently well supported by the
	sql compiler yet.
	//*/

		$this->MergeFlaggedValues($this->Havings, $Condition, $Flags);
		return $this;
	}

	public function
	Sort(string|array $Sorting, int $Flags=self::SortAsc):
	static {
	/*//
	@date 2022-02-17
	define what sorts should be imposed in this verse.
	//*/

		$this->MergeFlaggedValues($this->Sorts, $Sorting, $Flags);
		return $this;
	}

	public function
	Group(string|array $Grouping):
	static {
	/*//
	@date 2022-02-17
	define what groupings should be imposed in this verse.
	//*/

		$this->MergeValues($this->Groups, $Grouping);
		return $this;
	}

	public function
	Limit(int $Num):
	static {
	/*//
	@date	2022-02-17
	how many items to limit the result of this verse by.
	//*/

		$this->Limit = $Num;
		return $this;
	}

	public function
	Offset(int $Num):
	static {
	/*//
	@date 2022-02-17
	how many items to offset the result of this verse by.
	//*/

		$this->Offset = $Num;
		return $this;
	}

	public function
	Fields(string|array $Fields, bool $Reset=FALSE) {
	/*//
	@deprecated 2022-02-17
	@alias self::Field
	provide bc.
	//*/

		if($Reset)
		$this->Fields = [];

		$this->MergeValues($this->Fields, $Fields);
		return $this;
	}

	public function
	Values(string|array $Values) {
	/*//
	@date 2022-02-17
	@alias self::Field
	provide context for insert queries.
	//*/

		return $this->Fields($Values);
	}

	public function
	PrimaryKey(?string $Name=NULL):
	static {
	/*//
	@date 2022-02-18
	//*/

		$this->PrimaryKey = $Name ?? '';
		return $this;
	}

	public function
	Charset(?string $Charset):
	static {
	/*//
	@date 2021-08-24
	set the charset for this verse.
	//*/

		$this->Charset = $Charset;
		return $this;
	}

	public function
	Collate(?string $Collation):
	static {
	/*//
	@date 2021-08-24
	set the collation for this verse.
	//*/

		$this->Collate = $Collation;
		return $this;
	}

	public function
	Engine(?string $Engine):
	static {
	/*//
	@date 2021-08-24
	set the engine for this verse.
	//*/

		$this->Engine = $Engine;
		return $this;
	}

	public function
	ForeignKey(string|array $Key) {
	/*//
	@date 2021-08-24
	set the keys for this verse.
	//*/

		$this->MergeValues($this->ForeignKeys, $Key);
		return $this;
	}

	public function
	Index(string|array $Index) {
	/*//
	@date 2021-08-24
	set the indexing for this verse.
	//*/

		$this->MergeValues($this->Indexes, $Index);
		return $this;
	}

	public function
	Comment(?string $Text) {
	/*//
	@date 2021-08-24
	set the comment for this table.
	//*/

		$this->Comment = $Text ?? '';
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetDatabase():
	?Connection {
	/*//
	@date 2022-02-18
	get the current mode of the query.
	//*/

		return $this->Database;
	}

	public function
	HasDatabase():
	bool {
	/*//
	@date 2022-02-19
	//*/

		return ($this->Database instanceof Connection);
	}

	public function
	GetMode():
	int {
	/*//
	@date 2022-02-17
	Get the current mode of the query.
	//*/

		return $this->Mode;
	}

	public function
	GetFlags():
	int {
	/*//
	@date 2022-02-17
	get the flags for this query.
	//*/

		return $this->Flags;
	}

	public function
	GetTables():
	array {
	/*//
	@date 2022-02-17
	get the tables for this query.
	//*/

		return $this->Tables;
	}

	public function
	GetJoins():
	array {
	/*//
	@date 2022-02-17
	get the join definitions for this query.
	//*/

		return $this->Joins;
	}

	public function
	GetFields():
	array {
	/*//
	@date 2022-02-17
	get the fields for this query.
	//*/

		return $this->Fields;
	}

	public function
	GetConditions():
	array {
	/*//
	@date 2022-02-17
	get the conditions for this query.
	//*/

		return $this->Conditions;
	}

	public function
	GetHavings():
	array {
	/*//
	@date 2022-02-17
	get the having conditions.
	//*/

		return $this->Havings;
	}

	public function
	GetSorts():
	array {
	/*//
	@date 2022-02-17
	get the sort parameters for queries.
	//*/

		return $this->Sorts;
	}

	public function
	GetGroups():
	array {
	/*//
	@date 2022-02-17
	get the grouping conditions for queries.
	//*/

		return $this->Groups;
	}

	public function
	GetLimit():
	int {
	/*//
	@date 2022-02-17
	get the limit for this query.
	//*/

		return $this->Limit;
	}

	public function
	GetOffset():
	int {
	/*//
	@date 2022-02-17
	get the offset for this query.
	//*/

		return $this->Offset;
	}

	public function
	GetPrimaryKey():
	?string {
	/*//
	@date 2022-02-17
	get the primary key for this table.
	//*/

		return $this->PrimaryKey ?: NULL;
	}

	public function
	GetCharset():
	string {
	/*//
	@date 2022-02-17
	get the charset for this query.
	//*/

		return $this->Charset;
	}

	public function
	GetCollate():
	string {
	/*//
	@date 2022-02-17
	get the collate for this query.
	//*/

		return $this->Collate;
	}

	public function
	GetEngine():
	string {
	/*//
	@date 2022-02-17
	get the engine for this query.
	//*/

		return $this->Engine;
	}

	public function
	GetForeignKeys():
	array {
	/*//
	@date 2022-02-17
	get the foreign keys for this table.
	//*/

		return $this->ForeignKeys;
	}

	public function
	GetIndexes():
	array {
	/*//
	@date 2022-02-17
	get the indexes for this table.
	//*/

		return $this->Indexes;
	}

	public function
	GetComment():
	string {
	/*//
	@date 2022-02-17
	get the comment for this table.
	//*/

		return $this->Comment;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetSQL():
	string {
	/*//
	@date 2022-02-17
	fetch the compiled sql query that we have described in this verse.
	//*/

		$Compiler = new ($this->Compiler)($this);
		$Output = $Compiler->Compile();

		return $Output;
	}

	public function
	Query(array|object $Argv=[]):
	Result {
	/*//
	@date 2022-02-17
	pass the request to query to the underlying database.
	//*/

		if($this->Database === NULL)
		throw new Error\NoConnectionAvailable;

		$Result = $this->Database->Query($this, $Argv);

		return $Result;
	}

	public function
	Reset():
	static {
	/*//
	@date 2022-02-17
	reset the query properties.
	//*/

		$this->ResetQueryProperties();
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetPretty(bool $Input):
	static {
	/*//
	@date 2022-02-17
	//*/

		$this->Pretty = $Input;
		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetDefaultCompiler():
	string {
	/*//
	@date 2022-02-17
	get the default compiler.
	//*/

		// todo 2022-12-18
		// it needs to determine this from the connection type.

		return 'Nether\\Database\\Verse\\MySQL';
	}

	protected function
	GetDefaultConnection():
	?Connection {
	/*//
	@date 2022-02-17
	try to connect to the default database connection if configured.
	//*/

		$Default = Library::Get(Library::ConfDefaultConnection);

		if(!is_string($Default))
		return NULL;

		$DBM = new Manager;
		$DB = $DBM->Get($Default);

		// in this instance manager will be throwing an exception
		// if it was invalid.

		return $DB;
	}

	protected function
	ResetQueryProperties(bool $Full=FALSE):
	static {
	/*//
	reset all the properties of this verse incase this instance is reused to
	generate multiple sql queries.
	//*/

		// @todo 2022-02-17
		// move the charset, collate, engine defaults into the compiler
		// somehow so engines can set reasonable defaults themselves.

		$this->Tables = [];
		$this->Fields = [];
		$this->Joins = [];
		$this->Conditions = [];
		$this->Havings = [];
		$this->Sorts = [];
		$this->Groups = [];
		$this->Limit = 0;
		$this->Offset = 0;
		$this->PrimaryKey = NULL;
		$this->Charset = 'utf8mb4';
		$this->Collate = 'utf8mb4_general_ci';
		$this->Engine = 'InnoDB';
		$this->ForeignKeys = [];
		$this->Indexes = [];
		$this->Comment = '';

		if($Full) {
			$this->Mode = 0;
			$this->Flags = 0;
		}

		return $this;
	}

	protected function
	MergeValues(array &$Pool, mixed $Addl):
	static {
	/*//
	@date 2022-02-17
	merge merge the additional values into the pool of values given.
	the additional can be a single value or an array of values.
	//*/

		$Key = NULL;
		$Query = NULL;

		////////

		if(is_array($Addl))
		foreach($Addl as $Key => $Query) {
			if(is_numeric($Key))
			$Pool[] = $Query;

			else
			$Pool[$Key] = $Query;
		}

		else
		$Pool[] = $Addl;

		////////

		return $this;
	}

	protected function
	MergeFlaggedValues(array &$Pool, mixed $Addl, int $Flags):
	static {
	/*//
	@date 2022-02-17
	merge the additional values into the pool of values given.
	the additional can be a single value or an array of values. the flags
	get shared across all the additionals.
	//*/

		$Key = NULL;
		$Query = NULL;

		////////

		if(is_array($Addl))
		foreach($Addl as $Key => $Query) {
			if(is_string($Key))
			$Pool[$Key] = new FlaggedQueryValue($Flags, $Query);

			else
			$Pool[] = new FlaggedQueryValue($Flags, $Query);
		}

		else
		$Pool[] = new FlaggedQueryValue($Flags, $Addl);

		////////

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	FromMeta(string $Class, int $Mode, ?Connection $DB=NULL):
	?static {

		$Verse = match($Mode) {

			Verse::ModeCreate
			=> Verse::FromMetaCreate($Class, $DB),

			Verse::ModeDropTable
			=> Verse::FromMetaDropTable($Class, $DB),

			default
			=> NULL

		};

		return $Verse;
	}

	static public function
	FromMetaSelect(string $ClassName, ?Connection $DB=NULL):
	static {
	/*//
	@date	2022-02-17
	//*/

		$Verse = new static($DB);
		$Table = new TableClassInfo($ClassName);

		////////

		$Verse
		->Select($Table->Name)
		->PrimaryKey($Table->PrimaryKey);

		return $Verse;
	}

	static public function
	FromMetaInsert(string $ClassName, ?Connection $DB=NULL):
	static {
	/*//
	@date	2022-02-17
	//*/

		$Verse = new static($DB);
		$Table = new TableClassInfo($ClassName);
		$Flags = 0;
		$Attr = NULL;

		////////

		foreach($Table->Attributes as $Attr) {
			if($Attr instanceof Meta\InsertIgnore)
			$Flags |= static::InsertIgnore;

			if($Attr instanceof Meta\InsertReuseUnique)
			$Flags |= static::InsertReuseUnique;

			if($Attr instanceof Meta\InsertUpdate)
			$Flags |= static::InsertUpdate;
		}

		////////

		$Verse
		->Insert($Table->Name, $Flags)
		->PrimaryKey($Table->PrimaryKey);

		////////

		return $Verse;
	}

	static public function
	FromMetaUpdate(string $ClassName, ?Connection $DB=NULL):
	static {
	/*//
	@date	2022-02-17
	//*/

		$Verse = new static($DB);
		$Table = new TableClassInfo($ClassName);

		////////

		$Verse
		->Update($Table->Name)
		->PrimaryKey($Table->PrimaryKey);

		return $Verse;
	}

	static public function
	FromMetaDelete(string $ClassName, ?Connection $DB=NULL):
	static {
	/*//
	@date	2022-02-17
	//*/

		$Verse = new static($DB);
		$Table = new TableClassInfo($ClassName);

		////////

		$Verse
		->Delete($Table->Name)
		->PrimaryKey($Table->PrimaryKey);

		return $Verse;
	}

	static public function
	FromMetaCreate(string $ClassName, ?Connection $DB=NULL):
	static {
	/*//
	@date	2022-02-17
	//*/

		$Verse = new static($DB);
		$Table = new TableClassInfo($ClassName);

		$Verse
		->Create($Table->Name)
		->Comment($Table->Comment)
		->Fields($Table->GetFieldList())
		->Index($Table->GetIndexList())
		->ForeignKey($Table->GetForeignKeyList());

		return $Verse;
	}

	static public function
	FromMetaDropTable(string $ClassName, ?Connection $DB=NULL):
	static {
	/*//
	@date	2022-02-17
	//*/

		$Verse = new static($DB);
		$Table = new TableClassInfo($ClassName);

		////////

		$Verse
		->DropTable($Table->Name);

		return $Verse;
	}

}
