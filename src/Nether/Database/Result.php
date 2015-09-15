<?php

namespace Nether\Database;
use \Nether;

use \PDO;

class Result {
/*//
this class will wrap around a group of behaviours that defines a result set
returned by a database query. it will provide some basic set interaction like
fetching the next row or even more simple things like letting you know if the
query was even a success.
//*/

	protected
	$Database = null;
	/*//
	@type Nether\Database
	the database connection we are interacting with.
	//*/

	protected
	$Statement = null;
	/*//
	@type PDOStatement
	the pdo statement object for this query.
	//*/

	protected
	$Args = null;
	/*//
	@type array
	the list of arguments to use in the statement.
	//*/

	public function
	GetArgs() {
	/*//
	@return array|null
	get the data used as query arugments.
	//*/

		return $this->Args;
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Time = null;
	/*//
	@type float
	the time in seconds this query took to execute.
	//*/

	public function
	GetTime() {
	/*//
	@return int
	get the time the query took to run.
	//*/

		return $this->Time;
	}

	////////////////////////////////
	////////////////////////////////

	public
	$Error = null;
	/*//
	@type string
	@todo make this protected.
	the error message if any.
	//*/

	public function
	GetError() {
	/*//
	get the error message if there was one.
	//*/

		return $this->Error;
	}

	////////////////////////////////
	////////////////////////////////

	public
	$OK = null;
	/*//
	@type boolean
	@todo make this protected.
	a flag if the query was a success or not.
	//*/

	public function
	IsOK() {
	/*//
	get if this query was a success or not.
	//*/

		return $this->OK;
	}

	////////////////////////////////
	////////////////////////////////

	protected
	$Count = 0;
	/*//
	@type int
	how many rows were found on success.
	//*/

	public function
	GetCount() {
	/*//
	@return int
	get how many rows were found on success.
	//*/

		return $this->Count;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	__construct($Database,$Statement,$Args) {
	/*//
	given a database, a statement, and a dateset, wrap a database result set
	into this little pseudoiterable object.
	//*/

		list(
			$this->Database,
			$this->Statement,
			$this->Args
		) = func_get_args();

		$Time = microtime(true);

		// try to execute the statement.
		if(!$this->Statement->Execute($this->Args)) {
			$this->Error = $this->Statement->ErrorInfo()[2];
			$this->OK = false;
			return;
		}

		$this->Time = microtime(true) - $Time;

		/*
		print_r([
			'Time' => $this->GetTime(),
			'Query' => $this->GetQuery(),
			'Args' => $Args
		]);
		*/

		$this->OK = true;
		$this->Count = $this->Statement->RowCount();
		$this->Rows = $this->Count; // deprecated.
		return;
	}

	////////////////////////////////
	////////////////////////////////

	public function
	GetQuery() {
	/*//
	@return string
	fetch the query sql that was executed on the server.
	//*/

		return $this->Statement->queryString;
	}

	public function
	GetInsertID() {
	/*//
	@return string
	if the last statement was an insert statement, then this will ask the
	database for the id it last inserted on this connection.
	//*/

		return $this->Database->GetDriver()->LastInsertId();
	}

	public function
	ID() {
	/*//
	@alias Nether\Database\Result::GetID
	@deprecated
	//*/

		return $this->GetInsertID();
	}

	////////////////////////////////
	////////////////////////////////

	public function
	Next($Class='stdClass') {
	/*//
	@argv string ClassName default stdClass
	fetch the next row of the result set.
	//*/

		return $this->Statement->FetchObject($Class);
	}

	public function
	Glomp($Class='stdClass') {
	/*//
	@argv string ClassName default stdClass
	@return array
	get all the rows from the query.
	//*/

		$List = [];
		$Row = null;

		while($Row = $this->Next($Class))
		$List[] = $Row;

		return $List;
	}

}
