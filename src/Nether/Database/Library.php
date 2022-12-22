<?php

namespace Nether\Database;

use Nether\Common;
use Nether\Database;

class Library
extends Common\Library {

	const
	ConfConnections = 'Nether.Database.Connections',
	ConfDefaultConnection = 'Nether.Database.DefaultConnection';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnLoad(...$Argv):
	void {

		static::$Config->BlendRight([
			static::ConfDefaultConnection => 'Default'
		]);

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
