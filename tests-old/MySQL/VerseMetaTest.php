<?php

require_once(sprintf(
	'%s/_PackageGetDatabaseMock.php',
	dirname(__FILE__)
));

use Nether\Option;
use Nether\Database\Verse;
use Nether\Database\Struct\TableClassInfo;

//Option::Set('Nether.Database.Verse.ConnectionDefault', NULL);

#[Meta\TableClass(Name: 'ExampleTable1', Comment: 'Example Table 1')]
#[Meta\MultiFieldIndex(Fields: [ 'ParentID', 'ChildID' ], Unique: TRUE)]
class ExampleTable1 {

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	public int $ID;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Nether\Database\Meta\ForeignKey(Table: 'OtherTable', Key:'ID')]
	public int $OtherID;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Nether\Database\Meta\FieldIndex]
	public string $KeyToIndex;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int $ParentID;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int $ChildID;


	////////

	static public function
	GetPrettyCreateSQL():
	string {

		$Query = "CREATE TABLE `ExampleTable1` (\n";
		$Query .= "\t`ID` BIGINT UNSIGNED AUTO_INCREMENT,\n";
		$Query .= "\t`OtherID` BIGINT UNSIGNED,\n";
		$Query .= "\t`KeyToIndex` BIGINT UNSIGNED,\n";
		$Query .= "\t`ParentID` BIGINT UNSIGNED,\n";
		$Query .= "\t`ChildID` BIGINT UNSIGNED,\n";
		$Query .= "\tINDEX `IdxExampleTable1KeyToIndex` (`KeyToIndex`) USING BTREE,\n";
		$Query .= "\tUNIQUE `UnqExampleTable1ParentIDChildID` (`ParentID`,`ChildID`),\n";
		$Query .= "\tINDEX `FnkExampleTable1OtherTableOtherID` (`OtherID`) USING BTREE,\n";
		$Query .= "\tCONSTRAINT `FnkExampleTable1OtherTableOtherID` FOREIGN KEY(`OtherID`) REFERENCES `OtherTable` (`ID`) ON UPDATE CASCADE ON DELETE CASCADE\n";
		$Query .= ")\n";
		$Query .= "CHARSET=utf8mb4\n";
		$Query .= "COLLATE=utf8mb4_general_ci\n";
		$Query .= "ENGINE=InnoDB\n";
		$Query .= "COMMENT=\"Example Table 1\"";

		return $Query;
	}

}

class VerseMetaTest
extends PHPUnit\Framework\TestCase {

	use
	GetDatabaseMock;

	/** @test */
	public function
	TestCreateFromMeta():
	void {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();
		$QueryCreate1 = ExampleTable1::GetPrettyCreateSQL();

		$Table = new TableClassInfo('ExampleTable1');

		$Verse
		->Create($Table->Name)
		->Comment($Table->Comment)
		->Fields($Table->GetFieldList())
		->ForeignKey($Table->GetForeignKeyList())
		->Index($Table->GetIndexList());

		$this->AssertEquals($QueryCreate1, (string)$Verse->SetPretty(TRUE));

		return;
	}

	/** @test */
	public function
	TestCreateFromMetaHelper():
	void {

		$Verse = Verse::FromMetaCreate('ExampleTable1');
		$QueryCreate1 = ExampleTable1::GetPrettyCreateSQL();

		$this->AssertEquals($QueryCreate1, (string)$Verse->SetPretty(TRUE));

		return;
	}

	/** @test */
	public function
	TestSelectFromBasicMetaHelper():
	void {

		$Query1 = 'SELECT Field1 FROM ExampleTable1';

		$Verse = (
			Verse::FromMeta('ExampleTable1', Verse::ModeSelect)
			->Column('Field1')
		);

		$this->AssertEquals($Query1, (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestSelectFromMetaHelper():
	void {

		$Query1 = 'SELECT Field1 FROM ExampleTable1';

		$Verse = (
			Verse::FromMetaSelect('ExampleTable1')
			->Column('Field1')
		);

		$this->AssertEquals($Query1, (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestUpdateFromMetaHelper():
	void {

		$Query1 = 'UPDATE ExampleTable1 SET `Field1`=:Value1';

		$Verse = (
			Verse::FromMetaUpdate('ExampleTable1')
			->Values(['Field1' => ':Value1'])
		);

		$this->AssertEquals($Query1, (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestDeleteFromMetaHelper():
	void {

		$Query1 = 'DELETE FROM ExampleTable1 WHERE (Field1=:Value1) LIMIT 1';

		$Verse = (
			Verse::FromMetaDelete('ExampleTable1')
			->Where('Field1=:Value1')
			->Limit(1)
		);

		$this->AssertEquals($Query1, (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestInsertFromMetaHelper():
	void {

		$Query1 = 'INSERT INTO ExampleTable1 (`Field1`) VALUES (:Value1)';

		$Verse = (
			Verse::FromMetaInsert('ExampleTable1')
			->Values(['Field1' => ':Value1'])
		);

		$this->AssertEquals($Query1, (string)$Verse);

		return;
	}

}
