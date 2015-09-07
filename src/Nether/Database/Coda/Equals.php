<?php

namespace Nether\Database\Coda;
use \Nether;

class Equals
extends Nether\Database\Coda {

	public function
	Render() {

		if(is_array($this->Value)) {
			foreach($this->Value as &$Value)
			$Value = $this->Database->Escape($Value);

			return sprintf(
				'%s IN("%s")',
				$this->Field,
				implode('","',$this->Value)
			);
		}

		else {
			return sprintf(
				'%s="%s"',
				$this->Field,
				$this->Database->Escape($this->Value)
			);
		}
	}

}
