<?php

namespace Nether\Database\Meta;

use Nether\Database\Meta\Interface\FieldAttribute;
use Nether\Database\Meta\Interface\FieldDefinition;
use Nether\Database\Struct\TableClassInfo;
use Nether\Object\Meta\PropertyOrigin;

use Stringable;
use ReflectionProperty;

abstract class TableField
implements Stringable, FieldDefinition {
/*//
@date 2021-08-20
this is the base type for all of the fields. all of the types will extend
this and supply the needed sugar.
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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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
	__ToString():
	string {
	/*//
	@date 2022-02-21
	//*/

		return $this->GetFieldDef();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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
			if(!property_exists($Attrib, 'Inst'))
			continue;

			if($Attrib->Inst instanceof PropertyOrigin)
			$this->Name = $Attrib->Inst->Name;

			if($Attrib->Inst instanceof Interface\FieldAttribute) {
				if($Attrib->Inst instanceof FieldIndex)
				$this->Index = $Attrib->Inst->Learn($Table, $this);

				if($Attrib->Inst instanceof ForeignKey)
				$this->ForeignKey = $Attrib->Inst->Learn($Table, $this);

				if($Attrib->Inst instanceof PrimaryKey)
				$this->PrimaryKey = $Attrib->Inst->Learn($Table, $this);
			}
		}

		return $this;
	}

	public function
	GetFieldDef():
	string {
	/*//
	@date 2022-02-21
	this default implementation generates the context that all of the sql
	types share. extensions of this class can thusly generate the start of
	the field definition, and then concat the results of this parent method
	onto the end of that to be done.
	//*/

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

}
