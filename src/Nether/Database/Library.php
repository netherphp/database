<?php

namespace Nether\Database;

use Nether\Common;
use Nether\Database;

class Library
extends Common\Library {

	const
	ConfConnections = 'Nether.Database.Connections';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnLoad(...$Argv):
	void {

		return;
	}

	public function
	OnPrepare(...$Argv):
	void {

		$CTX = NULL;

		foreach(Manager::GetConnections() as $CTX)
		$CTX->Connect();

		return;
	}

}
