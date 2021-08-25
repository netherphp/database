<?php

namespace Nether\Database\Struct;

use Nether\Database\Meta;

use ReflectionClass;
use Exception;

class TableClassInfo {

	public string
	$Name;

	public ?string
	$Comment = NULL;

	public array
	$Fields = [];

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $ClassName) {

		if(!class_exists($ClassName))
		throw new Exception("{$ClassName} not found",1);

		$Class = new ReflectionClass($ClassName);
		$Attribs = $Class->GetAttributes();
		$Attrib = NULL;
		$Inst = NULL;

		////////

		foreach($Attribs as $Attrib) {
			$Inst = $Attrib->NewInstance();

			if($Inst instanceof Meta\TableClass)
			$this->HandleTableClass($Class, $Inst);
		}

		if(!isset($this->Name))
		throw new Exception("{$ClassName} had no TableClass attribute",2);

		if(!count($this->Fields))
		throw new Exception("{$ClassName} had no fields defined",3);

		return;
	}

	protected function
	HandleTableClass(ReflectionClass $Class, Meta\TableClass $Inst):
	void {

		$this->Name = $Inst->Name;
		$this->Comment = $Inst->Comment;

		$Props = $Class->GetProperties();
		$Prop = NULL;
		$Attrib = NULL;
		$Inst = NULL;

		foreach($Props as $Prop) {
			$Attribs = $Prop->GetAttributes();

			// prime them so they can inspect eachother.
			foreach($Attribs as $Attrib)
			$Attrib->Inst = $Attrib->NewInstance();

			// find the table fields.
			foreach($Attribs as $Attrib)
			if($Attrib->Inst instanceof Meta\TableField)
			$this->Fields[] = $Attrib->Inst->Learn($Prop,$Attribs);

		}

		return;
	}

}
