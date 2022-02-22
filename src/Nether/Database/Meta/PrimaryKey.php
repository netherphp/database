<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Meta\TableField;
use Nether\Database\Meta\Interface\FieldAttribute;
use Nether\Database\Struct\TableClassInfo;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey
implements FieldAttribute {
/*//
@date 2021-08-20
//*/

	public function
	__Construct() {
	/*//
	@date 2021-08-20
	//*/

		return;
	}

	public function
	Learn(TableClassInfo $Table, TableField $Field):
	static {
	/*//
	@date 2021-08-24
	//*/

		return $this;
	}

}
