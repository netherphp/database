<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Common;
use Nether\Database\Meta\Interface\FieldAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[Common\Meta\Date('2023-12-27')]
class TypeDate
extends TableField {


	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(?string $Name=NULL, bool $Nullable=TRUE, mixed $Default=FALSE) {

		parent::__Construct(
			Name: $Name,
			Nullable: $Nullable,
			Default: $Default
		);

		return;
	}

	public function
	GetFieldDef():
	string {

		$Def = "`{$this->Name}` DATE ";
		$Def .= parent::GetFieldDef();

		////////

		return trim($Def);
	}

}
