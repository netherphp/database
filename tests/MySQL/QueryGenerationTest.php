<?php

require_once(sprintf(
	'%s/_PackageGetDatabaseMock.php',
	dirname(__FILE__)
));

class QueryGenerationTest
extends PHPUnit_Framework_TestCase {

	use GetDatabaseMock;

	/** @test */
	public function
	TestBasicQueryGeneration() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();
		$Argv = (object)[ ':ObjectIDs' => [42,69,1080] ];
		$Dataset = [];
		$Stage1Query = "SELECT Field1, Field2, Field3 FROM TableName WHERE ObjectID IN(:ObjectIDs)";
		$Stage2Query = "SELECT Field1, Field2, Field3 FROM TableName WHERE ObjectID IN(:__ObjectIDs__0,:__ObjectIDs__1,:__ObjectIDs__2)";

		$SQL = $Verse
		->Select('TableName')
		->Values(['Field1','Field2','Field3'])
		->Where('ObjectID IN(:ObjectIDs)')
		->GetSQL();

		////////
		////////

		// check that the verse looks good.
		$this->AssertEquals($Stage1Query,$SQL);

		// check that the query builder was able to map our data.
		$DB->Query_BuildDataset($SQL,$Argv,$Dataset);
		$this->AssertEquals($Stage1Query,$SQL);
		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) == 1);
		$this->AssertTrue(count($Dataset[':ObjectIDs']) == 3);

		// check that the query builder was able to expand our data and sql.
		$DB->Query_ExpandDataset($SQL,$Argv,$Dataset);
		$this->AssertEquals($Stage2Query,$SQL);
		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) == 3);
		$this->AssertTrue(array_key_exists(':__ObjectIDs__0',$Dataset));
		$this->AssertTrue(array_key_exists(':__ObjectIDs__1',$Dataset));
		$this->AssertTrue(array_key_exists(':__ObjectIDs__2',$Dataset));

		return;
	}

	/** @test */
	public function
	TestCodaQueryGeneration() {

		$DB = $this->GetDatabaseMock();
		$Verse = $DB->NewVerse();
		$Argv = (object)[ ':ObjectIDs' => [42,69,1080] ];
		$Dataset = [];
		$Stage1Query = "SELECT Field1, Field2, Field3 FROM TableName WHERE ObjectID IN(:ObjectIDs)";
		$Stage2Query = "SELECT Field1, Field2, Field3 FROM TableName WHERE ObjectID IN(:__ObjectIDs__0,:__ObjectIDs__1,:__ObjectIDs__2)";

		////////
		////////

		$SQL = $Verse
		->Select('TableName')
		->Values(['Field1','Field2','Field3'])
		->Where(
			$Verse
			->NewCoda('In')
			->SetField('ObjectID')
			->SetValue(':ObjectIDs')
		)
		->GetSQL();

		// check that our verse looks good when it didn't know about the
		// data that it needed to work with. you dont typically want to
		// do it that way fyi.
		$this->AssertEquals($Stage1Query,$SQL);

		////////
		////////

		$SQL = $Verse
		->Select('TableName')
		->Values(['Field1','Field2','Field3'])
		->Where(
			$Verse
			->NewCoda('In')
			->SetField('ObjectID')
			->SetValue(':ObjectIDs')
			->SetData($Argv->{':ObjectIDs'})
		)
		->GetSQL();

		// check that our verse this time rendered something more like what
		// the query expansion would have done because it knew about the data
		// that it needed. this is the preferred way to do it fyi.
		$this->AssertEquals($Stage2Query,$SQL);

		// check that the query builder was able to map our data.
		$DB->Query_BuildDataset($SQL,$Argv,$Dataset);
		$this->AssertEquals($Stage2Query,$SQL);
		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) == 1);
		$this->AssertTrue(count($Dataset[':ObjectIDs']) == 3);

		// check that the query builder was able to expand our data and sql.
		$DB->Query_ExpandDataset($SQL,$Argv,$Dataset);
		$this->AssertEquals($Stage2Query,$SQL);
		$this->AssertTrue(is_array($Dataset));
		$this->AssertTrue(count($Dataset) == 3);
		$this->AssertTrue(array_key_exists(':__ObjectIDs__0',$Dataset));
		$this->AssertTrue(array_key_exists(':__ObjectIDs__1',$Dataset));
		$this->AssertTrue(array_key_exists(':__ObjectIDs__2',$Dataset));

		return;
	}

}