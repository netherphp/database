<?php

namespace Nether\Database\Verse;

use \Exception;
use \Nether;
use \Nether\Database\Verse;

class MySQL extends Compiler {

	protected function GetJoinType($flags) {
		$type = '';

		if($flags & Verse::JoinInner) $type .= 'INNER ';
		if($flags & Verse::JoinOuter) $type .= 'OUTER ';
		if($flags & Verse::JoinNatural) $type .= 'NATURAL ';
		if($flags & Verse::JoinLeft) $type .= 'LEFT ';
		if($flags & Verse::JoinRight) $type .= 'RIGHT ';

		return "{$type}JOIN";
	}

	protected function GetWhereType($flags) {
		$type = '';

		if($flags & Verse::WhereAnd) $type .= 'AND ';
		if($flags & Verse::WhereOr) $type .= 'OR ';
		if($flags & Verse::WhereNot) $type .= 'NOT ';

		return rtrim($type);
	}

	protected function GetSortType($flags) {
		$type = '';

		if($flags & Verse::SortAsc) $type = 'ASC';
		if($flags & Verse::SortDesc) $type = 'DESC';

		return $type;
	}

	////////////////
	////////////////

	protected function GetConditionString($conds) {
		$first = true;
		$string = 'WHERE ';

		foreach($conds as $cond) {
			if($first) {
				$string .= "{$cond->Query} ";
				$first = false;
			} else {
				$string .= sprintf(
					'%s %s ',
					$this->GetWhereType($cond->Flags),
					$cond->Query
				);
			}
		}

		return $string;
	}

	protected function GetJoinString($joins) {
		$string = '';

		foreach($joins as $join) {
			$string .= sprintf(
				'%s %s ',
				$this->GetJoinType($join->Flags),
				$join->Query
			);
		}

		return $string;
	}

	protected function GetLimitString($limit) {

		return "LIMIT {$limit} ";
	}

	protected function GetOffsetString($offset) {

		return "OFFSET {$offset} ";
	}

	protected function GetSetString($sets) {
		$first = true;
		$string = 'SET ';

		foreach($sets as $field => $value) {
			$string .= sprintf(
				'%s%s=%s',
				(($first)?(''):(', ')),
				$field,
				$value
			);
			$first = false;
		}

		return "{$string} ";
	}

	protected function GetSortString($sorts) {
		$first = true;
		$string = 'ORDER BY ';

		foreach($sorts as $sort) {
			$string .= sprintf(
				'%s%s %s',
				(($first)?(''):(', ')),
				$sort->Query,
				$this->GetSortType($sort->Flags)
			);
			$first = false;
		}

		return "{$string} ";
	}

	////////////////
	////////////////

	protected function GenerateDeleteQuery() {
	/*//
	@return string
	generate a DELETE style query.
	//*/

		$this->QueryString = sprintf(
			'DELETE FROM %s ',
			join(', ',$this->Verse->GetTables())
		);

		if($conds = $this->Verse->GetConditions())
		$this->QueryString .= $this->GetConditionString($conds);

		if(($limit = $this->Verse->GetLimit()) !== false)
		$this->QueryString .= $this->GetLimitString($limit);

		return $this->QueryString;
	}

	protected function GenerateInsertQuery() {
	/*//
	@return string
	generate an INSERT style query.
	//*/

		$table = current($this->Verse->GetTables());
		$fields = join(',',array_keys($this->Verse->GetFields()));
		$values = join(',',array_values($this->Verse->GetFields()));

		$this->QueryString = sprintf(
			'INSERT INTO %s (%s) VALUES (%s) ',
			$table,
			$fields,
			$values
		);

		return $this->QueryString;
	}

	protected function GenerateSelectQuery() {
	/*//
	@return string
	generate a SELECT style query.
	//*/

		$this->QueryString = sprintf(
			'SELECT %s FROM %s ',
			implode(', ',$this->Verse->GetFields()),
			implode(', ',$this->Verse->GetTables())
		);

		if($joins = $this->Verse->GetJoins())
		$this->QueryString .= $this->GetJoinString($joins);

		if($conds = $this->Verse->GetConditions())
		$this->QueryString .= $this->GetConditionString($conds);

		if($sorts = $this->Verse->GetSorts())
		$this->QueryString .= $this->GetSortString($sorts);

		if(($limit = $this->Verse->GetLimit()) !== 0)
		$this->QueryString .= $this->GetLimitString($limit);

		if(($offset = $this->Verse->GetOffset()) !== 0)
		$this->QueryString .= $this->GetOffsetString($offset);

		return trim($this->QueryString);
	}


	protected function GenerateUpdateQuery() {
	/*//
	@return string
	generate an UPDATE style query.
	//*/

		$this->QueryString = sprintf(
			'UPDATE %s %s',
			join(', ',$this->Verse->GetTables()),
			$this->GetSetString($this->Verse->GetFields())
		);

		if($conds = $this->Verse->GetConditions())
		$this->QueryString .= $this->GetConditionString($conds);

		if(($limit = $this->Verse->GetLimit()) !== false)
		$this->QueryString .= $this->GetLimitString($limit);

		return $this->QueryString;
	}

}
