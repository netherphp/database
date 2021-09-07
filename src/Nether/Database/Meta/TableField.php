<?php

namespace Nether\Database\Meta;

use Nether;
use Nether\Database\Struct\TableClassInfo;
use ReflectionProperty;
use Stringable;

abstract class TableField
implements Stringable {
/*//
@date 2021-08-20
//*/

	public ?string
	$Name;

	public bool
	$Nullable;

	public mixed
	$Default;

	public ?ForeignKey
	$ForeignKey = NULL;

	public ?FieldIndex
	$Index = NULL;

	public ?PrimaryKey
	$PrimaryKey = NULL;

	public function
	__Construct(
		?string $Name=NULL,
		bool $Nullable=TRUE,
		mixed $Default=FALSE
	) {
	/*//
	@date 2021-08-20
	//*/

		$this->Name = $Name;
		$this->Nullable = $Nullable;
		$this->Default = $Default;

		return;
	}

	public function
	Learn(TableClassInfo $Table, ReflectionProperty $Prop, ?array $Attribs=NULL):
	static {
	/*//
	@date 2021-08-20
	//*/

		$Attrib = NULL;

		////////

		// use the property name if none was supplied to the attribute.

		if($this->Name === NULL)
		$this->Name = $Prop->GetName();

		// then run through the attributes looking for additional info.

		if($Attribs !== NULL)
		foreach($Attribs as $Attrib) {
			if(!property_exists($Attrib,'Inst'))
			continue;

			// support nether object's origin meta.

			if($Attrib->Inst instanceof Nether\Object\Meta\PropertyOrigin)
			$this->Name = $Attrib->Inst->Name;

			// note if this is a simple field index.

			elseif($Attrib->Inst instanceof FieldIndex)
			$this->Index = $Attrib->Inst->Learn($Table, $this);

			// note if this is a foreign key.

			elseif($Attrib->Inst instanceof ForeignKey)
			$this->ForeignKey = $Attrib->Inst->Learn($Table, $this);

			elseif($Attrib->Inst instanceof PrimaryKey)
			$this->PrimaryKey = $Attrib->Inst->Learn($Table, $this);
		}

		return $this;
	}

	public function
	GetFieldDef():
	string {

		$Output = '';

		////////

		if(!$this->Nullable)
		$Output .= ' NOT NULL';

		if($this->Default !== FALSE) {
			if($this->Default === NULL)
			$Output .= ' DEFAULT NULL';

			else
			$Output .= sprintf(
				' DEFAULT "%s"',
				str_replace('"', '\\"', $this->Default)
			);
		}

		////////

		return ltrim($Output);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	abstract public function
	__ToString():
	string;

}
