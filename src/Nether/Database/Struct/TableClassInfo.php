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
	$Attributes = [];

	public array
	$Fields = [];

	public array
	$Indexes = [];

	public string
	$PrimaryKey = '';

	public string
	$ObjectKey;

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

			elseif($Inst instanceof Meta\Interface\TableAttribute)
			$this->HandleTableAttribute($Class, $Inst);
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
		$Name = NULL;
		$Inst = NULL;

		foreach($Props as $Prop) {
			$Attribs = $Prop->GetAttributes();
			$Name = $Prop->GetName();

			// prime them so they can inspect eachother.
			foreach($Attribs as $Attrib)
			$Attrib->Inst = $Attrib->NewInstance();

			// find the table fields.
			foreach($Attribs as $Attrib) {
				if($Attrib->Inst instanceof Meta\Interface\FieldDefinition) {
					$this->Fields[$Name] = $Attrib->Inst->Learn($this,$Prop,$Attribs);

					if($this->Fields[$Name]->PrimaryKey) {
						$this->PrimaryKey = $this->Fields[$Name]->Name;
						$this->ObjectKey = $Name;
					}
				}

				if($Attrib->Inst instanceof Meta\Interface\FieldAttribute) {
					if(!array_key_exists($Name, $this->Fields))
					throw new Exception('must annotate a FieldDefinition prior to a FieldAttribute.');

					if($Attrib->Inst instanceof Meta\Interface\TableIndex)
					$this->Indexes[$Name] = $this->Fields[$Name];
				}
			}

		}

		return;
	}

	protected function
	HandleTableAttribute(ReflectionClass $Class, Meta\Interface\TableAttribute $Inst):
	void {

		if($Inst instanceof Meta\Interface\TableIndex)
		$this->Indexes[$Inst->Name] = $Inst->Learn($this);

		else
		$this->Attributes[] = $Inst->Learn($this);

		return;
	}

	public function
	GetFieldList():
	array {

		$Fields = [];
		$Field = NULL;

		foreach($this->Fields as $Field)
		$Fields[$Field->Name] = $Field;

		return $Fields;
	}

	public function
	GetIndexList():
	array{

		return $this->Indexes;
	}

	public function
	GetForeignKeyList():
	array {

		$Fields = [];
		$Field = NULL;

		foreach($this->Fields as $Field)
		if($Field->ForeignKey)
		$Fields[$Field->ForeignKey->Name] = $Field;

		return $Fields;
	}

	public function
	GetAttributeList():
	array {

		return $this->Attributes;
	}

}
