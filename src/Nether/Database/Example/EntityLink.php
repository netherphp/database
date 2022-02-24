<?php

namespace Nether\Database\Example;

use Nether\Database\Meta\TableClass;
use Nether\Database\Meta\FieldIndex;
use Nether\Database\Meta\MultiFieldIndex;
use Nether\Database\Meta\TypeChar;
use Nether\Database\Meta\TypeIntBig;

#[TableClass('EntityLink')]
#[MultiFieldIndex([ 'ParentID', 'ChildID' ], Unique: TRUE)]
class EntityLink {

	#[TypeIntBig(Unsigned: TRUE, AutoInc: TRUE, Nullable: FALSE)]
	public int
	$ID;

	#[TypeChar(Size: 36)]
	#[FieldIndex]
	public string
	$UUID;

	#[TypeIntBig(Unsigned: TRUE)]
	public int
	$ParentID;

	#[TypeIntBig(Unsigned: TRUE)]
	public int
	$ChildID;

}
