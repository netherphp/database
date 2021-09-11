<?php

namespace Nether\Database\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TypeIntTiny
extends TypeInt {
/*//
@date 2021-08-19
//*/

	protected string
	$TypeDef = 'TINYINT';

}
