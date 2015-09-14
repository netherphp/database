<?php

require_once(sprintf(
	'%s/_PackageGetDatabaseMock.php',
	dirname(__FILE__)
));

class CodaEqualsTest
extends PHPUnit_Framework_TestCase {

	use GetDatabaseMock;

	////////////////////////////////
	////////////////////////////////

	/** @test */
	public function
	TestMethodIsAndNot() {
	/*//
	test that the equality methods are working as expected.
	//*/

		// by default codas build equality conditions.
		$Coda = new Nether\Database\Coda\Equals;
		$this->AssertTrue($Coda->GetEqual());

		// can be forced via SetEqual();
		$Coda->SetEqual(false);
		$this->AssertFalse($Coda->GetEqual());
		$Coda->SetEqual(true);
		$this->AssertTrue($Coda->GetEqual());

		// and can be forced into inequality via Not().
		$Coda->Not();
		$this->AssertFalse($Coda->GetEqual());

		// and can be forced into equality via Is().
		$Coda->Is();
		$this->AssertTrue($Coda->GetEqual());

		return;
	}

	/** @test */
	public function
	TestEquality() {
	/*//
	test that this coda is able to generate an equality condition that will
	work with this database.
	//*/

		$Coda = (new Nether\Database\Coda\Equals)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID')
		->SetValue(':ObjectID');

		$this->AssertEquals(
			'ObjectID=:ObjectID',
			$Coda->Render()
		);

		return;
	}

	/** @test */
	public function
	TestInequality() {
	/*//
	test that this coda is able to generate an inequality condition that will
	work with this database.
	//*/

		$Coda = (new Nether\Database\Coda\Equals)
		->SetDatabase($this->GetDatabaseMock())
		->Not()
		->SetField('ObjectID')
		->SetValue(':ObjectID');

		$this->AssertEquals(
			'ObjectID!=:ObjectID',
			$Coda->Render()
		);

		return;
	}

	/** @test */
	public function
	TestDislikesArrays() {
	/*//
	test that this coda refuses to deal with arrays.
	//*/

		$Coda = (new Nether\Database\Coda\Equals)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID')
		->SetValue([ 'some','dumb','array' ]);

		$this->SetExpectedException('Exception');
		$Coda->Render();

		return;
	}

	/** @test */
	public function
	TestDislikesObjects() {
	/*//
	test that this coda refuses to deal with objects.
	//*/

		$Coda = (new Nether\Database\Coda\Equals)
		->SetDatabase($this->GetDatabaseMock())
		->SetField('ObjectID')
		->SetValue(new stdClass);

		$this->SetExpectedException('Exception');
		$Coda->Render();

		return;
	}

}
