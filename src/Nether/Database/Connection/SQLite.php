<?php

namespace Nether\Database\Connection;

use Nether\Database;

class SQLite
extends Database\Connection {

	public function
	__Construct(
		string $Database,
		?string $Name=NULL
	) {

		parent::__Construct(
			Name: $Name,
			Type: 'sqlite',
			Database: $Database,
			Hostname: '',
			Username: '',
			Password: ''
		);

		return;
	}

};
