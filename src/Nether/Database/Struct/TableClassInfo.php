<?php

namespace Nether\Database\Struct;

use Nether\Database\Meta;

use ReflectionClass;
use Exception;

class TableClassInfo {

	public string
	$Name;

	public ?string
	$Alias;

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
		$Attribs = $this->DigForAttributes($Class);
		$Attrib = NULL;
		$Inst = NULL;

		////////

		foreach($Attribs as $Attrib) {
			$Inst = $Attrib->NewInstance();

			if($Inst instanceof Meta\TableClass) {
				$Inst->Learn($this);
				$this->HandleTableClass($Class, $Inst);
			}

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
		$this->Alias = $Inst->Alias;
		$this->Comment = $Inst->Comment;

		$ClassDef = NULL;
		$FieldSet = [];
		$Props = $Class->GetProperties();
		$Prop = NULL;
		$Attrib = NULL;
		$Name = NULL;

		foreach($Props as $Prop) {
			$Name = $Prop->GetName();

			$Attribs = AttributePair::NewArrayFromArray(
				$Attribs = $Prop->GetAttributes()
			);

			foreach($Attribs as $Attrib) {
				if($Attrib->Inst instanceof Meta\Interface\FieldDefinition) {
					$ClassDef = $Prop->GetDeclaringClass()->GetName();

					if(!isset($FieldSet[$ClassDef]))
					$FieldSet[$ClassDef] = [];

					$FieldSet[$ClassDef][$Name] = $Attrib->Inst->Learn(
						$this,
						$Prop,
						$Attribs
					);

					if($FieldSet[$ClassDef][$Name]->PrimaryKey) {
						$this->PrimaryKey = $FieldSet[$ClassDef][$Name]->Name;
						$this->ObjectKey = $Name;
					}
				}

				if($Attrib->Inst instanceof Meta\Interface\FieldAttribute) {
					if(!array_key_exists($Name, $FieldSet[$ClassDef]))
					throw new Exception('must annotate a FieldDefinition prior to a FieldAttribute.');

					if($Attrib->Inst instanceof Meta\Interface\TableIndex)
					$this->Indexes[$Name] = $FieldSet[$ClassDef][$Name];
				}
			}
		}

		// ok so
		// theoretically
		// fieldset will have its sets defined in like order of dig.
		// meaning like the most parent of class will have been the last
		// in the set, since php reflection seems to dig them up child
		// class first.

		$FieldSet = array_reverse($FieldSet);

		array_walk(
			$FieldSet,
			fn(array $Fields)
			=> $this->Fields = array_merge($this->Fields, $Fields)
		);

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
	DigForAttributes(ReflectionClass $Class):
	array {

		$Output = [];
		$Found = NULL;
		$Stop = FALSE;
		$AName = NULL;

		while($Class) {
			$Found = $Class->GetAttributes();

			foreach($Found as $Attr) {
				$AName = $Attr->GetName();

				// if this is a database attribute we do want to make
				// note of it.

				if(str_starts_with($AName, 'Nether\\Database\\Meta'))
				$Output[] = $Attr;

				// if this was a table class definition attribute then
				// we want to stop searching parent classes when we
				// finish with this one.

				if($AName === Meta\TableClass::class)
				$Stop = TRUE;
			}

			if($Stop)
			break;

			$Class = $Class->GetParentClass();
		}

		return $Output;
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

	public function
	HasAttribute(string $Type):
	bool {

		$Attr = NULL;

		foreach($this->Attributes as $Attr)
		if($Attr::class === $Type)
		return TRUE;

		return FALSE;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetPrefixedAlias(?string $TPre, ?string $Alias=NULL):
	string {

		// provide a way to insert custom aliases so classes can make the
		// queries more relevant. thinking in the case of the uploads table
		// a user might want to prefix it to sound like an image for the
		// avatar lookup.

		$Alias ??= $this->Alias;

		////////

		if($TPre === NULL)
		return '';

		if($TPre === 'Main')
		return $TPre;

		$TPre = ltrim("{$TPre}_{$Alias}", '_');

		return $TPre;
	}

	public function
	GetAliasedTable(?string $Alias=NULL):
	string {

		$Alias ??= $this->Alias;

		return "`{$this->Name}` `{$Alias}`";
	}

	public function
	GetAliasedPK(?string $Alias=NULL):
	string {

		return $this->GetAliasedField($this->PrimaryKey, $Alias);
	}

	public function
	GetAliasedField(string $Field, ?string $Alias=NULL):
	string {

		$Alias ??= $this->Alias;

		return "`{$Alias}`.`{$Field}`";
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetPrefixedKey(string $Alias):
	string {

		// deprecated

		return $this->GetAliasedPK($Alias);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetPrefixedField(string $Alias, string $Field):
	string {

		return "`{$Alias}`.`{$Field}`";
	}

}
