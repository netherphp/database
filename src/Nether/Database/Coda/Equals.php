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
			$this->Database->Escape($this->Value)
		);	
	}

	protected function
	Render_List() {
		$Value = $this->Value;
		$Quote = null;
		
		foreach($Value as &$Val) {
			$Val = $this->Database->GetDriver()->quote((string)$Val,\PDO::PARAM_STR);
			//$Quote = substr($Val,0);
			//$Val = substr($Val,1,-1);
		}

		return sprintf(
			'%s %s(%s)',
			$this->Field,
			(($this->Equal)?('IN'):('NOT IN')),
			implode("{$Quote},{$Quote}",$this->Value)
		);	
	}

}
