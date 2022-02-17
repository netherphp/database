<?php

namespace Nether\Database\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TypeChar
extends TableField {
/*//
@date 2021-08-19
//*/

	public string
	$Size;

	public bool
	$Variable;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(?string $Name=NULL, int $Size=256, bool $Variable=FALSE) {
	/*//
	@date 2021-08-19
	//*/

		parent::__Construct($Name);

		$this->Size = $Size;
		$this->Variable = $Variable;
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

		$FieldType = match($this->Variable) {
			TRUE=> 'VARCHAR',
			default=> 'CHAR'
		};

		$Def = "`{$this->Name}` {$FieldType}({$this->Size}) ";
		$Def .= parent::GetFieldDef();

		////////

		return trim($Def);
	}

}
