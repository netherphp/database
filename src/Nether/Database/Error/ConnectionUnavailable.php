<?php

namespace Nether\Database\Error;

use Exception;

class ConnectionUnavailable
extends Exception {

	public function
	__Construct() {
		parent::__Construct('No database connection.');
		return;
	}

}
