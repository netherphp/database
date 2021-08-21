<?php

namespace Nether\Database\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TableClass {
/*//
@date 2021-08-20
//*/

	public string
	$Name;

	public string
	$PrimaryKey;

	public string
	$ObjectKey;

	public function
	__Construct(string $Name, string $PrimaryKey, string $ObjectKey) {
	/*//
	@date 2021-08-20
	//*/

		$this->Name = $Name;
		$this->PrimaryKey = $PrimaryKey;
		$this->ObjectKey = $ObjectKey;

		return;
	}

}
