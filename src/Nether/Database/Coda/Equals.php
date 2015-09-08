<?php

namespace Nether\Database\Coda;
use \Nether;

class Equals
extends Nether\Database\Coda {

	protected $Equal = true;

	////////////////
	////////////////

	public function
	SetEqual($State) {
		$this->Equal = $State;
		return $this;
	}

	public function
	Is() {
		return $this->SetEqual(true);
	}

	public function
	Not() {
		return $this->SetEqual(false);
	}

	////////////////
	////////////////

	public function
	Render() {

		$this->RequireDatabase();

		if(!is_array($this->Value))
		return $this->Render_Flat();

		else
		return $this->Render_List();
	}

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
	/*//
	if we passed a list as values then we will construct an IN code for this
	query, doing the best we can to sanitise it since we cannot just reuse the
	pdo bound parameter system for it.
	//*/

		$Value = $this->Value;
		$Quote = null;
		
		foreach($Value as &$Val) {
			$Val = $this->Database->Escape((string)$Val);
			$Quote = substr($Val,0,1); // find what the system quoted it with.
			$Val = substr($Val,1,-1); // then cut the edge quotes off.
		}

		return sprintf(
			"%s %s({$Quote}%s{$Quote})",
			$this->Field,
			(($this->Equal)?('IN'):('NOT IN')),
			implode("{$Quote},{$Quote}",$Value)
		);	
	}

}
