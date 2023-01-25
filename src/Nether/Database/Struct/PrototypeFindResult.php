<?php

namespace Nether\Database\Struct;
use Nether;

class PrototypeFindResult
extends Nether\Common\Datastore {

	public int
	$Total = 0;

	public int
	$Limit = 0;

	public int
	$Page = 1;

	public int
	$PageCount = 1;

	public ?Nether\Database\Result
	$Result = NULL;

	protected bool
	$FullDebug = TRUE;

}
