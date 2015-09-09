<?php

namespace Nether\Database;
use \Nether;

////////////////
////////////////

class Connection {
/*//
this class defines the configuration values required to connect to the various
database servers you may setup in your site config.
//*/

	public
	$Type = null;
	/*//
	@type string
	the database server type we are connecting to. it should be a type PDO
	understands as one of its available drivers.
	//*/

	public
	$Hostname = null;
	/*//
	@type string
	the hostname of the server we are connecting to.
	//*/

	public
	$Database = null;
	/*//
	@type string
	the database name for the database connection.
	//*/

	public
	$Username = null;
	/*//
	@type string
	the username required for the database connection.
	//*/

	public
	$Password = null;
	/*//
	@type string
	the password required for the database connection.
	//*/

	////////////////////////////////
	////////////////////////////////

	public function
	__construct($Opt) {
	/*//
	@argv object|array
	//*/

		$Opt = new Nether\Object($Opt,[
			'Type'     => null,
			'Hostname' => null,
			'Username' => null,
			'Password' => null,
			'Database' => null
		],['DefaultKeysOnly'=>true]);

		foreach($Opt as $Prop => $Val)
		$this->{$Prop} = $Val;

		return;
	}

	public function
	__toString() {
	/*//
	when used in a string context dump out the pdo connection string.
	//*/

		return $this->GetDSN();
	}

	////////////////////////////////
	////////////////////////////////

	public function
	GetDSN() {
	/*//
	@type string
	get the connection dsn string for this database connection.
	//*/

		switch($this->Type) {
			case 'mysql': return sprintf(
				'%s:host=%s;dbname=%s',
				$this->Type,
				$this->Hostname,
				$this->Database
			);
			case 'sqlite': return sprintf(
				'%s:%s',
				$this->Type,
				$this->Database
			);
			default: return sprintf(
				'%s:host=%s;dbname=%s, %s, %s',
				$this->Type,
				$this->Hostname,
				$this->Database,
				$this->Username,
				$this->Password
			);
		}
	}


}
