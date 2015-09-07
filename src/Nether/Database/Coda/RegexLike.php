<?php

namespace Nether\Database\Coda;
use \Nether;

class RegexLike
extends Nether\Database\Coda {

	////////
	////////

	protected $MountStart = false;

	public function
	GetMountStart() { return $this->MountStart; }

	public function
	SetMountStart($State) { $this->MountStart = $State; return $this; }

	////////
	////////

	protected $MountEnd = false;

	public function
	GetMountEnd() { return $this->MountEnd; }

	public function
	SetMountEnd($State) { $this->MountEnd = $State; return $this; }

	////////
	////////

	public function
	Render() {

		$Value = sprintf(
			'%s%s%s',
			(($this->MountStart)?('^'):('')),
			((is_array($this->Value))?
				('('.implode('|',$this->Value).')'):
				($this->Value)),
			(($this->MountEnd)?('$'):(''))
		);

		return sprintf(
			'%s RLIKE %s',
			$this->Field,
			$this->Database->Escape($Value)
		);
	}

}
