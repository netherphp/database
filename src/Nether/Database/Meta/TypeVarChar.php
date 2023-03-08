<?php

namespace Nether\Database\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TypeVarChar
extends TableField {
/*//
@date 2021-08-19
//*/

	public string
	$Size;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(?string $Name=NULL, int $Size=256, ...$Argv) {
	/*//
	@date 2021-08-19
	//*/

		parent::__Construct($Name, ...$Argv);

		$this->Size = $Size;
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

		$Def = "`{$this->Name}` VARCHAR({$this->Size}) ";
		$Def .= parent::GetFieldDef();

		////////

		return trim($Def);
	}

}
