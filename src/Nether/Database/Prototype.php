<?php

namespace Nether\Database;
use Nether;

use Exception;
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

		$SQL = (
			(Nether\Database::Get())
			->NewVerse()
			->Insert($Table->Name)
			->Fields($Fields)
		);

		$Result = $SQL->Query($Input);

		////////

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		////////

		return new static((array)$Input);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Find(iterable $Input):
	Struct\PrototypeFindResult {

		$Output = new Struct\PrototypeFindResult;
		$Main = static::GetTableInfo();

		$SQL = NULL;
		$Result = NULL;
		$Found = NULL;

		$Opt = [
			'Page'  => 1,
			'Limit' => 0,
			'Debug' => FALSE
		];

		////////

		$Opt = array_merge($Opt, $Input);
		static::FindExtendOptions($Opt);

		////////

		// compile and execute the query.

		$SQL = (
			(Nether\Database::Get())
			->NewVerse()
			->Select($Main->GetAliasedTable('Main'))
			->Fields("Main.*")
			->Offset(($Opt['Page'] - 1) * $Opt['Limit'])
			->Limit($Opt['Limit'])
		);

		$Result = $SQL->Query($Opt);

		if(!$Result->IsOK())
		throw new Exception($Result->GetError());

		while($Row = $Result->Next())
		$Output->Push(new static($Row));

		////////

		// recompile and re-execute the query in count mode.

		$SQL
		->Fields('COUNT(*) AS Total', TRUE)
		->Limit(0)
		->Offset(0);

		$Found = $SQL->Query($Opt);

		if(!$Found->IsOK())
		throw new Exception($Found->GetError());

		$Output->Total = $Found->Next()->Total;

		////////

		// finalise the output object.

		$Output->Page = $Opt['Page'];
		$Output->Limit = $Opt['Limit'];

		if($Opt['Limit'])
		$Output->PageCount = floor($Output->Total / $Output->Limit);

		if($Opt['Debug'])
		$Output->Result = $Result;

		return $Output;
	}

	static public function
	FindExtendOptions(array &$Input):
	void {

		// $Input->SomeFilterName ??= NULL;

		return;
	}

	static public function
	FindExtendFilters(Verse $SQL, array $Input):
	void {

		// if($Input['SomeProperty'] !== NULL)
		// $SQL->Where('SomeProperty=:SomeProperty')

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
