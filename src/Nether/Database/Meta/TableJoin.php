<?php

namespace Nether\Database\Meta;

use Nether\Database;
use Nether\Common;

use Attribute;
use Exception;
use ReflectionAttribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
#[Common\Meta\Date('2023-07-14')]
class TableJoin
implements Common\Prototype\PropertyInfoInterface {

	public ?string
	$Alias;

	public ?string
	$Field;

	public bool
	$Extend;

	public function
	__Construct(?string $JField=NULL, ?string $JAlias=NULL, bool $Extend=FALSE) {

		// the table field to join on.
		$this->Field = $JField;

		// the table alias to override.
		$this->Alias = $JAlias;

		// if it should also reach out to extend.
		$this->Extend = $Extend;

		return;
	}

	public function
	OnPropertyInfo(Common\Prototype\PropertyInfo $Attrib, ReflectionProperty $RefProp, ReflectionAttribute $RefAttrib):
	void {

		if(!is_a($Attrib->Type, Database\Prototype::class, TRUE))
		throw new Exception('TableJoin property should extend Database Prototype');

		$Table = ($Attrib->Type)::GetTableInfo();

		if(!$this->Field)
		$this->Field = $Table->PrimaryKey;

		return;
	}

}
