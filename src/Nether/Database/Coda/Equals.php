<?php

namespace Nether\Database\Coda;
use \Nether;

use \Exception;

class Equals
extends Nether\Database\Coda {
/*//
probably the most simple operation we can create a coda for. this class
mearly lets you assign if the value must be equal or not equal to the
specified field. the thing is, several other codas are going to be extending
this one.
//*/

	protected
	$Equal = true;
	/*//
	defines if this is a positive or negative comparison.
	//*/

	public function
	SetEqual($State) {
	/*//
	@argv bool
	@eturn self
	set if this ia positive or negative comparison.
	//*/

		$this->Equal = $State;
		return $this;
	}

	public function
	Is() {
	/*//
	@return self
	force this comparison to be positive.
	//*/

		return $this->SetEqual(true);
	}

	public function
	Not() {
	/*//
	force this comparison to be negative.
	//*/

		return $this->SetEqual(false);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Render_MySQL() {
	/*//
	provide the rendering for an equality condition.
	//*/

		$this->RequireDatabase();

		if(is_object($this->Value) || is_array($this->Value))
		throw new Exception('value must be a literal value.');

		return sprintf(
			'%s%s%s',
			$this->Field,
			(($this->Equal)?('='):('!=')),
			((strpos($this->Value,':')===0)?
				($this->Value):
				($this->Database->Escape($this->Value))
			)
		);
	}

}
