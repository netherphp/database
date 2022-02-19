<?php

namespace Nether\Database\Error;

use Exception;

class ConfigFileNotFound
extends Exception {

	public function
	__Construct(mixed $Filename) {
		parent::__Construct("Config file not found: {$Filename}");
		return;
	}

}
