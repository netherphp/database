<?php

namespace Nether\Database;
use \Nether;

abstract class Coda {

	protected $Type = 'mysql';

	public function
	__construct($opt=null) {

		$opt = new Nether\Object($opt,[
			'Field' => null,
			'Value' => null
		]);

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
