<?php

namespace Nether\Database\Error;
use \Nether;

use \Exception;

class QueryPrepareFailure
extends Exception {

	public function
	__construct(?string $SQL=NULL) {
		parent::__construct("The query failed to be prepared by PDO ({$SQL}).");
		return;
	}

}
