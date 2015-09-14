<?php

require_once(sprintf(
	'%s/_PackageGetDatabaseMock.php',
	dirname(__FILE__)
));

class CodaInTest
extends PHPUnit_Framework_TestCase {

	use GetDatabaseMock;

	////////////////////////////////
	////////////////////////////////

	/** @test */
	public function
	TestEqualityWithBondage() {
	/*//
	test that this coda is able to generate an equality condition that will
	work with this database.
	//*/

		$Coda = (new Nether\Database\Coda\In)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID')
		->SetValue(':ObjectID');

		$this->AssertEquals(
			'ObjectID IN(:ObjectID)',
			$Coda->Render()
		);

		return;
	}

	/** @test */
	public function
	TestInequalityWithBondage() {
	/*//
	test that this coda is able to generate an inequality condition that will
	work with this database.
	//*/

		$Coda = (new Nether\Database\Coda\In)
		->SetDatabase($this->GetDatabaseMock())
		->Not()
		->SetField('ObjectID')
		->SetValue(':ObjectID');

		$this->AssertEquals(
			'ObjectID NOT IN(:ObjectID)',
			$Coda->Render()
		);

		return;
	}

	/** @test */
	public function
	TestEqualityWithLiterally() {
	/*//
	test that this coda is able to generate an equality condition when
	throwing the raw data into the string.
	//*/

		$Coda = (new Nether\Database\Coda\In)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID');

		// with single values.
		$Coda->SetValue(42);
		$this->AssertEquals(
			'ObjectID IN(\'42\')',
			$Coda->Render()
		);

		// with lists of values.
		$Coda->SetValue([42,69,1080]);
		$this->AssertEquals(
			'ObjectID IN(\'42\',\'69\',\'1080\')',
			$Coda->Render()
		);
	}

}
