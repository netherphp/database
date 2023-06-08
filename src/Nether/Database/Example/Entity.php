<?php

namespace Nether\Database\Example;

use Nether\Database\Meta;

#[Meta\TableClass(Name: 'Entities')]
class Entity {
/*//
@date 2022-02-18
this is an example class just sitting around autoloadable to help test the
meta class loading against for various purposes.
example: netherdb sql-create Nether.Database.Example.Entity
result: sql to create a table for this in the db.
//*/

	#[Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Meta\PrimaryKey]
	public int
	$ID;

	#[Meta\TypeChar(Size: 36, Variable: FALSE)]
	#[Meta\FieldIndex]
	public string
	$UUID;

	#[Meta\TypeChar(Size: 42, Variable: TRUE)]
	public string
	$Name;

	#[Meta\TypeIntBig(Unsigned: TRUE)]
	#[Meta\ForeignKey(Table: 'Photos', Key: 'ID')]
	public int
	$PhotoID;

}
