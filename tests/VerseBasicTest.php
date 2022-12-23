<?php

namespace Nether\Database;

use PHPUnit;
use Nether;

use Nether\Object\Datastore;

new Nether\Database\Library;

class VerseTest
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
	TestBasic():
	void {

		$Verse = static::NewVerseBasic();

		$this->AssertTrue($Verse->HasDatabase());
		$this->AssertInstanceOf(
			Connection::class,
			$Verse->GetDatabase()
		);

		$Verse
		->Select('TestTable')
		->Fields('Field0')
		->Where('Field1=:Value1');

		$Verse->Fields([ 'Field1', 'Field2' ], TRUE);

		$this->AssertEquals(
			'SELECT `Field1`,`Field2` FROM TestTable WHERE (Field1=:Value1)',
			(string)$Verse
		);

		////////

		$Verse->Where('Field2=:Value2');

		$this->AssertEquals(
			'SELECT `Field1`,`Field2` FROM TestTable WHERE (Field1=:Value1) AND (Field2=:Value2)',
			(string)$Verse
		);

		////////

		$Verse->Table('OtherTable', TRUE);
		$this->AssertEquals(
			'SELECT `Field1`,`Field2` FROM OtherTable WHERE (Field1=:Value1) AND (Field2=:Value2)',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestPrettyPrint():
	void {

		$Verse = static::NewVerseBasic();
		$Verse->Pretty = TRUE;

		$Verse
		->Select('TestTable')
		->Fields([ 'Field1', 'Field2' ])
		->Where('Field1=:Value1')
		->Where('Field2=:Value2');

		$this->AssertEquals(
			"SELECT `Field1`,`Field2`\n".
			"FROM TestTable\n".
			"WHERE (Field1=:Value1)\n".
			"AND (Field2=:Value2)",
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestArrayExpansion():
	void {

		$Verse = static::NewVerseBasic();
		$DB = $Verse->GetDatabase();
		$Valueset = [ 'Value1'=> [ 'One', 'Two' ] ];
		$Expand = [];

		$Verse
		->Select('TestTable')
		->Fields('Field1')
		->Where('Field1 IN(:Value1)');

		// this first step is a basic sanity check on the dataset.

		$DB->Query_BuildDataset($Verse, $Valueset, $Expand);
		$this->AssertCount(1, $Expand);
		$this->AssertArrayHasKey(':Value1', $Expand);

		// this step causes the query expansion.

		$DB->Query_ExpandDataset($Verse, $Valueset, $Expand);
		$this->AssertCount(2, $Expand);
		$this->AssertArrayHasKey(':__Value1__0', $Expand);
		$this->AssertArrayHasKey(':__Value1__1', $Expand);

		$this->AssertEquals(
			'SELECT `Field1` FROM TestTable WHERE (Field1 IN(:__Value1__0,:__Value1__1))',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestJoinedQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Select('TestTable T')
		->Join('OtherTable O ON T.ID=O.TID')
		->Fields([ 'F1', 'F2' ])
		->Where('F1=:V1');

		$this->AssertEquals(
			'SELECT `F1`,`F2` FROM TestTable T LEFT JOIN OtherTable O ON T.ID=O.TID WHERE (F1=:V1)',
			(string)$Verse
		);

		////////

		$Verse
		->Join('AnotherTable A ON T.ID=A.TID');

		$this->AssertEquals(
			'SELECT `F1`,`F2` FROM TestTable T LEFT JOIN OtherTable O ON T.ID=O.TID LEFT JOIN AnotherTable A ON T.ID=A.TID WHERE (F1=:V1)',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestSortedQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Select('TableName')
		->Fields('*')
		->Sort('Field1');

		$this->AssertEquals(
			'SELECT * FROM TableName ORDER BY Field1 ASC',
			(string)$Verse
		);

		$Verse
		->Sort('Field2', $Verse::SortDesc);

		$this->AssertEquals(
			'SELECT * FROM TableName ORDER BY Field1 ASC, Field2 DESC',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestGroupedQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Select('TableName')
		->Fields('*')
		->Group('Field1');

		$this->AssertEquals(
			'SELECT * FROM TableName GROUP BY Field1',
			(string)$Verse
		);

		$Verse->Group('Field2');

		$this->AssertEquals(
			'SELECT * FROM TableName GROUP BY Field1, Field2',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestGroupedHavingQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Select('TableName')
		->Fields('*')
		->Having('Field1=:Value1');

		$this->AssertEquals(
			'SELECT * FROM TableName HAVING (Field1=:Value1)',
			(string)$Verse
		);

		////////

		$Verse->Having('Field2=:Value2');

		$this->AssertEquals(
			'SELECT * FROM TableName HAVING (Field1=:Value1) AND (Field2=:Value2)',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestLimitedQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Select('TableName')
		->Fields('*')
		->Limit(10);

		$this->AssertEquals(
			'SELECT * FROM TableName LIMIT 10',
			(string)$Verse
		);

		$Verse->Offset(20);

		$this->AssertEquals(
			'SELECT * FROM TableName LIMIT 10 OFFSET 20',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestNamedBuildingOverwriting():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Select('TableName')
		->Fields('*');

		////////

		$Verse->Where([ 'TestCond'=> 'Field1=:Value1' ]);

		$this->AssertEquals(
			'SELECT * FROM TableName WHERE (Field1=:Value1)',
			(string)$Verse
		);

		////////

		$Verse->Where([ 'TestCond'=> 'Field2=:Value2' ]);

		$this->AssertEquals(
			'SELECT * FROM TableName WHERE (Field2=:Value2)',
			(string)$Verse
		);

		////////

		$Verse->Sort([ 'TestSort'=> 'Field1' ]);

		$this->AssertEquals(
			'SELECT * FROM TableName WHERE (Field2=:Value2) ORDER BY Field1 ASC',
			(string)$Verse
		);

		////////

		$Verse->Sort([ 'TestSort'=> 'Field2' ]);

		$this->AssertEquals(
			'SELECT * FROM TableName WHERE (Field2=:Value2) ORDER BY Field2 ASC',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestInsertQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Insert('TableName')
		->Values([ 'Field1'=> ':Value1', 'Field2'=> ':Value2' ]);

		$this->AssertEquals(
			'INSERT INTO TableName (`Field1`,`Field2`) VALUES (:Value1,:Value2)',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestUpdateQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Update('TableName')
		->Fields([ 'Field1'=> ':Value1', 'Field2'=> ':Value2' ]);

		$this->AssertEquals(
			'UPDATE TableName SET `Field1`=:Value1,`Field2`=:Value2',
			(string)$Verse
		);

		$Verse->Where('Field0=:Cond0');

		$this->AssertEquals(
			'UPDATE TableName SET `Field1`=:Value1,`Field2`=:Value2 WHERE (Field0=:Cond0)',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestDeleteQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Delete('TableName')
		->Where('Field1=:Value1');

		$this->AssertEquals(
			'DELETE FROM TableName WHERE (Field1=:Value1)',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestDropTableQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse->DropTable('TableName');

		$this->AssertEquals(
			'DROP TABLE IF EXISTS `TableName`',
			(string)$Verse
		);

		return;
	}

}