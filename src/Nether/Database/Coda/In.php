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

		$QueryValue = $this->GetDataBindings();
		if(!$QueryValue) $QueryValue = $this->GetSafeValue();

		return sprintf(
			'%s %s(%s)',
			$this->Field,
			(($this->Equal)?
				('IN'):
				('NOT IN')),
			((is_array($QueryValue) || is_object($QueryValue))?
				(implode(',',(array)$QueryValue)):
				($QueryValue))
		);
	}

}
