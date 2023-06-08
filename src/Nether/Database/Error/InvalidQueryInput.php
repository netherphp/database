<?php

namespace Nether\Database\Error;
use \Nether;

use \Exception;

class InvalidQueryInput
extends Exception {

	public function
	__construct() {
		parent::__construct("Query Input must be specified as an object or an array.");
		return;
	}

}
