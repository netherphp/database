<?php

namespace Nether\Database\Error;
use \Nether;

use \Exception;

class InvalidConfig
extends Exception {

	public function
	__Construct($Alias) {
		parent::__Construct("The configuration for {$Alias} is invalid.");
		return;
	}

}
