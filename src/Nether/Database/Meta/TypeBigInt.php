<?php

namespace Nether\Database\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TypeBigInt
extends TableField {
/*//
@date 2021-08-19
//*/

	public bool
	$Unsigned;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(bool $Unsigned=FALSE, ...$Argv) {
	/*//
	@date 2021-08-19
	//*/

		parent::__Construct(...$Argv);

		$this->Unsigned = $Unsigned;
		return;
	}

	public function
	__ToString():
	string {
	/*//
	@date 2021-08-19
	//*/

		if($this->Unsigned)
		return "{$this->Name} BIGINT UNSIGNED";

		return "{$this->Name} BIGINT";
	}

}
