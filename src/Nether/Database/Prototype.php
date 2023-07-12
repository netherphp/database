<?php

namespace Nether\Database;
use Nether;

use Nether\Common;

use ArrayAccess;
use Exception;
use Nether\Database\Meta\InsertReuseUnique;
use Nether\Database\Meta\InsertUpdate;
use Nether\Database\Verse;

class Prototype
extends Nether\Common\Prototype {

	static public string
	$DBA = 'Default';

	static public function
	HasDB():
	bool {

		$DBM = new Manager;

		if(!$DBM->Exists(static::$DBA))
		return FALSE;

		return TRUE;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Drop():
	static {
	/*//
	@date 2022-11-08
	drop this object from the database for real.
	//*/

		$Table = static::GetTableInfo();
		$DBM = new Manager;

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->Delete($Table->Name)
			->Where("{$Table->PrimaryKey}=:ID")
			->Limit(1)
		);

		$Result = $SQL->Query([
			':ID' => $this->{$Table->PrimaryKey}
		]);

		////////

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		////////

		return $this;
	}

	public function
	Update(iterable $Dataset):
	static {
	/*//
	@date 2022-11-08
	update this object and its database entry with the supplied data.
	//*/

		$Table = static::GetTableInfo();
		$Fields = static::GetTableInsertMapFrom($Dataset);
		$DBM = new Manager;
		$Key = NULL;
		$Val = NULL;

		if($Dataset instanceof Common\Datastore)
		$Dataset = $Dataset->GetData();

		////////

		foreach($Dataset as $Key => $Val) {
			if(!isset($Table->Fields[$Key]))
			continue;

			if($Dataset[$Key] === '' && $Table->Fields[$Key]->Nullify)
			$Dataset[$Key] = NULL;
		}

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->Update($Table->Name)
			->Values($Fields)
			->Where("{$Table->PrimaryKey}=:PrimaryKeyID")
			->Limit(1)
		);

		$Result = $SQL->Query(array_merge(
			$Dataset,
			[ ':PrimaryKeyID' => $this->{$Table->PrimaryKey} ]
		));

		foreach($Fields as $Key => $Val)
		$this->{$Key} = $Dataset[$Key];


		////////

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		////////

		return $this;
	}

	public function
	Patch(array|ArrayAccess $Input):
	array {
	/*//
	@date 2023-02-02
	given some input use the defined property filters to patch that dataset
	up and return it. a property must have both the patchable attribute and
	at least one filter defined.
	//*/

		$PropInfos = static::GetPropertiesWithAttribute(
			Common\Meta\PropertyPatchable::class
		);

		$Output = [];
		$Prop = NULL;
		$Info = NULL;

		foreach($PropInfos as $Prop => $Info) {
			/** @var Common\Prototype\PropertyInfo $Info */

			$Has = match(TRUE) {
				$Input instanceof ArrayAccess
				=> $Input->OffsetExists($Prop),

				default
				=> array_key_exists($Prop, $Input)
			};

			if(!$Has)
			continue;

			$Filters = $Info->GetAttributes(
				Common\Meta\PropertyFilter::class
			);

			if(!count($Filters))
			continue;

			$Output[$Prop] = array_reduce(
				$Filters,
				fn(mixed $Data, callable $Func)=> $Func($Data),
				$Input[$Prop]
			);
		}

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetByField(string $Field, mixed $Value):
	?static {
	/*//
	@date 2022-11-08
	query for a single row where a specified field matches a specified value.
	it is mainly to make adding more specific things easy later when a fetch
	only needs to check that one field is one value, like GetByEmail.
	//*/

		$Table = static::GetTableInfo();
		$DBM = new Manager;

		if(!array_key_exists($Field, $Table->Fields))
		throw new Exception("{$Field} not found on {$Table->Name}");

		////////

		$Opt = new Common\Datastore([
			':FieldValue' => $Value
		]);

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->Select("{$Table->Name} Main")
			->Fields('`Main`.*')
			->Where("`Main`.`{$Field}`=:FieldValue")
			->Limit(1)
		);

		static::FindExtendTables($SQL, $Opt);

		$Result = $SQL->Query($Opt->GetData());

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		////////

		$Row = $Result->Next();

		if(!$Row)
		return NULL;

		////////

		return new static((array)$Row);
	}

	static public function
	GetByID(mixed $ID):
	?static {
	/*//
	@date 2022-11-08
	query for a single row against this table's primary key. this allows
	for fetching by an ID without having to know anything about the table.
	//*/

		$Table = static::GetTableInfo();

		return static::GetByField($Table->PrimaryKey, $ID);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {
	/*//
	@date 2022-11-08
	insert this object into the database with the supplied data. if checks
	and defaults are needed it is suggested you overrride this method with
	one that is more verbose ending with a call to this parent version.
	//*/

		$Table = static::GetTableInfo();
		$Fields = static::GetTableInsertMapFrom($Input);
		$DBM = new Manager;
		$Flags = 0;

		////////

		if($Table->HasAttribute(InsertReuseUnique::class))
		$Flags |= Verse::InsertReuseUnique;

		if($Table->HasAttribute(InsertUpdate::class))
		$Flags |= Verse::InsertUpdate;

		////////

		if($Input instanceof Common\Datastore)
		$Input = $Input->GetData();

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->Insert($Table->Name, $Flags)
			->PrimaryKey($Table->PrimaryKey)
			->Fields($Fields)
		);

		$Result = $SQL->Query($Input);

		////////

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		////////

		return static::GetByID($Result->GetInsertID());
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////


	/*******
	BlogUser::JoinExtendTables($SQL, NULL);

	BlogUser needs to know about the Blog.
	^ prepares GetPrefixedAlias(NULL) = ''
	^ calls Blog::JoinMainTables($SQL, '');
	^ calls Blog::JoinExtendTables($SQL, '');

		Blog needs Owning User
		^ prepares GetPrefixedAlias('') = 'BL'
		^ calls User::JoinMainTables($SQL, 'BL');
		^ calls User::JoinExtendTables($SQL, 'BL);

			User needs the Uploaded Avatar.
			^ prepares GetPrefixedAlias('BL') = 'BL_U'
			^ calls Upload::JoinMainTables($SQL, 'BL_U')
			^ calls Upload::JoinExtendTables($SQL, 'BL_U');

				Uploaded Avatar needs the owning User.
				^ prepares GetPrefixedAlias('BL_U') = 'BL_U_UP'
				^ calls User::JoinMainTables($SQL, 'BL_U_UP')
				watch out for recursion though. we need to not
				have something like a user fetch their avatar
				which fetches its owner which fetches its avatar
				which fetches its owner which fetches its avatar
				which fetches its owner which fetches its avatar
				which fetches its owner...

		Blog needs Uploaded Icon
		^ calls Upload::JoinMainTables($SQL, 'BL)
		^ calls Upload::JoinExtendTables($SQL, 'BL)

			Uploaded Icon needs the owning User.
			^ prepares GetPrefixedAlias('BL') = 'BL_UP'
			^ calls User::JoinMainTables($SQL, 'BL_UP')

	*******/

	static public function
	JoinMainTables(Verse $SQL, string $JAlias, string $JField, string $TPre='', ?string $TAlias=NULL):
	void {

		$Table = static::GetTableInfo();
		$Prefix = $Table->GetPrefixedAlias($TPre, $TAlias);

		$SQL->Join(sprintf(
			'%s ON %s=%s',
			$Table->GetAliasedTable($Prefix),
			match($JField) {
				'EntityUUID' => $Table->GetAliasedField('UUID', $Prefix),
				default      => $Table->GetAliasedPK($Prefix)
			},
			$Table::GetPrefixedField($JAlias, $JField)
		));

		return;
	}

	static public function
	JoinMainFields(Verse $SQL, string $TPre='', ?string $TAlias=NULL):
	void {

		$BTable = static::GetTableInfo();
		$Prefix = $BTable->GetPrefixedAlias($TPre, $TAlias);

		$SQL->Fields(static::GetTableSelectFields($Prefix));

		return;
	}

	static public function
	JoinExtendTables(Verse $SQL, string $JAlias='Main', ?string $TPre=NULL):
	void {

		return;
	}

	static public function
	JoinExtendFields(Verse $SQL, ?string $TPre=NULL):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Find(array $Input):
	Struct\PrototypeFindResult {

		$Output = new Struct\PrototypeFindResult;
		$DBM = new Manager;
		$Main = static::GetTableInfo();
		$PKField = $Main->GetAliasedPK('Main');

		$SQL = NULL;
		$Result = NULL;
		$Row = NULL;
		$PPCallable = NULL;

		////////

		$Opt = new Common\Datastore([
			'Sort'      => NULL,
			'Limit'     => 0,
			'Page'      => 1,
			'Debug'     => FALSE,
			'Filters'   => NULL,
			'Remappers' => NULL
		]);

		$Opt->MergeRight($Input);

		////////

		// begin compiling a standard select statement supplying all of the
		// information needed for paginated results.

		$SQL = (
			($DBM->NewVerse(static::$DBA))
			->Select(
				$Main->GetAliasedTable('Main'),
				Verse::SelectCalcFound
			)
			->Fields("Main.*")
			->Offset(($Opt['Page'] - 1) * $Opt['Limit'])
			->Limit($Opt['Limit'])
		);

		// allow extension classes to register options and supply
		// additional filters using those options to the query.

		static::FindExtendOptions($Opt);
		static::FindExtendTables($SQL, $Opt);
		static::FindExtendFilters($SQL, $Opt);

		// before checking if an extension class wants to add sorting
		// we will supply the default implementations for sorting by this
		// table primary key. it is optimal here as we have already asked
		// for the table private info.

		match($Opt['Sort']) {
			'pk-az'
			=> $SQL->Sort($PKField, $SQL::SortAsc),

			'pk-za'
			=> $SQL->Sort($PKField, $SQL::SortDesc),

			default
			=> static::FindExtendSorts($SQL, $Opt)
		};

		// ship the query off and see what we get back.

		//Common\Dump::Var($SQL, TRUE);

		$Result = $SQL->Query($Opt->GetData());

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		while($Row = $Result->Next())
		$Output->Push(new static($Row));

		// run the post processing filters.

		if(isset($Opt['Filters'])) {
			if(!is_iterable($Opt['Filters']))
			$Opt['Filters'] = [ $Opt['Filters'] ];

			foreach($Opt['Filters'] as $PPCallable)
			if(is_callable($PPCallable))
			$Output->Filter($PPCallable);
		}

		// run post process remapping.

		if(isset($Opt['Remappers'])) {
			if(!is_iterable($Opt['Remappers']))
			$Opt['Remappers'] = [ $Opt['Remappers'] ];

			foreach($Opt['Remappers'] as $PPCallable)
			if(is_callable($PPCallable))
			$Output->Remap($PPCallable);
		}

		////////

		// calc found and fetch count still seems the most consistently
		// useful method. had some issues where groups would screw this up
		// doing it a slightly faster way of retargeting the query for a
		// COUNT() instead.

		$Output->Total = (
			($SQL->GetDatabase())
			->Query('SELECT FOUND_ROWS() AS Total;')
			->Next()
			->Total
		);

		////////

		// finalise the output object with information from the query and
		// result set for pagination.

		$Output->Page = $Opt['Page'];
		$Output->Limit = $Opt['Limit'];

		if($Opt['Limit'])
		$Output->PageCount = ceil($Output->Total / $Output->Limit);

		if($Opt['Debug'])
		$Output->Result = $Result;

		return $Output;
	}

	static public function
	FindCount(array $Input):
	int {

		$Input['Page'] = 1;
		$Input['Limit'] = 0;

		$Result = static::Find($Input);

		return $Result->Total;
	}

	static protected function
	FindExtendOptions(Common\Datastore $Input):
	void {

		// $Input->SomeFilterName ??= NULL;

		return;
	}

	static protected function
	FindExtendTables(Verse $SQL, Common\Datastore $Input):
	void {

		static::JoinExtendTables($SQL);
		static::JoinExtendFields($SQL);

		return;
	}

	static protected function
	FindExtendFilters(Verse $SQL, Common\Datastore $Input):
	void {

		// if($Input['SomeProperty'] !== NULL)
		// $SQL->Where('SomeProperty=:SomeProperty')

		return;
	}

	static protected function
	FindExtendSorts(Verse $SQL, Common\Datastore $Input):
	void {

		// switch($Input['Sort']) {
		//	case 'whatever':
		//		$SQL->Sort('...');
		//	break;
		// }

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetTableInfo():
	Struct\TableClassInfo {
	/*//
	@date 2022-11-08
	return an object that describes the table this class is for. handles
	checking and populating the cache of this info.
	//*/

		if(Struct\TableClassCache::Has(static::class))
		return Struct\TableClassCache::Get(static::class);

		////////

		$Table = new Struct\TableClassInfo(static::class);

		if(!isset($Table->Name))
		throw new Exception(
			'Table Name undefined (TableClass attribute?)'
		);

		if(!isset($Table->PrimaryKey))
		throw new Exception(
			'Primary Key undefined (PrimaryKey attribute?)'
		);

		if(!isset($Table->ObjectKey))
		throw new Exception(
			'Object Key undefined (PrimaryKey attribute?)'
		);

		////////

		return Struct\TableClassCache::Set(static::class, $Table);
	}

	static public function
	GetTableInsertMapFrom(iterable $Dataset):
	array {
	/*//
	@date 2022-11-08
	return an array map of vlaid field names and placeholders that can be
	inserted for this type of object for use to passing to the Fields method
	of the Verse objects.
	//*/

		$Info = static::GetTableInfo();
		$Key = NULL;
		$Val = NULL;
		$Output = [];

		foreach($Dataset as $Key => $Val) {
			if(!array_key_exists($Key, $Info->Fields))
			throw new Exception(sprintf(
				'%s does not exist in %s field set',
				$Key,
				static::class
			));

			$Output[$Key] = ":{$Key}";
		}

		return $Output;
	}

	static public function
	GetTableSelectFields(string $TblKey, ?string $Prefix=NULL):
	array {
	/*//
	@date 2022-11-08
	//*/

		$Info = static::GetTableInfo();
		$FieldName = NULL;
		$FieldInfo = NULL;
		$Output = [];

		if($Prefix === NULL)
		$Prefix = "{$TblKey}_";

		foreach($Info->Fields as $FieldName => $FieldInfo)
		if(strpos($FieldInfo->Name,'_') !== 0)
		$Output[] = "{$TblKey}.{$FieldInfo->Name} AS {$Prefix}{$FieldInfo->Name}";

		return $Output;
	}

	static public function
	GetTableStrippedData(array $Dataset, string $Prefix):
	array {
	/*//
	@date 2022-11-08
	//*/

		$Property = NULL;
		$Value = NULL;
		$Output = [];

		//foreach($Dataset as $Property => $Value)
		//if(str_starts_with($Property,$Prefix))
		//$Output[str_replace($Prefix,'',$Property)] = $Value;

		foreach($Dataset as $Property => $Value)
		if(str_starts_with($Property, $Prefix))
		$Output[ substr($Property, strlen($Prefix)) ] = $Value;

		//error_log(json_encode($Output));
		//error_log('');

		return $Output;
	}

	static public function
	FromPrefixedDataset(array $Dataset, string $Prefix):
	static {
	/*//
	@date 2022-11-08
	//*/

		return new static(static::GetTableStrippedData(
			$Dataset,
			$Prefix
		));
	}

}
