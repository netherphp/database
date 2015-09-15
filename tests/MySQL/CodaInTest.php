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
	TestSimpleEqualityWithBondage() {
	/*//
	test that this coda can generate an equal condition when given a value
	that is to be used as a bound parameter. this is to simulate creating a
	simple condition without needing to care what the data is about. this
	demonstrates the preferred way to use this coda.
	//*/

		$Coda = (new Nether\Database\Coda\In)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID');

		$Coda->SetValue(':ObjectID');
		$this->AssertEquals(
			'ObjectID IN(:ObjectID)',
			$Coda->Render()
		);

		return;
	}

	/** @test */
	public function
	TestSimpleInequalityWithBondage() {
	/*//
	tests that this coda can generate an inequality condition given a value
	that is to be used as a bound parametre.
	//*/

		$Coda = (new Nether\Database\Coda\In)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID')
		->Not();

		$Coda->SetValue(':ObjectID');
		$this->AssertEquals(
			'ObjectID NOT IN(:ObjectID)',
			$Coda->Render()
		);

		return;
	}


	/** @test */
	public function
	TestComplexEqualityWithBondage() {
	/*//
	test that this coda can generate an equal condition when given an array of
	values to use as bound parameters. technically, the user would never do
	this beecause it is kind of stupid. they should instead prefer to do what
	the next test, Complex Equality With Bondage Using Data.
	//*/

		$Coda = (new Nether\Database\Coda\In)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID')
		->SetValue([':__ObjectID__0',':__ObjectID__1',':__ObjectID__2']);

		$this->AssertEquals(
			'ObjectID IN(:__ObjectID__0,:__ObjectID__1,:__ObjectID__2)',
			$Coda->Render()
		);

		return;
	}

	/** @test */
	public function
	TestComplexEqualityWithBondageUsingData() {
	/*//
	test that this coda can generate an equal condition when given a value
	that is to be used as the bound parameter, but with a multivalue dataset
	having been given as well. this demonstrates the preferred way to use
	this coda.
	//*/

		$Coda = (new Nether\Database\Coda\In)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID')
		->SetValue(':ObjectID')
		->SetData([42,69,1080]);

		$this->AssertEquals(
			'ObjectID IN(:__ObjectID__0,:__ObjectID__1,:__ObjectID__2)',
			$Coda->Render()
		);

		return;
	}

	/** @test */
	public function
	TestEqualityWithLiterallyValues() {
	/*//
	test that this coda can generate an equal condition when given literal
	values that are not bound parameters.
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
