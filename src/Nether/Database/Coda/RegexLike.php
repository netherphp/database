<?php

namespace Nether\Database\Coda;
use \Nether;

class RegexLike
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
	@argv bool
	@return self
	//*/

		$this->MountEnd = $State;
		return $this;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Render_MySQL() {
	/*//
	@return string
	//*/

		$this->RequireDatabase();

		// if the value is a bound parameter we don't need to literally it.
		if(is_string($this->Value) && strpos($this->Value,':') === 0)
		return sprintf(
			'%s %s %s',
			$this->Field,
			(($this->Equal)?('RLIKE'):('NOT RLIKE')),
			$this->Value
		);

		// but if its another value we do.
		return $this->RenderLiterally_MySQL();
	}

	protected function
	RenderLiterally_MySQL() {
	/*//
	@return string
	//*/

		$Value = sprintf(
			'%s%s%s',
			(($this->MountStart)?('^'):('')),
			((is_array($this->Value))?
				('('.implode('|',$this->Value).')'):
				($this->Value)
			),
			(($this->MountEnd)?('$'):(''))
		);

		return sprintf(
			'%s %s %s',
			$this->Field,
			(($this->Equal)?('RLIKE'):('NOT RLIKE')),
			$this->Database->Escape($Value)
		);
	}

}
