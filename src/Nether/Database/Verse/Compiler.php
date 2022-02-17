<?php

namespace Nether\Database\Verse;

use Nether\Database\Verse;

use Exception;

abstract class Compiler {
/*//
@date 2014-10-21
provide the base api for the query compiling system.
//*/

	public Verse
	$Verse;
	/*//
	@date 2022-02-17
	//*/

	protected string
	$QueryString;
	/*//
	@date 2022-02-17
	//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(Verse $Verse) {
	/*//
	@date 2022-02-17
	//*/

		$this->Verse = $Verse;
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Get():
	string {
	/*//
	@date 2022-02-17
	//*/

		$Mode = $this->Verse->GetMode();

		if($Mode < Verse::ModeSelect || $Mode > Verse::ModeCreate)
		throw new Exception('invalid query mode');

		////////

		return match($this->Verse->GetMode()) {
			Verse::ModeSelect => $this->GenerateSelectQuery(),
			Verse::ModeInsert => $this->GenerateInsertQuery(),
			Verse::ModeUpdate => $this->GenerateUpdateQuery(),
			Verse::ModeDelete => $this->GenerateDeleteQuery(),
			Verse::ModeCreate => $this->GenerateCreateQuery(),
			default           => 'SELECT 0;'
		};
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	abstract protected function
	GenerateDeleteQuery():
	string;

	abstract protected function
	GenerateInsertQuery():
	string;

	abstract protected function
	GenerateSelectQuery():
	string;

	abstract protected function
	GenerateUpdateQuery():
	string;

	abstract protected function
	GenerateCreateQuery():
	string;

}
