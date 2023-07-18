<?php

namespace Nether\Database\Error;

use Exception;

class TableJoinInvalidTarget
extends Exception {

	public function
	__Construct(?string $What=NULL) {

		$Message = 'TableJoin property should extend Database\\Prototype';

		if($What !== NULL)
		$Message .= sprintf(' (%s)', $What);

		////////

		parent::__Construct($Message);
		return;
	}

}

