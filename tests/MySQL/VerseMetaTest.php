<?php

require_once(sprintf(
	'%s/_PackageGetDatabaseMock.php',
	dirname(__FILE__)
));

use Nether\Database;
use Nether\Database\Verse;
use Nether\Database\Struct\TableClassInfo;

#[Nether\Database\Meta\TableClass(Name: 'ExampleTable1', Comment: 'Example Table 1')]
class ExampleTable1 {

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	public int $ID;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Nether\Database\Meta\ForeignKey(Table: 'OtherTable', Key:'ID')]
	public int $OtherID;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Nether\Database\Meta\FieldIndex]
	public string $KeyToIndex;

}

class VerseMetaTest
extends PHPUnit\Framework\TestCase {

	use
	GetDatabaseMock;

	/** @test */
	public function
	TestCreateFromAttribs():
	void {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();
		$Verse->Pretty = TRUE;
		$Table = new TableClassInfo('ExampleTable1');

		$QueryCreate1 = "CREATE TABLE `ExampleTable1` (\n";
		$QueryCreate1 .= "	`ID` BIGINT UNSIGNED AUTO_INCREMENT,\n";
		$QueryCreate1 .= "	`OtherID` BIGINT UNSIGNED,\n";
		$QueryCreate1 .= "	`KeyToIndex` BIGINT UNSIGNED,\n";
		$QueryCreate1 .= "	INDEX `IdxKeyToIndex` (`KeyToIndex`) USING BTREE,\n";
		$QueryCreate1 .= "	INDEX `FnkExampleTable1OtherTableOtherID` (`OtherID`) USING BTREE,\n";
		$QueryCreate1 .= "	CONSTRAINT `FnkExampleTable1OtherTableOtherID` FOREIGN KEY(`OtherID`) REFERENCES `OtherTable` (`ID`) ON UPDATE CASCADE ON DELETE CASCADE\n";
		$QueryCreate1 .= ")\n";
		$QueryCreate1 .= "CHARSET=utf8mb4\n";
		$QueryCreate1 .= "COLLATE=utf8mb4_general_ci\n";
		$QueryCreate1 .= "ENGINE=InnoDB\n";
		$QueryCreate1 .= "COMMENT=\"Example Table 1\"";

		$Verse
		->Create($Table->Name)
		->Comment($Table->Comment)
		->Fields($Table->GetFieldList())
		->ForeignKey($Table->GetForeignKeyList())
		->Index($Table->GetIndexList());

		$this->AssertEquals($QueryCreate1, (string)$Verse);

		return;
	}

}
