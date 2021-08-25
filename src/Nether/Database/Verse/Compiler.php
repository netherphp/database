<?php

namespace Nether\Database\Verse;

use Nether;
use Nether\Database\Verse;

abstract class Compiler {

	public Nether\Database\Verse
	$Verse;

	protected string
	$QueryString = '';

	////////////////
	////////////////

	public function
	__Construct(Nether\Database\Verse $V) {
		$this->Verse = $V;
		return;
	}

	////////////////
	////////////////

	public function
	Get() {
		switch($this->Verse->GetMode()) {
			case Verse::ModeDelete: return $this->GenerateDeleteQuery();
			case Verse::ModeInsert: return $this->GenerateInsertQuery();
			case Verse::ModeSelect: return $this->GenerateSelectQuery();
			case Verse::ModeUpdate: return $this->GenerateUpdateQuery();
			case Verse::ModeCreate: return $this->GenerateCreateQuery();
			default: return 'derp.';
		}
	}

	////////////////
	////////////////

	abstract protected function
	GenerateDeleteQuery();

	abstract protected function
	GenerateInsertQuery();

	abstract protected function
	GenerateSelectQuery();

	abstract protected function
	GenerateUpdateQuery();

	abstract protected function
	GenerateCreateQuery();

}
