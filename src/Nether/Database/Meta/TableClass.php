<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Meta\Interface\TableDefinition;
use Nether\Database\Struct\TableClassInfo;

#[Attribute(Attribute::TARGET_CLASS)]
class TableClass
implements TableDefinition {
/*//
@date 2021-08-20
//*/

	public string
	$Name;

	public ?string
	$Alias;

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
		?string $Alias=NULL,
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
		$this->Alias = $Alias;
		$this->Engine = $Engine;
		$this->Charset = $Charset;
		$this->Collate = $Collate;
		$this->Comment = $Comment;

		return;
	}

	public function
	Learn(TableClassInfo $Table):
	static {

		if($this->Alias === NULL)
		$this->Alias = strtoupper(substr($this->Name, 0, 2));

		return $this;
	}

}
