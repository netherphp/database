<?php

namespace Nether\Database\Coda;
use \Nether;

class Like
extends Nether\Database\Coda\Equals {
/*//
construct a fragment for like via regular expression.
//*/

	protected
	$MountStart = false;
	/*//
	@type bool
	if we should add the "match start of line" mode to the regex.
	//*/

	public function
	GetMountStart() {
	/*//
	@return bool
	//*/

		return $this->MountStart;
	}

	public function
	SetMountStart($State) {
	/*//
	@deprecated
	@alias FromStart
	//*/

		return $this->FromStart($State);
	}

	public function
	FromStart($State=true) {
	/*//
	@argv bool
	@return self
	//*/

		$this->MountStart = $State;
		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$MountEnd = false;
	/*//
	@type bool
	if we should add the "match end of line" mode to the regex.
	//*/

	public function
	GetMountEnd() {
	/*//
	@return bool
	//*/

		return $this->MountEnd;
	}

	public function
	SetMountEnd($State) {
	/*//
	@deprecated
	@alias FromEnd
	//*/

		return $this->FromEnd($State);
	}

	public function
	FromEnd($State=true) {
	/*//
	@argv bool
	@return self
	//*/

		$this->MountEnd= $State;
		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	GetData() {

		$Data = $this->Data;
		if(!is_array($Data)) $Data = [$Data];

		foreach($Data as $Key => $Val) {
			if(!$this->MountStart && !$this->MountEnd)
			$Data[$Key] = "%{$Val}%";

			elseif($this->MountStart && !$this->MountEnd)
			$Data[$Key] = "{$Val}%";

			elseif(!$this->MountStart && $this->MountEnd)
			$Data[$Key] = "%{$Val}";
		}

		return $Data;
	}


	////////////////////////////////
	////////////////////////////////

	public function
	Render_Generic() {
	/*//
	@return string
	//*/

		$this->RequireDatabase();

		$QueryValue = $this->GetDataBindings();
		if(!$QueryValue) $QueryValue = $this->GetSafeValue();

		$Equality = (($this->Equal)?
			('LIKE'):
			('NOT LIKE'));

		$Join = (($this->Equal)?
			('OR'):
			('AND'));

		return sprintf(
			'(%s %s %s)',
			$this->Field,
			$Equality,
			((is_array($QueryValue) || is_object($QueryValue))?
				(implode(" {$Join} {$this->Field} {$Equality} ",(array)$QueryValue)):
				($QueryValue))
		);
	}

}
