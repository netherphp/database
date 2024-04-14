<?php

namespace Nether\Database\Connection;

use Nether\Database;

class MySQL
extends Database\Connection {

	public function
	__Construct(
		string $Hostname,
		string $Database,
		string $Username,
		string $Password,
		string $Charset='utf8mb4',
		?string $Name=NULL,
		bool $Auto=FALSE
	) {

		parent::__Construct(
			Name: $Name,
			Type: 'mysql',
			Database: $Database,
			Hostname: $Hostname,
			Username: $Username,
			Password: $Password,
			Charset: $Charset,
			Auto: $Auto
		);

		return;
	}

};
