<?php

namespace Nether\Database;

use Nether\Object\Datastore;

class Manager {

	static protected Datastore
	$CTX;

	static public function
	GetConnections():
	Datastore {

		return static::$CTX;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(?Datastore $Config=NULL) {

		if(!isset(static::$CTX))
		static::$CTX = new Datastore;

		if($Config instanceof Datastore)
		$this->Prepare($Config);

		return;
	}

	protected function
	Prepare(Datastore $Config):
	void {

		$Defined = $Config[Library::ConfConnections];
		$Item = NULL;
		$Alias = NULL;

		if(is_array($Defined)) {
			foreach($Defined as $Alias => $Item) {
				if($Item instanceof Connection)
				static::$CTX->Shove($Alias, $Item);
			}
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Exists(string $Alias):
	bool {

		return static::$CTX->HasKey($Alias);
	}

	public function
	Get(string $Alias):
	?Connection {

		if(!$this->Exists($Alias))
		throw new Error\InvalidConnection($Alias);

		return static::$CTX[$Alias];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	NewVerse(string $Alias):
	Verse {

		if(!$this->Exists($Alias))
		throw new Error\InvalidConnection($Alias);

		return $this->Get($Alias)->NewVerse();
	}

}
