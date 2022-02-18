<?php

trait GetDatabaseMock {

	protected function
	GetDatabaseMock() {

		$DB = $this
		->GetMockBuilder('Nether\Database')
		->SetMethods([
			'GetDriverName',
			'Escape'
		 ])
		->DisableOriginalConstructor()
		->GetMock();

		// codas make use of the database connection's driver name to decide
		// what version of the coda it should use in case some servers have
		// differences in syntax. this test directory is only testing the
		// output for mysql.

		$DB
		->Method('GetDriverName')
		->Will($this->ReturnValue('mysql'));

		// codas make use of database connections for their ability to escape
		// data in a way that is safe for that specific server. since we are
		// testing the coda's ability to write code rather than the server's
		// ability to clean we will mock the Escape method to return a
		// simulation of safe data, so that our tests *look* less bad.
		// however one must remember that tests using this mock are not not
		// claiming their data is 100% safe, only that they compile as
		// expected when given a specific means of cleaning.

		$DB
		->Method('Escape')
		->Will($this->ReturnCallback(function($Input){
			return sprintf("'%s'",addslashes($Input));
		}));

		return $DB;
	}

}
