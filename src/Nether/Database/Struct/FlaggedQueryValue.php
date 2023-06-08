<?php

namespace Nether\Database\Struct;

class FlaggedQueryValue {

	public int
	$Flags;

	public mixed
	$Query;

	public function
	__Construct(int $Flags, mixed $Query) {

		$this->Flags = $Flags;
		$this->Query = $Query;
		return;
	}

}
