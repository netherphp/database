<?php

namespace Nether\Database\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MultiFieldIndex {
/*//
@date 2021-08-20
//*/

	public ?string
	$Name;

	public ?string
	$Type;

	public function
	__Construct(
		?string $Name=NULL,
		?string $Type=NULL
	) {
	/*//
	@date 2021-08-20
	//*/

		$this->Name = $Name;
		$this->Type = $Type;

		return;
	}

	public function
	Learn(TableField $Field):
	static {
	/*//
	@date 2021-08-24
	//*/

		if(!$this->Name)
		$this->Name = "Idx{$Field->Name}";

		return $this;
	}

}
