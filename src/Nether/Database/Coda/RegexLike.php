<?php

namespace Nether\Database\Coda;
use \Nether;

class RegexLike
extends Nether\Database\Coda {

	public function
	Render() {
		return sprintf(
			'%s RLIKE "%s"',
			$this->Field,
			$this->Value
		);
	}

}
