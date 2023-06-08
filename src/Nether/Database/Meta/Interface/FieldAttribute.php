<?php

namespace Nether\Database\Meta\Interface;

use Nether\Database\Struct\TableClassInfo;
use Nether\Database\Meta\TableField;

interface FieldAttribute {
/*//
@date 2022-02-21
tag for attributes that are fitting to be assigned at table level.
//*/

	public function
	Learn(TableClassInfo $Table, TableField $Field):
	static;

}
