<?php

namespace Nether\Database\Struct;

use ReflectionAttribute;

class AttributePair {

	public ReflectionAttribute
	$Attrib;

	public object
	$Inst;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(ReflectionAttribute $Attrib) {

		$this->Attrib = $Attrib;
		$this->Inst = $Attrib->NewInstance();

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	NewArrayFromArray(array $Attribs):
	array {

		$Output = [];
		$Attrib = NULL;

		foreach($Attribs as $Attrib)
		$Output[] = new static($Attrib);

		return $Output;
	}

}
