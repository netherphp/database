<?php

namespace Nether\Database;

use PHPUnit;
use Nether;

use Throwable;
use Nether\Object\Datastore;

class ManagerTest
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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	/** @test */
	public function
	TestBasic():
	void {

		// without any config we should have no connections.

		$DBM = new Manager;
		$this->AssertFalse($DBM->Exists('Default'));
		$this->AssertInstanceOf(Datastore::class, Manager::GetConnections());
		$this->AssertEquals(0, Manager::GetConnections()->Count());

		// giving it a config we should be able to have a connection.

		$DBM = new Manager(static::GetConfigBasic());
		$this->AssertTrue($DBM->Exists('Default'));
		$this->AssertEquals(1, Manager::GetConnections()->Count());

		// connections have been preconfigured so we should still
		// be able to find one.

		$DBM = new Manager;
		$this->AssertTrue($DBM->Exists('Default'));

		// check that the connection smells ok.

		$DB = $DBM->Get('Default');
		$this->AssertTrue($DB instanceof Connection);
		$this->AssertFalse($DB->IsConnected());
		$this->AssertEquals('mysql', $DB->Type);
		$this->AssertEquals('hostname', $DB->Hostname);
		$this->AssertEquals('database', $DB->Database);
		$this->AssertEquals('username', $DB->Username);
		$this->AssertEquals('password', $DB->Password);

		$Verse = $DB->NewVerse();
		$this->AssertTrue($Verse instanceof Verse);

		return;
	}

	/** @test */
	public function
	TestUndefinedConnection():
	void {

		$DBM = new Manager;
		$Exceptional = FALSE;
		$Err = NULL;

		////////

		try {
			$DB = $DBM->Get('Captain Jean-Luc Picard of the USS Enterprise.');
		}

		catch(Throwable $Err) {
			$this->AssertInstanceOf(
				Error\InvalidConnection::class,
				$Err
			);

			$Exceptional = TRUE;
		}

		$this->AssertTrue($Exceptional);

		////////

		$Exceptional = FALSE;
		$Err = NULL;

		try {
			$DB = $DBM->NewVerse('And His Mom.');
		}

		catch(Throwable $Err) {
			$this->AssertInstanceOf(
				Error\InvalidConnection::class,
				$Err
			);

			$Exceptional = TRUE;
		}

		$this->AssertTrue($Exceptional);

		return;
	}

}