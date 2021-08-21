<?php

namespace Nether\Database\Meta;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ForeignKey {
/*//
@date 2021-08-20
//*/

	public string
	$Table;

	public string
	$Key;

	public string
	$Update;

	public string
	$Delete;

	public function
	__Construct(
		string $Table,
		string $Key,
		?string $Update=NULL,
		?string $Delete=NULL
	) {
	/*//
	@date 2021-08-20
	//*/

		$this->Table = $Table;
		$this->Key = $Key;
		$this->Update = $Update ?? 'CASCADE';
		$this->Delete = $Delete ?? 'CASCADE';

		return;
	}

}
