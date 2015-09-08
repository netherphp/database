<?php

namespace Nether\Database;
use \Nether;
use \PDO;

////////////////
////////////////

class Result {

	protected $Driver;
	/*//
	@type PDO
	the pdo object driving this query.
	//*/

	protected $Statement;
	/*//
	@type PDOStatement
	the pdo statement object for this query.
	//*/

	protected $Args;
	/*//
	@type array
	the list of arguments to use in the statement.
	//*/

	public $Error;
	/*//
	@type string
	the error message if any.
	//*/

	public $OK;
	/*//
	@type boolean
	a flag if the query was a success or not.
	//*/

	////////////////
	////////////////

	public function __construct($driver,$statement,$args) {
		list(
			$this->Driver,
			$this->Statement,
			$this->Args
		) = func_get_args();

		// try to execute the statement.
		if(!$statement->execute($this->Args)) {
			$this->Error = $statement->errorInfo()[2];
			$this->OK = false;
			return;
		}

		$this->OK = true;
		$this->Rows = $this->Statement->rowCount();
		return;
	}

	////////////////
	////////////////

	public function ID() {
	/*//
	@return string
	//*/

		return $this->Driver->lastInsertId();
	}

	public function Next($class='stdClass') {
	/*//
	@argv string ClassName default stdClass
	fetch the next row of the result set.
	//*/

		return $this->Statement->fetchObject($class);
	}

	public function Glomp($class='stdClass') {
	/*//
	@argv string ClassName default stdClass
	@return array
	get all the rows from the query.
	//*/

		$list = array();

		while($row = $this->Next($class))
		$list[] = $row;

		return $list;
	}

}
