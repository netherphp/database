<?php

namespace Nether\Database;

use Nether;

class ConnectionConfig
extends Connection {
/*//
@date 2022-02-18
this class provides newer constructor so that i can promote using
named args instead of arrays for configuration. it may over time
bcecome the primary method of config. or maybe not. see how we feel
about it after using it some.
//*/

	public function
	__Construct(
		string $Type='',
		string $Hostname='',
		string $Database='',
		string $Username='',
		string $Password='',
		string $Charset='utf8'
	) {

		// idealy, instead of this, this class would replace the older
		// Connection class.

		parent::__Construct([
			'Type'     => $Type,
			'Hostname' => $Hostname,
			'Database' => $Database,
			'Username' => $Username,
			'Password' => $Password,
			'Charset'  => $Charset
		]);

		return;
	}

}
