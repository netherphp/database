<?php

namespace Nether\Database\Coda;
use \Nether;

class Like
extends Nether\Database\Coda\RegexLike {
/*//
construct a fragment for like via regular expression.
//*/

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
	Render_MySQL() {
	/*//
	@return string
	//*/

		$this->RequireDatabase();

		if(is_array($this->Data))
		return $this->RenderList_MySQL();

		return sprintf(
			'%s %s %s',
			$this->Field,
			(($this->Equal)?('LIKE'):('NOT LIKE')),
			$this->Value
		);
	}

	public function
	RenderList_MySQL() {
	/*//
	@return string
	//*/

		$List = [];
		$NewKey = str_replace(':',':__',$this->Value);

		foreach(array_values($this->GetData()) as $Key => $Val) {
			$List[] = "{$this->Field} LIKE {$NewKey}__{$Key}";
		}

		return sprintf(
			'(%s)',
			implode(" OR ",$List)
		);
	}

}
