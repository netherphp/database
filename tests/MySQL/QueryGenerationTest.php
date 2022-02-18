<?php

require_once(sprintf(
	'%s/_PackageGetDatabaseMock.php',
	dirname(__FILE__)
));

use Nether\Database;
use Nether\Database\Verse;

class QueryGenerationTest
extends PHPUnit\Framework\TestCase {

	use
	GetDatabaseMock;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	TestTableName = 'TableName';

	const
	TestSelectFields = [
		'Field1',
		'Field2',
		'Field3'
	];

	const
	TestInsertValues = [
		'Field1' => ':Value1',
		'Field2' => ':Value2',
		'Field3' => ':Value3'
	];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	/** @test */
	public function
	TestVerseSelectQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();
		$Argv = (object)[ ':ObjectIDs' => [42, 69, 1080] ];
		$Dataset = [];
		$Stage1Query = "SELECT Field1, Field2, Field3 FROM TableName WHERE (ObjectID IN(:ObjectIDs))";
		$Stage2Query = "SELECT Field1, Field2, Field3 FROM TableName WHERE (ObjectID IN(:__ObjectIDs__0,:__ObjectIDs__1,:__ObjectIDs__2))";

		$SQL = (
			$Verse
			->Select(static::TestTableName)
			->Values(static::TestSelectFields)
			->Where('ObjectID IN(:ObjectIDs)')
			->GetSQL()
		);

		////////

		// check that the verse looks good.

		$this->AssertEquals($Stage1Query, $SQL);

		// the rest of this is sanity checking a few other core features
		// of the database lib as well.

		// check that the query builder was able to map our data.

		$DB->Query_BuildDataset($SQL, $Argv, $Dataset);

		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) === 1);
		$this->AssertTrue(count($Dataset[':ObjectIDs']) === 3);

		// check that the query builder was able to expand our data and sql.

		$DB->Query_ExpandDataset($SQL, $Argv, $Dataset);

		$this->AssertEquals($Stage2Query, $SQL);
		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) === 3);
		$this->AssertTrue(array_key_exists(':__ObjectIDs__0', $Dataset));
		$this->AssertTrue(array_key_exists(':__ObjectIDs__1', $Dataset));
		$this->AssertTrue(array_key_exists(':__ObjectIDs__2', $Dataset));

		return;
	}

	/** @test */
	public function
	TestVerseInsertQuery(){

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QueryInsertNormal = "INSERT INTO TableName (Field1,Field2,Field3) VALUES (:Value1,:Value2,:Value3)";
		$QueryInsertIgnore = "INSERT IGNORE INTO TableName (Field1,Field2,Field3) VALUES (:Value1,:Value2,:Value3)";
		$QueryOnDupliateKeyUpdate = "ON DUPLICATE KEY UPDATE Field1=:Value1,Field2=:Value2,Field3=:Value3";

		$QueryInsertNormalUpdate = "{$QueryInsertNormal} {$QueryOnDupliateKeyUpdate}";
		$QueryInsertIgnoreUpdate = "{$QueryInsertIgnore} {$QueryOnDupliateKeyUpdate}";

		////////

		$Query = (
			$Verse
			->Insert(static::TestTableName)
			->Values(static::TestInsertValues)
			->GetSQL()
		);

		$this->AssertEquals($QueryInsertNormal, $Query);

		////////

		$Query = (
			$Verse
			->Insert(static::TestTableName, Verse::InsertIgnore)
			->Values(static::TestInsertValues)
			->GetSQL()
		);

		////////

		$this->AssertEquals($QueryInsertIgnore, $Query);

		$Query = (
			$Verse
			->Insert(static::TestTableName, Verse::InsertUpdate)
			->Values(static::TestInsertValues)
			->GetSQL()
		);

		$this->AssertEquals($QueryInsertNormalUpdate, $Query);

		////////

		$Query = (
			$Verse
			->Insert(static::TestTableName, (Verse::InsertIgnore | Verse::InsertUpdate))
			->Values(static::TestInsertValues)
			->GetSQL()
		);

		$this->AssertEquals($QueryInsertIgnoreUpdate, $Query);

		return;
	}

}