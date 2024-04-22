<?php

namespace Nether\Database;

use Nether\Common\Datastore;

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
		$Name = NULL;

		if(is_array($Defined)) {
			foreach($Defined as $Name => $Item) {
				if($Item instanceof Connection) {
					static::$CTX->Shove(
						$Item->Name ?? $Name,
						$Item
					);
				}
			}
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Add(Connection $DBC):
	static {

		static::$CTX->Set($DBC->Name, $DBC);

		return $this;
	}

	public function
	Exists(string $Alias):
	bool {

		return static::$CTX->HasKey($Alias);
	}

	public function
	Get(string $Alias):
	?Connection {

		// if the alias is a class name and the class extends the
		// database prototype then we can determine the db alias from
		// that.

		if(str_contains($Alias, '\\') && class_exists($Alias)) {
			if(is_subclass_of($Alias, 'Nether\\Database\\Prototype'))
			$Alias = $Alias::$DBA;
		}

		////////

		if(!$this->Exists($Alias))
		throw new Error\ConnectionNotFound($Alias);

		return static::$CTX[$Alias];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	NewVerse(string $Alias):
	Verse {

		if(!$this->Exists($Alias))
		throw new Error\ConnectionNotFound($Alias);

		return $this->Get($Alias)->NewVerse();
	}

}
