<?php

namespace Nether\Database\Error;

use Exception;

class InvalidPrimaryKeyInput
extends Exception {

	public function
	__Construct() {
		parent::__construct("no primary key was defined for this query.");
		return;
	}

}
