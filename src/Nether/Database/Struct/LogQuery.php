<?php

namespace Nether\Database\Struct;

class LogQuery {

	public float
	$Time;

	public string
	$Query;

	public array
	$Input;

	public int
	$Count;

	public mixed
	$Trace;

	public function
	__Construct(
		float $Time,
		string $Query,
		array $Input,
		int $Count,
		mixed $Trace
	) {

		$this->Time = $Time;
		$this->Query = $Query;
		$this->Input = $Input;
		$this->Count = $Count;
		$this->Trace = $Trace;

		return;
	}

}
