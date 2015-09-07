<?php

namespace Nether\Database\Coda;
use \Nether;

class Equals
extends Nether\Database\Coda {

	public function
	Render() {
		return sprintf(
			'%s="%s"',
			$this->Field,
			$this->Value
		);
	}

}
