<?php

namespace Nether\Database;
use \Nether;

abstract class Coda {

	public function
	__construct($opt=null) {

		$opt = new Nether\Object($opt,[
			'Database' => null,
			'Field'    => null,
			'Value'    => null
		]);

		$this->Database = $opt->Database;
		$this->Field = $opt->Field;
		$this->Value = $opt->Value;

		return;
	}

	public function
	__toString() {
		return $this->Render();
	}

	////////////////
	////////////////

	protected $Database;

	public function
	GetDatabase() { return $this->Database; }

	public function
	SetDatabase($DB) { $this->Database = $DB; return $this; }

	protected $Field;

	public function
	GetField() { return $this->Field; }

	public function
	SetField($Field) { $this->Field = $Field; return $this; }

	protected $Value;

	public function
	GetValue() { return $this->Value; }

	public function
	SetValue($Value) { $this->Value = $Value; return $this; }

	////////////////
	////////////////

	public function
	Render() {

		if($this->Database)
		$this->Type = $this->Database->GetType();

		////////

		$MethodName = "Render_{$this->Type}";

		if(method_exists($this,$MethodName))
		return $this->{$MethodName}();

		else
		return sprintf(
			'-- Coda %s does not currently support %s',
			static::class,
			$this->Type
		);
	}

}
