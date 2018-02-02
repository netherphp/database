<?php

namespace
Nether\Database;

use
\Nether as Nether;

////////////////
////////////////

class
Connection {
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

	public
	$Charmap = 'utf8';
	/*//
	@type string
	the character encoding for the connection.
	//*/

	////////////////////////////////
	////////////////////////////////

	public function
	__construct($Opt) {
	/*//
	@argv object|array
	//*/

		$Opt = new Nether\Object\Mapped($Opt,[
			'Type'     => NULL,
			'Hostname' => NULL,
			'Username' => NULL,
			'Password' => NULL,
			'Database' => NULL,
			'Charset'  => 'utf8'
		],[ 'DefaultKeysOnly'=>TRUE ]);

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

		switch(strtolower($this->Type)) {
			case 'mysql': return sprintf(
				'%s:host=%s;dbname=%s;charset=%s',
				$this->Type,
				$this->Hostname,
				$this->Database,
				$this->Charset
			);
			case 'sqlite': return sprintf(
				'%s:%s',
				$this->Type,
				$this->Database
			);
			default: return sprintf(
				'%s:host=%s;dbname=%s, %s, %s;charset=%s',
				$this->Type,
				$this->Hostname,
				$this->Database,
				$this->Username,
				$this->Password,
				$this->Charset
			);
		}
	}


}
