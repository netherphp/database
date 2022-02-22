<?php

namespace Nether\Database\Meta\Interface;

use Nether\Database\Struct\TableClassInfo;
use ReflectionProperty;

interface FieldDefinition {
/*//
@date 2022-02-21
tag for attributes that are fitting to be assigned at table level.
//*/

	public function
	Learn(TableClassInfo $Table, ReflectionProperty $Prop, ?array $Attribs=NULL):
	static;

	public function
	GetFieldDef():
	string;

}
