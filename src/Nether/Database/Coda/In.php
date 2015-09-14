<?php

namespace Nether\Database\Coda;
use \Nether;

class In
extends Nether\Database\Coda\Equals {
/*//
before the query code was refactored to include the ability to flatten arrays
out into the main dataset, the need for this coda to manually interpolate the
actual data to generate an IN query was much more impressive. you can still
have this class apply them literally if you want, though.
//*/

	public function
	Render_Generic() {
	/*//
	@return string
	//*/

		$this->RequireDatabase();

		if($this->IsValueBinding()) $Value = $this->Value;
		else $Value = $this->GetSafeValue();

		if(is_array($Value) || is_object($Value))
		$Value = implode(',',(array)$Value);

		return sprintf(
			'%s %s(%s)',
			$this->Field,
			(($this->Equal)?('IN'):('NOT IN')),
			$Value
		);
	}

}
