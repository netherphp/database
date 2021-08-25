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

	public bool
	$AutoInc;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(bool $Unsigned=FALSE, bool $AutoInc=FALSE, ...$Argv) {
	/*//
	@date 2021-08-19
	//*/

		parent::__Construct(...$Argv);

		$this->Unsigned = $Unsigned;
		$this->AutoInc = $AutoInc;

		return;
	}

	public function
	__ToString():
	string {
	/*//
	@date 2021-08-19
	//*/

		return $this->GetFieldDef();
	}

	public function
	GetFieldDef():
	string {

		$Def = "`{$this->Name}` BIGINT ";

		if($this->Unsigned)
		$Def .= 'UNSIGNED ';

		if($this->AutoInc)
		$Def .= 'AUTO_INCREMENT ';

		$Def .= parent::GetFieldDef();

		////////

		return trim($Def);
	}

}
