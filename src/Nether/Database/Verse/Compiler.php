<?php

namespace Nether\Database\Verse;

use \Nether;
use \Nether\Database\Verse;

abstract class Compiler {

	protected $QueryString = '';

	////////////////
	////////////////

	public function __construct(Nether\Database\Verse $v) {
		$this->Verse = $v;
		return;
	}

	////////////////
	////////////////

	public function Get() {
		switch($this->Verse->GetMode()) {
			case Verse::ModeDelete: return $this->GenerateDeleteQuery();
			case Verse::ModeInsert: return $this->GenerateInsertQuery();
			case Verse::ModeSelect: return $this->GenerateSelectQuery();
			case Verse::ModeUpdate: return $this->GenerateUpdateQuery();
			default: return 'derp.';
		}
	}

	////////////////
	////////////////

	abstract protected function GenerateDeleteQuery();
	abstract protected function GenerateInsertQuery();
	abstract protected function GenerateSelectQuery();
	abstract protected function GenerateUpdateQuery();

}
