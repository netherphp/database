<?php

namespace Nether\Database\Error;
use \Nether;

use \Exception;

class InvalidConfig
extends Exception {

	public function
	__construct($Alias) {
		parent::__construct("The configuration for {$Alias} is invalid.");
		return;
	}

}
