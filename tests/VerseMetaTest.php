<?php

namespace Nether\Database;

use PHPUnit;
use Nether;

use Nether\Common\Datastore;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

#[Meta\TableClass(Name: 'ExampleTable1', Comment: 'Example Table 1')]
#[Meta\MultiFieldIndex(Fields: [ 'ParentID', 'ChildID' ], Unique: TRUE)]
class ExampleTable1 {

	#[Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	public int $ID;

	#[Meta\TypeIntBig(Unsigned: TRUE)]
	#[Meta\ForeignKey(Table: 'OtherTable', Key:'ID')]
	public int $OtherID;

	#[Meta\TypeIntBig(Unsigned: TRUE)]
	#[Meta\FieldIndex]
	public string $KeyToIndex;

	#[Meta\TypeIntBig(Unsigned: TRUE)]
	public int $ParentID;

	#[Meta\TypeIntBig(Unsigned: TRUE)]
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

#[Meta\TableClass(Name: 'ExampleTable2')]
#[Meta\InsertIgnore]
#[Meta\InsertReuseUnique]
#[Meta\InsertUpdate]
class ExampleTable2 {

	#[Meta\TypeIntBig(Unsigned: TRUE)]
	public int $ID1;

	#[Meta\TypeIntBig(Unsigned: TRUE)]
	public int $ID2;

}

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class VerseMetaTest
extends PHPUnit\Framework\TestCase {

	static protected function
	GetConfigBasic():
	Datastore {

		$Config = new Datastore;
		$Config[Library::ConfConnections] = [
			'Default' => new Connection(
				'mysql', 'hostname', 'database',
				'username', 'password'
			)
		];

		return $Config;
	}

	static protected function
	GetManagerBasic():
	Manager {

		return new Manager(static::GetConfigBasic());
	}

	static protected function
	NewVerseBasic():
	Verse {

		return (
			(static::GetManagerBasic())
			->NewVerse('Default')
		);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	/** @test */
	public function
	TestCreateTableFromMeta():
	void {

		$Verse = Verse::FromMetaCreate('Nether\\Database\\ExampleTable1');
		$Verse->SetPretty(TRUE);
		$this->AssertEquals(ExampleTable1::GetPrettyCreateSQL(), (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestSelectFromMeta():
	void {

		$Verse = Verse::FromMetaSelect('Nether\Database\ExampleTable1');
		$Verse->Fields('*');

		$this->AssertEquals(
			'SELECT * FROM ExampleTable1',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestInsertFromMeta():
	void {

		$Verse = Verse::FromMetaInsert('Nether\Database\ExampleTable1');
		$Verse->Fields([ 'Field1'=> ':Value1' ]);

		$this->AssertEquals(
			'INSERT INTO ExampleTable1 (`Field1`) VALUES (:Value1)',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestUpdateFromMeta():
	void {

		$Verse = Verse::FromMetaUpdate('Nether\Database\ExampleTable1');
		$Verse->Fields([ 'Field1'=> ':Value1' ]);

		$this->AssertEquals(
			'UPDATE ExampleTable1 SET `Field1`=:Value1',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestDeleteFromMeta():
	void {

		$Verse = Verse::FromMetaDelete('Nether\Database\ExampleTable1');
		$Verse->Where('Field1=:Value1');

		$this->AssertEquals(
			'DELETE FROM ExampleTable1 WHERE (Field1=:Value1)',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestDropTableQuery():
	void {

		$Verse = Verse::FromMetaDropTable('Nether\Database\ExampleTable1');

		$this->AssertEquals(
			'DROP TABLE IF EXISTS `ExampleTable1`',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestInsertFlags():
	void {

		$Verse = Verse::FromMetaInsert('Nether\\Database\\ExampleTable2');

		$Flags = $Verse->GetFlags();

		$this->AssertGreaterThan(0, $Flags & Verse::InsertIgnore);
		$this->AssertGreaterThan(0, $Flags & Verse::InsertReuseUnique);
		$this->AssertGreaterThan(0, $Flags & Verse::InsertUpdate);

		return;
	}

}