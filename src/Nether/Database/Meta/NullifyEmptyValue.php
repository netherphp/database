<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Meta\Interface\TableAttribute;
use Nether\Database\Struct\TableClassInfo;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NullifyEmptyValue
implements TableAttribute {
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
	Learn(TableClassInfo $Table):
	static {
	/*//
	@date 2021-08-24
	//*/

		return $this;
	}

}
