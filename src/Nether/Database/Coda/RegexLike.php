<?php

namespace Nether\Database\Coda;
use \Nether;

class RegexLike
extends Nether\Database\Coda\Like {
/*//
construct a fragment for like via regular expression.
//*/

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
	Render_Generic() {
	/*//
	@return string
	//*/

		$this->RequireDatabase();

		$QueryValue = $this->GetDataBindings();
		if(!$QueryValue) $QueryValue = $this->GetSafeValue();

		return sprintf(
			'%s %s %s',
			$this->Field,
			(($this->Equal)?('RLIKE'):('NOT RLIKE')),
			$this->Value
		);
	}

}
