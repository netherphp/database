<?php

require_once(sprintf(
	'%s/_PackageGetDatabaseMock.php',
	dirname(__FILE__)
));

use Nether\Database;
use Nether\Database\Verse;

class VerseQueryTest
extends PHPUnit\Framework\TestCase {

	use
	GetDatabaseMock;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	const
	TestTableName = 'TableName';

	const
	TestTableJoin = 'Table2 ON TableName.Field1=Table2.Field1';

	const
	TestTableJoins = [
		'Table2 ON TableName.Field1=Table2.Field1',
		'Three' => 'Table3 ON TableName.Field1=Table3.Field1'
	];

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
	TestConditionBasic2 = 'Field2=:Value2';

	const
	TestConditionArray = 'Field1 IN(:ValueList)';

	const
	TestValueList = [ ':ValueList' => [42, 69, 1080] ];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	/** @test */
	public function
	TestSelectQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();
		$Dataset = [];
		$QuerySimple = "SELECT Field1, Field2, Field3 FROM TableName WHERE (Field1 IN(:ValueList))";
		$QueryExpanded = "SELECT Field1, Field2, Field3 FROM TableName WHERE (Field1 IN(:__ValueList__0,:__ValueList__1,:__ValueList__2))";
		$QueryPretty = "SELECT Field1, Field2, Field3\nFROM TableName\nWHERE (Field1 IN(:ValueList))";

		$SQL = (
			$Verse
			->Select(static::TestTableName)
			->Values(static::TestSelectFields)
			->Where(static::TestConditionArray)
			->GetSQL()
		);

		////////

		// check that the verse looks good.

		$this->AssertEquals($QuerySimple, $SQL);
		$this->AssertEquals($QuerySimple, (string)$Verse);

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

		$this->AssertEquals($QueryExpanded, $SQL);
		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) === 3);
		$this->AssertTrue(array_key_exists(':__ValueList__0', $Dataset));
		$this->AssertTrue(array_key_exists(':__ValueList__1', $Dataset));
		$this->AssertTrue(array_key_exists(':__ValueList__2', $Dataset));

		// check that pretty print does something.

		$Verse->Pretty = TRUE;
		$this->AssertEquals($QueryPretty, $Verse->GetSQL());

		return;
	}

	/** @test */
	public function
	TestSelectJoinedQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QueryJoin1 = "SELECT Field1, Field2, Field3 FROM TableName LEFT JOIN Table2 ON TableName.Field1=Table2.Field1 WHERE (Field1 IN(:ValueList))";
		$QueryJoin2 = "SELECT Field1, Field2, Field3 FROM TableName LEFT JOIN Table2 ON TableName.Field1=Table2.Field1 LEFT JOIN Table3 ON TableName.Field1=Table3.Field1 WHERE (Field1 IN(:ValueList))";

		////////

		$SQL = (
			$Verse
			->Select(static::TestTableName)
			->Join(static::TestTableJoin)
			->Values(static::TestSelectFields)
			->Where(static::TestConditionArray)
			->GetSQL()
		);

		$this->AssertEquals($QueryJoin1, $SQL);

		////////

		$SQL = (
			$Verse
			->Select(static::TestTableName)
			->Join(static::TestTableJoins)
			->Values(static::TestSelectFields)
			->Where(static::TestConditionArray)
			->GetSQL()
		);

		$this->AssertEquals($QueryJoin2, $SQL);

		return;
	}

	/** @test */
	public function
	TestSelectSortedQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QuerySort1 = "SELECT Field1, Field2, Field3 FROM TableName ORDER BY Field1 ASC";
		$QuerySort2 = "SELECT Field1, Field2, Field3 FROM TableName ORDER BY Field2 DESC";

		////////

		$Verse
		->Select(static::TestTableName)
		->Values(static::TestSelectFields)
		->Sort('Field1', $Verse::SortAsc);

		$this->AssertEquals($QuerySort1, (string)$Verse);

		$Verse
		->Select(static::TestTableName)
		->Values(static::TestSelectFields)
		->OrderBy('Field2', $Verse::SortDesc);

		$this->AssertEquals($QuerySort2, (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestSelectGroupedQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QueryGroup1 = "SELECT Field1, Field2, Field3 FROM TableName GROUP BY Field1";
		$QueryGroup2 = "SELECT Field1, Field2, Field3 FROM TableName GROUP BY Field1, Field2";

		////////

		$Verse
		->Select(static::TestTableName)
		->Values(static::TestSelectFields)
		->Group('Field1');

		$this->AssertEquals($QueryGroup1, (string)$Verse);

		$Verse
		->Select(static::TestTableName)
		->Values(static::TestSelectFields)
		->Group('Field1')
		->GroupBy('Field2');

		$this->AssertEquals($QueryGroup2, (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestSelectLimitedQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QueryLimit1 = "SELECT Field1, Field2, Field3 FROM TableName LIMIT 42";
		$QueryLimit2 = "SELECT Field1, Field2, Field3 FROM TableName LIMIT 42 OFFSET 69";

		////////

		$Verse
		->Select(static::TestTableName)
		->Values(static::TestSelectFields)
		->Limit(42);

		$this->AssertEquals($QueryLimit1, (string)$Verse);

		////////

		$Verse
		->Select(static::TestTableName)
		->Values(static::TestSelectFields)
		->Limit(42)
		->Offset(69);

		$this->AssertEquals($QueryLimit2, (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestSelectConditionedQuery() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QueryWhere = "SELECT Field1, Field2, Field3 FROM TableName WHERE (Field1=:Value1)";
		$QueryHaving = "SELECT Field1, Field2, Field3 FROM TableName HAVING (Field1=:Value1)";
		$QueryWhereHaving = "SELECT Field1, Field2, Field3 FROM TableName WHERE (Field1=:Value1) HAVING (Field2=:Value2)";

		////////

		$SQL = (
			$Verse
			->Select(static::TestTableName)
			->Values(static::TestSelectFields)
			->Where(static::TestConditionBasic)
			->GetSQL()
		);

		$this->AssertEquals($QueryWhere, $SQL);

		////////

		$SQL = (
			$Verse
			->Select(static::TestTableName)
			->Values(static::TestSelectFields)
			->Having(static::TestConditionBasic)
			->GetSQL()
		);

		$this->AssertEquals($QueryHaving, $SQL);

		////////

		$SQL = (
			$Verse
			->Select(static::TestTableName)
			->Values(static::TestSelectFields)
			->Where(static::TestConditionBasic)
			->Having(static::TestConditionBasic2)
			->GetSQL()
		);

		$this->AssertEquals($QueryWhereHaving, $SQL);

		return;
	}

	/** @test */
	public function
	TestInsertQuery() {

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
	TestDeleteQuery() {

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
	TestUpdateQuery() {

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
	TestCreateQuery(){

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QueryCreate1 = 'CREATE TABLE `TableName` ( Field1 INT, Field2 BIGINT, Field3 VARCHAR(255) ) CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ENGINE=InnoDB';
		$QueryCreate2 = 'CREATE TABLE `TableName` ( Field1 INT, Field2 BIGINT, Field3 VARCHAR(255) ) CHARSET=lulz COLLATE=lulz_ci ENGINE=LulzDB';

		////////

		$Verse
		->Create(static::TestTableName)
		->Fields(static::TestCreateFields)
		->GetSQL();

		$this->AssertEquals($QueryCreate1, (string)$Verse);

		////////

		$Verse
		->Create(static::TestTableName)
		->Fields(static::TestCreateFields)
		->Charset('lulz')
		->Collate('lulz_ci')
		->Engine('LulzDB')
		->GetSQL();

		$this->AssertEquals($QueryCreate2, (string)$Verse);

		return;
	}

	/** @test */
	public function
	TestTableMethodAliases() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();

		$QuerySimple = 'SELECT Field1, Field2, Field3 FROM TableName';

		////////

		$Verse
		->Select(static::TestTableName)
		->Fields(static::TestSelectFields);

		$this->AssertEquals($QuerySimple, (string)$Verse);

		////////

		$Verse
		->Reset()
		->Table(static::TestTableName)
		->Fields(static::TestSelectFields);

		$this->AssertEquals($QuerySimple, (string)$Verse);

		////////

		$Verse
		->Reset()
		->From(static::TestTableName)
		->Fields(static::TestSelectFields);

		$this->AssertEquals($QuerySimple, (string)$Verse);

		////////

		$Verse
		->Reset()
		->Into(static::TestTableName)
		->Fields(static::TestSelectFields);

		$this->AssertEquals($QuerySimple, (string)$Verse);

		return;
	}

}