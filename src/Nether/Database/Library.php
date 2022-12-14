<?php

namespace Nether\Database;

use Nether\Common;

use Nether\Object\Datastore;

class Library
extends Common\Library {

	public function
	OnLoad(...$Argv):
	void {

		return;
	}

	public function
	OnPrepare(...$Argv):
	void {

		$Filename = sprintf(
			'%s/conf/env/%s/netherdb.json',
			$Argv['Path'], $Argv['Env']
		);

		if(file_exists($Filename) && is_readable($Filename))
		ConnectionConfig::LoadFromJSON($Filename);

		return;
	}

}
