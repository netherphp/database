<?php

namespace Nether\Database;

use PHPUnit;
use Nether;

use Throwable;
use Nether\Common\Datastore;

////////

new Nether\Database\Library;

////////

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

		////////

		$Verse->Join([ 'Test1'=> 'TestJoin1' ]);
		$Verse->Join([ 'Test1'=> 'TestJoin2' ], $Verse::JoinNatural);

		$this->AssertEquals(
			'SELECT * FROM TableName NATURAL JOIN TestJoin2 WHERE (Field2=:Value2) ORDER BY Field2 ASC',
			(string)$Verse
		);

		$Verse->Join([ 'TestJoin3' ], $Verse::JoinNatural);

		$this->AssertEquals(
			'SELECT * FROM TableName NATURAL JOIN TestJoin2 NATURAL JOIN TestJoin3 WHERE (Field2=:Value2) ORDER BY Field2 ASC',
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

	/** @test */
	public function
	TestCreateQuery():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Create('NewTable')
		->Fields([ 'Field1 BIGINT UNSIGNED', 'Field2 VARCHAR(36)' ])
		->Charset('OMG')
		->Collate('WTF')
		->Engine('BBQ');

		$this->AssertEquals(
			'CREATE TABLE `NewTable` ( Field1 BIGINT UNSIGNED, Field2 VARCHAR(36) ) CHARSET=OMG COLLATE=WTF ENGINE=BBQ',
			(string)$Verse
		);

		return;
	}

	/** @test */
	public function
	TestPrimaryKey():
	void {

		// i do not specifically remember the use case where i wanted this
		// it does not really make too much sense for most general things
		// but i know if i remove it i'll have a bad day at work.

		$Verse = static::NewVerseBasic();

		$this->AssertNull($Verse->GetPrimaryKey());

		$Verse->PrimaryKey('What');

		$this->AssertEquals('What', $Verse->GetPrimaryKey());

		return;
	}

	/** @test */
	public function
	TestVarSet():
	void {

		$Verse = static::NewVerseBasic();
		$Verse->VarSet([ 'YEEBUDDY' => 1 ]);

		$SQL = (string)$Verse;
		$this->AssertEquals(
			'SET `YEEBUDDY`=1',
			$SQL
		);

		return;
	}

	/** @test */
	public function
	TestQuery():
	void {

		$RXM = $this
		->GetMockBuilder('Nether\\Database\\Result')
		->DisableOriginalConstructor()
		->GetMock();

		$CXM = $this
		->GetMockBuilder('Nether\\Database\\Connection')
		->DisableOriginalConstructor()
		->GetMock();

		$CXM
		->Method('Query')
		->Will($this->ReturnValue($RXM));

		////////

		$Verse = static::NewVerseBasic();
		$Verse->Database = $CXM;
		$Verse->Select('Table');
		$Verse->Fields('Field1');

		$Result = $Verse->Query([ ':Value1' => 'Yee' ]);

		$this->AssertEquals($RXM, $Result);

		return;
	}

	/** @test */
	public function
	TestReset():
	void {

		$Verse = static::NewVerseBasic();

		$Verse
		->Select('yees ye')
		->Join('yaas ya ON ye.id=ya.id')
		->Fields([ 'omg', 'wtf', 'bbq' ])
		->Where('bbq=42')
		->Having('omg=123')
		->Sort('wtf', $Verse::SortDesc)
		->Group('wat')
		->Limit(69)
		->Offset(70)
		->PrimaryKey('id')
		->Charset('emoji')
		->Collate('ijome')
		->Engine('NopeItsElectric')
		->ForeignKey([ 'fore', 'ign', 'key' ])
		->Index([ 'in', 'dex', 'es' ])
		->Comment('ayy lmao');

		$this->AssertCount(1, $Verse->GetTables());
		$this->AssertCount(3, $Verse->GetFields());
		$this->AssertCount(1, $Verse->GetJoins());
		$this->AssertCount(1, $Verse->GetConditions());
		$this->AssertCount(1, $Verse->GetHavings());
		$this->AssertCount(1, $Verse->GetSorts());
		$this->AssertCount(1, $Verse->GetGroups());
		$this->AssertCount(3, $Verse->GetForeignKeys());
		$this->AssertCount(3, $Verse->GetIndexes());
		$this->AssertEquals(69, $Verse->GetLimit());
		$this->AssertEquals(70, $Verse->GetOffset());
		$this->AssertEquals('id', $Verse->GetPrimaryKey());
		$this->AssertEquals('emoji', $Verse->GetCharset());
		$this->AssertEquals('ijome', $Verse->GetCollate());
		$this->AssertEquals('NopeItsElectric', $Verse->GetEngine());
		$this->AssertEquals('ayy lmao', $Verse->GetComment());

		$Verse->Reset();

		$this->AssertCount(0, $Verse->GetTables());
		$this->AssertCount(0, $Verse->GetFields());
		$this->AssertCount(0, $Verse->GetJoins());
		$this->AssertCount(0, $Verse->GetConditions());
		$this->AssertCount(0, $Verse->GetHavings());
		$this->AssertCount(0, $Verse->GetSorts());
		$this->AssertCount(0, $Verse->GetGroups());
		$this->AssertCount(0, $Verse->GetForeignKeys());
		$this->AssertCount(0, $Verse->GetIndexes());
		$this->AssertEquals(0, $Verse->GetLimit());
		$this->AssertEquals(0, $Verse->GetOffset());
		$this->AssertEquals(NULL, $Verse->GetPrimaryKey());
		$this->AssertEquals('utf8mb4', $Verse->GetCharset());
		$this->AssertEquals('utf8mb4_general_ci', $Verse->GetCollate());
		$this->AssertEquals('InnoDB', $Verse->GetEngine());
		$this->AssertEquals('', $Verse->GetComment());

		return;
	}

	/** @test */
	public function
	TestDefaultConnection():
	void {

		$Verse = NULL;
		$Exceptional = FALSE;

		////////

		Library::Set(Library::ConfDefaultConnection, NULL);
		$Verse = new Verse;
		$Verse->Select('TestTable')->Fields('*');

		$this->AssertNull($Verse->GetDatabase());

		try {
			$Verse->Query();
		}

		catch(Throwable $Err) {
			$this->AssertInstanceOf(Error\NoConnectionAvailable::class, $Err);
			$Exceptional = TRUE;
		}

		$this->AssertTrue($Exceptional);

		////////

		$Exceptional = FALSE;

		try {
			Library::Set(Library::ConfDefaultConnection, 'Smeefault');
			$Verse = new Verse;
			$this->AssertNull($Verse->GetDatabase());
		}

		catch(Throwable $Err) {
			$this->AssertInstanceOf(Error\InvalidConnection::class, $Err);
			$Exceptional = TRUE;
		}

		$this->AssertTrue($Exceptional);

		////////

		Library::Set(Library::ConfDefaultConnection, 'Default');
		$Verse = new Verse;

		$this->AssertInstanceOf(Connection::class, $Verse->GetDatabase());

		return;
	}

}