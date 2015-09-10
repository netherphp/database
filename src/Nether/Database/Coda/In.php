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

	protected
	$Literally = false;
	/*//
	if we should literally interpolate the values if we gave it real data or
	allow the main database object to handle it for bound arugments. if you
	have the choice you should always allow the bound argument system to do
	its job instead of doing it literally.
	//*/

	public function
	GetLiterally() {
	/*//
	@return bool
	set if we should literally generate the sql or not.
	//*/

		return $this->Literally;
	}

	public function
	SetLiterally($State) {
	/*//
	@argv bool
	return self
	set if we should literally generate the sql or not.
	//*/

		$this->Literally = $State;
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

		if(!$this->Literally && !is_array($this->Value))
		return sprintf(
			'%s %s(%s)',
			$this->Field,
			(($this->Equal)?('IN'):('NOT IN')),
			$this->Value
		);

		return $this->RenderLiterally_MySQL();
	}

	protected function
	RenderLiterally_MySQL() {
	/*//
	@return string
	//*/

		$Value = $this->Value;
		$Quote = null;

		// manually escape the input data, take a peek at what character was
		// used to escape it, and then trim the characters off each end leaving
		// just the escaped innards.
		foreach($Value as &$Val) {
			$Val = $this->Database->Escape((string)$Val);
			$Quote = substr($Val,0,1);
			$Val = substr($Val,1,-1);
		}

		// then construct the final clause.
		return sprintf(
			"%s %s({$Quote}%s{$Quote})",
			$this->Field,
			(($this->Equal)?('IN'):('NOT IN')),
			implode("{$Quote},{$Quote}",$Value)
		);
	}

}
