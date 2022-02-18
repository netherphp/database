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

	const
	TestCreateFields = [
		'Field1 INT',
		'Field2 BIGINT',
		'Field3 VARCHAR(255)'
	];

	const
	TestConditionBasic = 'Field1=:Value1';

	const
	TestConditionArray = 'Field1 IN(:ValueList)';

	const
	TestValueList = [ ':ValueList' => [42, 69, 1080] ];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	/** @test */
	public function
	TestVerseSelectQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();
		$Dataset = [];
		$Stage1Query = "SELECT Field1, Field2, Field3 FROM TableName WHERE (Field1 IN(:ValueList))";
		$Stage2Query = "SELECT Field1, Field2, Field3 FROM TableName WHERE (Field1 IN(:__ValueList__0,:__ValueList__1,:__ValueList__2))";

		$SQL = (
			$Verse
			->Select(static::TestTableName)
			->Values(static::TestSelectFields)
			->Where(static::TestConditionArray)
			->GetSQL()
		);

		////////

		// check that the verse looks good.

		$this->AssertEquals($Stage1Query, $SQL);

		// the rest of this is sanity checking a few other core features
		// of the database lib as well.

		// check that the query builder was able to map our data.

		$DB->Query_BuildDataset(
			$SQL,
			(object)static::TestValueList,
			$Dataset
		);

		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) === 1);
		$this->AssertTrue(count($Dataset[':ValueList']) === 3);

		// check that the query builder was able to expand our data and sql.

		$DB->Query_ExpandDataset(
			$SQL,
			(object)static::TestValueList,
			$Dataset
		);

		$this->AssertEquals($Stage2Query, $SQL);
		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) === 3);
		$this->AssertTrue(array_key_exists(':__ValueList__0', $Dataset));
		$this->AssertTrue(array_key_exists(':__ValueList__1', $Dataset));
		$this->AssertTrue(array_key_exists(':__ValueList__2', $Dataset));

		return;
	}

	/** @test */
	public function
	TestVerseInsertQuery() {

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

	/** @test */
	public function
	TestVerseDeleteQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QuerySimple = 'DELETE FROM TableName';
		$QueryCondition = 'DELETE FROM TableName WHERE (Field1=:Value1)';

		$Query = (
			$Verse
			->Delete(static::TestTableName)
			->GetSQL()
		);

		$this->AssertEquals($QuerySimple, $Query);

		$Query = (
			$Verse
			->Delete(static::TestTableName)
			->Where(static::TestConditionBasic)
			->GetSQL()
		);

		$this->AssertEquals($QueryCondition, $Query);
		return;
	}

	/** @test */
	public function
	TestVerseUpdateQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QuerySimple = 'UPDATE TableName SET Field1=:Value1,Field2=:Value2,Field3=:Value3';
		$QueryCondition = 'UPDATE TableName SET Field1=:Value1,Field2=:Value2,Field3=:Value3 WHERE (Field1=:Value1)';

		$Query = (
			$Verse
			->Update(static::TestTableName)
			->Set(static::TestInsertValues)
			->GetSQL()
		);

		$this->AssertEquals($QuerySimple, $Query);

		$Query = (
			$Verse
			->Update(static::TestTableName)
			->Set(static::TestInsertValues)
			->Where(static::TestConditionBasic)
			->GetSQL()
		);

		$this->AssertEquals($QueryCondition, $Query);

		return;
	}

	/** @test */
	public function
	TestVerseCreateQuery(){

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QuerySimple = 'CREATE TABLE `TableName` ( Field1 INT, Field2 BIGINT, Field3 VARCHAR(255) ) CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ENGINE=InnoDB';

		$Query = (
			$Verse
			->Create(static::TestTableName)
			->Fields(static::TestCreateFields)
			->GetSQL()
		);

		$this->AssertEquals($QuerySimple, $Query);
		return;
	}

}