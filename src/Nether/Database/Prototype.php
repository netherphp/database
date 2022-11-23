<?php

namespace Nether\Database;
use Nether;

use Exception;
use Nether\Database\Meta\InsertReuseUnique;
use Nether\Database\Meta\InsertUpdate;
use Nether\Object\Datastore;
use Nether\Database\Verse;

class Prototype
extends Nether\Object\Prototype {

	public function
	Drop():
	static {
	/*//
	@date 2022-11-08
	drop this object from the database for real.
	//*/

		$Table = static::GetTableInfo();

		$SQL = (
			(Nether\Database::Get())
			->NewVerse()
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
		$Key = NULL;
		$Val = NULL;

		////////

		$SQL = (
			(Nether\Database::Get())
			->NewVerse()
			->Update($Table->Name)
			->Set($Fields)
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

		if(!array_key_exists($Field, $Table->Fields))
		throw new Exception("{$Field} not found on {$Table->Name}");

		////////

		$SQL = (
			(Nether\Database::Get())
			->NewVerse()
			->Select($Table->Name)
			->Fields('*')
			->Where("{$Field}=:FieldValue")
			->Limit(1)
		);

		$Result = $SQL->Query([
			':FieldValue' => $Value
		]);

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
		$Flags = 0;

		////////

		if($Table->HasAttribute(InsertReuseUnique::class))
		$Flags |= Verse::InsertReuseUnique;

		if($Table->HasAttribute(InsertUpdate::class))
		$Flags |= Verse::InsertUpdate;

		////////

		if($Input instanceof Datastore)
		$Input = $Input->GetData();

		$SQL = (
			(Nether\Database::Get())
			->NewVerse()
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

	static public function
	Find(array $Input):
	Struct\PrototypeFindResult {

		$Output = new Struct\PrototypeFindResult;
		$Main = static::GetTableInfo();
		$PKField = $Main->GetPrefixedKey('Main');

		$SQL = NULL;
		$Result = NULL;
		$Found = NULL;
		$Row = NULL;

		////////

		$Opt = new Datastore([
			'Sort'  => NULL,
			'Limit' => 0,
			'Page'  => 1,
			'Debug' => FALSE
		]);

		$Opt->MergeRight($Input);

		////////

		// begin compiling a standard select statement supplying all of the
		// information needed for paginated results.

		$SQL = (
			(Nether\Database::Get())
			->NewVerse()
			->Select($Main->GetAliasedTable('Main'))
			->Fields("Main.*")
			->Offset(($Opt['Page'] - 1) * $Opt['Limit'])
			->Limit($Opt['Limit'])
		);

		// allow extension classes to register options and supply
		// additional filters using those options to the query.

		static::FindExtendOptions($Opt);
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

		$Result = $SQL->Query($Opt->GetData());

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		while($Row = $Result->Next())
		$Output->Push(new static($Row));

		////////

		// recompile and re-execute the query in count mode.
		// people claim this is better than calc found rows.

		$SQL
		->Fields('COUNT(*) AS Total', TRUE)
		->Offset(0)
		->Limit(0);

		$Found = $SQL->Query($Opt->GetData());

		if(!$Found->IsOK())
		throw new Exception($Found->GetError());

		if($Found->GetCount() !== 0)
		$Output->Total = $Found->Next()->Total;

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

	static protected function
	FindExtendOptions(Datastore $Input):
	void {

		// $Input->SomeFilterName ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Verse $SQL, Datastore $Input):
	void {

		// if($Input['SomeProperty'] !== NULL)
		// $SQL->Where('SomeProperty=:SomeProperty')

		return;
	}

	static protected function
	FindExtendSorts(Verse $SQL, Datastore $Input):
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

		foreach($Dataset as $Key => $Val)
		if(array_key_exists($Key, $Info->Fields))
		$Output[$Key] = ":{$Key}";

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

		foreach($Dataset as $Property => $Value)
		if(str_starts_with($Property,$Prefix))
		$Output[str_replace($Prefix,'',$Property)] = $Value;

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
