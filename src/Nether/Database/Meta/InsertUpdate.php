<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Meta\Interface\TableAttribute;
use Nether\Database\Struct\TableClassInfo;

#[Attribute(Attribute::TARGET_CLASS)]
class InsertUpdate
implements TableAttribute {
/*//
@date 2021-08-20
tables with this attribute will include ON DUPLICATE KEY UPDATE clauses to
force update collisions.
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
