<?php

namespace Nether\Database\Meta;

use Nether\Database\Struct\TableClassInfo;
use Nether\Database\Meta\Interface\TableAttribute;
use Nether\Database\Meta\Interface\TableIndex;

use Attribute;
use Exception;

#[Attribute(Attribute::TARGET_CLASS)]
class MultiFieldIndex
implements TableAttribute, TableIndex {

	public array
	$Fields;

	public ?string
	$Name;

	public bool
	$Unique;

	public ?string
	$Method;

	public function
	__Construct(
		array $Fields,
		bool $Unique=NULL,
		?string $Name=NULL,
		?string $Method=NULL
	) {
	/*//
	@date 2021-08-20
	//*/

		$this->Fields = $Fields;
		$this->Unique = $Unique;

		$this->Name = $Name;
		$this->Method = $Method;

		if(is_array($this->Fields))
		$this->Fields = array_filter(
			$this->Fields,
			(fn($Val)=> is_string($Val))
		);

		if(!count($this->Fields))
		throw new Exception('Fields is empty');

		return;
	}

	public function
	Learn(TableClassInfo $Table):
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
			join('', $this->Fields)
		);

		return $this;
	}

}
