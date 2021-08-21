<?php

namespace Nether\Database\Meta;

use Nether;

use ReflectionProperty;

abstract class TableField {
/*//
@date 2021-08-20
//*/

	public ?string
	$Name;

	public ?ForeignKey
	$ForeignKey = NULL;

	public function
	__Construct(?string $Name=NULL) {
	/*//
	@date 2021-08-20
	//*/

		$this->Name = $Name;

		return;
	}

	public function
	Learn(ReflectionProperty $Prop, ?array $Attribs=NULL):
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

			// note if this is a foreign key.

			elseif($Attrib->Inst instanceof ForeignKey)
			$this->ForeignKey = $Attrib->Inst;
		}

		return $this;
	}

}
