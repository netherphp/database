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
	/*//
	@override Nether\Database\Coda::GetData
	transform the given data into a regex the query can use.
	//*/

		return sprintf(
			'%s%s%s',
			(($this->MountStart)?('^'):('')),
			((is_array($this->Data))?
				('('.implode('|',$this->Data).')'):
				($this->Data)
			),
			(($this->MountEnd)?('$'):(''))
		);
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Render_MySQL() {
	/*//
	@return string
	//*/

		$this->RequireDatabase();

		return sprintf(
			'%s %s %s',
			$this->Field,
			(($this->Equal)?('RLIKE'):('NOT RLIKE')),
			$this->Value
		);
	}

}
