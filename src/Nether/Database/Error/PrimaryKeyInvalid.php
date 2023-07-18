<?php

namespace Nether\Database\Error;

use Exception;

class PrimaryKeyInvalid
extends Exception {

	public function
	__Construct() {
		parent::__Construct('No PrimaryKey was defined for this Query.');
		return;
	}

}
