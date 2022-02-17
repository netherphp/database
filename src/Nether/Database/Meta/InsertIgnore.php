<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Meta\TableField;
use Nether\Database\Struct\TableClassInfo;

#[Attribute(Attribute::TARGET_CLASS)]
class InsertIgnore {
/*//
@date 2021-08-20
tables with this attribute will use INSERT IGNORE instead of normal INSERT.
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
