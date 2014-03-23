<?php

namespace Nether\Database;
use \Nether;

////////////////
////////////////

class Connection {

	public $Type;
	/*//
	@type string
	the database server type we are connecting to. it should be a type
	PDO understands.
	//*/

	public $Hostname;
	/*//
	@type string
	the hostname of the server we are connecting to.
	//*/

	public $Database;
	/*//
	@type string
	the database name for the database connection.
	//*/

	public $Username;
	/*//
	@type string
	the username required for the database connection.
	//*/

	public $Password;
	/*//
	@type string
	the password required for the database connection.
	//*/

	public function __construct($opt) {
		$opt = new Nether\Object($opt,[
			'Type'     => null,
			'Hostname' => null,
			'Username' => null,
			'Password' => null,
			'Database' => null
		]);

		foreach($opt as $prop => $val)
		$this->{$prop} = $val;

		return;
	}

	public function __toString() {
		return $this->GetDSN();
	}

	////////////////
	////////////////

	public function GetDSN() {
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
