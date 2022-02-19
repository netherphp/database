<?php

namespace Nether\Database;

use Nether;

class Connection {
/*//
@date 2022-02-18
this class defines the configuration values required to connect to the various
database servers you may setup in your site config.
//*/

	public string
	$Type = '';
	/*//
	the database server type we are connecting to. it should be a type PDO
	understands as one of its available drivers.
	//*/

	public string
	$Hostname = '';
	/*//
	the hostname of the server we are connecting to.
	//*/

	public string
	$Database = '';
	/*//
	the database name for the database connection.
	//*/

	public string
	$Username = '';
	/*//
	the username required for the database connection.
	//*/

	public string
	$Password = '';
	/*//
	the password required for the database connection.
	//*/

	public string
	$Charset = 'utf8';
	/*//
	the character encoding for the connection.
	//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(array|object $Raw) {
	/*//
	@date 2022-02-18
	//*/

		$Prop = NULL;
		$Val = NULL;

		$Opt = new Nether\Object\Mapped(
			$Raw,
			[
				'Type'     => '',
				'Hostname' => '',
				'Username' => '',
				'Password' => '',
				'Database' => '',
				'Charset'  => 'utf8'
			],
			[
				'DefaultKeysOnly' => TRUE
			]
		);

		foreach($Opt as $Prop => $Val)
		$this->{$Prop} = $Val;

		return;
	}

	public function
	__ToString():
	string {
	/*//
	@date 2022-02-18
	when used in a string context dump out the pdo connection string.
	//*/

		return $this->GetDSN();
	}

	public function
	GetDSN():
	string {
	/*//
	@date 2022-02-18
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
