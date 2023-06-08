<?php

namespace Nether\Database\Meta;

use Attribute;
use Nether\Database\Struct\TableClassInfo;
use Nether\Database\Meta\Interface\TableIndex;
use Nether\Database\Meta\Interface\FieldAttribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FieldIndex
implements FieldAttribute, TableIndex {
/*//
@date 2021-08-20
//*/

	public ?string
	$Name;

	public bool
	$Unique;

	public ?string
	$Method;

	public function
	__Construct(
		bool $Unique=FALSE,
		?string $Name=NULL,
		?string $Method=NULL
	) {
	/*//
	@date 2021-08-20
	//*/

		$this->Name = $Name;
		$this->Unique = $Unique;
		$this->Method = $Method;

		return;
	}

	public function
	Learn(TableClassInfo $Table, TableField $Field):
	static {
	/*//
	@date 2021-08-24
	//*/

		$Prefix = 'Idx';

		if($this->Unique)
		$Prefix = 'Unq';

		if(!$this->Name)
		$this->Name = sprintf(
			'%s%s%s',
			$Prefix,
			$Table->Name,
			$Field->Name
		);

		return $this;
	}

}
