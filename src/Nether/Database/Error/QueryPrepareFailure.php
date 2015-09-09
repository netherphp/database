<?php

namespace Nether\Database\Error;
use \Nether;

use \Exception;

class QueryPrepareFailure
extends Exception {

	public function
	__construct() {
		parent::__construct("The query failed to be prepared by PDO.");
		return;
	}

}
