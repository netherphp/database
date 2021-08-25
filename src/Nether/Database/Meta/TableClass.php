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

	public ?string
	$Charset;

	public ?string
	$Collate;

	public ?string
	$Engine;

	public ?string
	$Comment;

	public function
	__Construct(
		string $Name,
		?string $Engine=NULL,
		?string $Charset=NULL,
		?string $Collate=NULL,
		?string $Comment=NULL
	) {
	/*//
	@date 2021-08-20
	//*/

		// @todo 2021-08-24 move defaults to config system.

		$this->Name = $Name;
		$this->Engine = $Engine;
		$this->Charset = $Charset;
		$this->Collate = $Collate;
		$this->Comment = $Comment;

		return;
	}

}
