<?php

namespace Nether\Database\Error;
use \Nether;

use \Exception;

class InvalidConnection
extends Exception {

	public function
	__Construct($Alias) {

		parent::__Construct("No configured connection for {$Alias}");

		return;
	}

}
