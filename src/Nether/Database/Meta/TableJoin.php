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
#[Common\Meta\Info('Allows Prototype based classes to auto-join foreign tables of classes that are also prototype based.')]
class TableJoin
implements Common\Prototype\PropertyInfoInterface {

	#[Common\Meta\Date('2023-07-14')]
	public ?string
	$Alias;

	#[Common\Meta\Date('2023-07-14')]
	public ?string
	$Field;

	#[Common\Meta\Date('2023-07-14')]
	public bool
	$Extend;

	#[Common\Meta\Date('2023-07-14')]
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

	#[Common\Meta\Date('2023-07-14')]
	public function
	OnPropertyInfo(Common\Prototype\PropertyInfo $Attrib, ReflectionProperty $RefProp, ReflectionAttribute $RefAttrib):
	void {

		if(!is_a($Attrib->Type, Database\Prototype::class, TRUE))
		throw new Database\Error\TableJoinInvalidTarget($Attrib->Type);

		if(!$this->Field) {
			$Table = ($Attrib->Type)::GetTableInfo();
			$this->Field = $Table->PrimaryKey;
		}

		return;
	}

}
