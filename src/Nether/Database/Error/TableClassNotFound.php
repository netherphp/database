<?php

namespace Nether\Database\Error;

use Exception;

class TableClassNotFound
extends Exception {

	public function
	__Construct(string $Class) {
		parent::__Construct("no TableClass found on {$Class}");
		return;
	}

}
