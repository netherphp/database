<?php

namespace Nether\Database;

use PHPUnit;
use Nether;

use Nether\Object\Datastore;

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

		$Verse
		->Select('TestTable')
		->Fields([ 'Field1', 'Field2' ])
		->Where('Field1=:Value1');

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
		->Join('OtherTable O')
		->Fields([ 'F1', 'F2' ])
		->Where('F1=:V1');

		$this->AssertEquals(
			'SELECT `F1`,`F2` FROM TestTable T JOIN OtherTable O WHERE (F1=:V1)'
		);

		return;
	}

}