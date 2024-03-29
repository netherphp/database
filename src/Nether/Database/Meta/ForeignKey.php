<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Meta\TableField;
use Nether\Database\Struct\TableClassInfo;
use Nether\Database\Meta\Interface\FieldAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ForeignKey
implements FieldAttribute {
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
		string|bool|NULL $Update=TRUE,
		string|bool|NULL $Delete=NULL
	) {
	/*//
	@date 2021-08-20
	//*/

		$this->Table = $Table;
		$this->Key = $Key;
		$this->Name = $Name;

		////////

		if($Update === TRUE)
		$this->Update = 'CASCADE';

		else
		$this->Update = $Update ?: 'SET NULL';

		////////

		if($Delete === TRUE)
		$this->Delete = 'CASCADE';

		else
		$this->Delete = $Delete ?? 'SET NULL';

		////////

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
