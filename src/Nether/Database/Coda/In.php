<?php

namespace Nether\Database\Coda;
use \Nether;

class In
extends Nether\Database\Coda\Equals {

	public function
	Render() {

		$this->RequireDatabase();

		return sprintf(
			'%s %s(%s)',
			$this->Field,
			(($this->Equal)?('IN'):('NOT IN')),
			$this->Value
		);
	}


	/*
	protected function
	Render_Flat() {
		return sprintf(
			'%s%s%s',
			$this->Field,
			(($this->Equal)?('='):('!=')),
			((strpos($this->Value,':')===0)?
				($this->Value): // bound parametre syntax.
				($this->Database->Escape($this->Value))) // flat value.
		);	
	}

	protected function
	Render_List() {
		$Value = $this->Value;
		$Quote = null;
		
		foreach($Value as &$Val) {
			$Val = $this->Database->Escape((string)$Val);
			$Quote = substr($Val,0,1);
			$Val = substr($Val,1,-1);
		}

		return sprintf(
			"%s %s({$Quote}%s{$Quote})",
			$this->Field,
			(($this->Equal)?('IN'):('NOT IN')),
			implode("{$Quote},{$Quote}",$Value)
		);	
	}
	*/

}
