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
	SetDatabase(Nether\Database $DB) { $this->Database = $DB; return $this; }

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

	protected final function
	RequireDatabase() {
	/*//
	demand a database to have been set for things like rendering.
	//*/

		if(!$this->Database)
		throw new \Exception("No database has been defined for this Coda to use for sanitisation yet.");

		return;		
	}

	public function
	Render() {
	/*//
	compile this coda down into sql
	//*/

		$this->RequireDatabase();

		$MethodName = "Render_{$this->Database->GetType()}";

		if(method_exists($this,$MethodName))
		return $this->{$MethodName}();

		else
		return sprintf(
			'-- Coda %s does not currently support %s (%s)',
			static::class,
			$this->Type,
			$MethodName
		);
	}

}
