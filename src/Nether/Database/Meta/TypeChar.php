<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Meta\Interface\FieldAttribute;

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
	__Construct(?string $Name=NULL, int $Size=256, bool $Variable=FALSE, bool $Nullable=TRUE, mixed $Default=FALSE) {
	/*//
	@date 2021-08-19
	//*/

		parent::__Construct(
			Name: $Name,
			Nullable: $Nullable,
			Default: $Default
		);

		$this->Size = $Size;
		$this->Variable = $Variable;

		return;
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
