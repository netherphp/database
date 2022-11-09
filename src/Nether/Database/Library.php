<?php

namespace Nether\Database;
use Nether;

use Nether\Object\Datastore;

class Library {

	static public function
	Init(string $Path, string $Env, Datastore $Config, ...$Argv):
	void {

		$Filename = sprintf(
			'%s/conf/env/%s/netherdb.json',
			$Path,
			$Env
		);

		if(file_exists($Filename) && is_readable($Filename))
		Nether\Database\ConnectionConfig::LoadFromJSON($Filename);

		return;
	}

}
