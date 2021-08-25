<?php

namespace Nether\Database\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TypeText
extends TableField {
/*//
@date 2021-08-19
//*/

	public function
	__Construct(?string $Name=NULL) {
	/*//
	@date 2021-08-19
	//*/

		parent::__Construct($Name);

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

		$Def = "`{$this->Name}` TEXT ";
		$Def .= parent::GetFieldDef();

		////////

		return trim($Def);
	}

}
