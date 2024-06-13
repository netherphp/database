<?php

namespace Nether\Database;

use Nether\Common;

use Generator;

class ResultSet
extends Struct\PrototypeFindResult {

	public int
	$Total = 0;

	public int
	$Limit = 0;

	public int
	$Page = 1;

	public int
	$PageCount = 1;

	public ?string
	$Class = NULL;

	public ?Common\Datastore
	$Filters = NULL;

	public ?Result
	$Result = NULL;

	protected bool
	$FullDebug = TRUE;

	protected Generator|NULL|FALSE
	$Paginator = NULL;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2024-06-13')]
	public function
	Total():
	int {

		// because i wanted a companion to Count().

		return $this->Total;
	}

	public function
	SetPageLimit(int $Page, int $Limit):
	static {

		$this->Page = $Page;
		$this->Limit = $Limit;

		$this->UpdatePageCount();

		return $this;
	}

	public function
	UpdatePageCount():
	static {

		$this->PageCount = ceil( $this->Count() / $this->Limit );

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Absorb(self $Result):
	static {

		unset($this->Result, $this->Data, $this->Filters);

		$this->Result = $Result->Result;
		$this->Data = $Result->Data;
		$this->Filters = $Result->Filters;

		$this->Page = $Result->Page;
		$this->PageCount = $Result->PageCount;

		return $this;
	}

	public function
	PaginatorReset():
	static {

		// release the magically generated iterator thing if there was one

		if($this->Paginator instanceof Generator)
		unset($this->Paginator);

		$this->Paginator = NULL;

		// then if we had wandered we need to get the data for page one.

		if($this->Page !== 1) {
			die('HARD RESET');
			$this->Absorb(($this->Class)::Find(
				$this->Filters->Set('Page', 1)
			));
		}

		////////

		return $this;
	}

	public function
	PaginatorGenerate():
	?Generator {

		// round one we already have the first page here.

		yield $this;

		// round two replay and update. with some song and dance that is
		// me pretending to avoid a memory avalance the deeper you go.

		while($this->Page < $this->PageCount) {
			$this->Absorb(($this->Class)::Find(
				$this->Filters->Bump('Page', 1)
			));

			yield $this;
		}

		return NULL;
	}

	public function
	Paginator():
	bool {

		if($this->Paginator === NULL) {
			$this->Paginator = $this->PaginatorGenerate();
		}

		else {
			$this->Paginator->Next();
		}

		return $this->Paginator->Valid();
	}

	public function
	Walkinator():
	?Generator {

		$this->PaginatorReset();

		while($this->Paginator()) {
			$Row = NULL;

			foreach($this as $Row)
			yield $Row;
		}

		return NULL;
	}

	static public function
	FromManualData(iterable $Input, int $Page=1, int $Limit=25):
	static {

		$Output = new static($Input);
		$Output->SetPageLimit($Page, $Limit);

		return $Output;
	}

}
