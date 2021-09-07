<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Meta\TableField;
use Nether\Database\Struct\TableClassInfo;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ForeignKey {
/*//
@date 2021-08-20
//*/

	public string
	$Table;

	public string
	$Key;

	public ?string
	$Name;

	public string
	$Update;

	public string
	$Delete;

	public function
	__Construct(
		string $Table,
		string $Key,
		?string $Name=NULL,
		?string $Update=NULL,
		?string $Delete=NULL
	) {
	/*//
	@date 2021-08-20
	//*/

		$this->Table = $Table;
		$this->Key = $Key;

		$this->Name = $Name;
		$this->Update = $Update ?? 'CASCADE';
		$this->Delete = $Delete ?? 'CASCADE';

		return;
	}

	public function
	Learn(TableClassInfo $Table, TableField $Field):
	static {
	/*//
	@date 2021-08-24
	//*/

		if(!$this->Name)
		$this->Name = "Fnk{$Table->Name}{$this->Table}{$Field->Name}";

		return $this;
	}

}
