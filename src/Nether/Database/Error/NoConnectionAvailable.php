<?php

namespace Nether\Database\Error;

use Exception;

class NoConnectionAvailable
extends Exception {

	public function
	__Construct() {

		parent::__Construct("No database connection.");

		return;
	}

}
