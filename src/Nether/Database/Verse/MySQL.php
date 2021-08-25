<?php

namespace Nether\Database\Verse;

use Nether\Database\Verse;
use Nether\Database\Meta\TableField;
use Nether\Database\Meta\FieldIndex;
use Nether\Database\Meta\ForeignKey;
use Nether\Database\Meta\PrimaryKey;

class MySQL
extends Compiler {

	protected function
	GetJoinType($Flags) {
		$Type = '';

		if($Flags & Verse::JoinInner) $Type .= 'INNER ';
		if($Flags & Verse::JoinOuter) $Type .= 'OUTER ';
		if($Flags & Verse::JoinNatural) $Type .= 'NATURAL ';
		if($Flags & Verse::JoinLeft) $Type .= 'LEFT ';
		if($Flags & Verse::JoinRight) $Type .= 'RIGHT ';

		return "{$Type}JOIN";
	}

	protected function
	GetWhereType($Flags) {
		$Type = '';

		if($Flags & Verse::WhereAnd) $Type .= 'AND ';
		if($Flags & Verse::WhereOr) $Type .= 'OR ';
		if($Flags & Verse::WhereNot) $Type .= 'NOT ';

		return rtrim($Type);
	}

	protected function
	GetSortType($Flags) {
		$Type = '';

		if($Flags & Verse::SortAsc) $Type = 'ASC';
		if($Flags & Verse::SortDesc) $Type = 'DESC';

		return $Type;
	}

	////////////////
	////////////////

	protected function
	GetConditionString($Conds) {

		$Cond = NULL;
		$First = TRUE;
		$String = 'WHERE ';

		foreach($Conds as $Cond) {
			if($First) {
				$String .= "{$Cond->Query} ";
				$First = FALSE;
			} else {
				$String .= sprintf(
					'%s %s ',
					$this->GetWhereType($Cond->Flags),
					$Cond->Query
				);
			}
		}

		return $String;
	}

	protected function
	GetHavingString($Conds) {

		$Cond = NULL;

		$First = TRUE;
		$String = 'HAVING ';

		foreach($Conds as $Cond) {
			if($First) {
				$String .= "{$Cond->Query} ";
				$First = FALSE;
			} else {
				$String .= sprintf(
					'%s %s ',
					$this->GetWhereType($Cond->Flags),
					$Cond->Query
				);
			}
		}

		return $String;
	}

	protected function
	GetJoinString($Joins) {
		$Join = NULL;

		$Join = NULL;

		$String = '';

		foreach($Joins as $Join) {
			$String .= sprintf(
				'%s %s ',
				$this->GetJoinType($Join->Flags),
				$Join->Query
			);
		}

		return $String;
	}

	protected function
	GetLimitString($Limit) {

		return "LIMIT {$Limit} ";
	}

	protected function
	GetOffsetString($Offset) {

		return "OFFSET {$Offset} ";
	}

	protected function
	GetSetString($Sets) {

		$Field = NULL;
		$Value = NULL;

		$First = TRUE;
		$String = 'SET ';

		foreach($Sets as $Field => $Value) {
			$String .= sprintf(
				'%s%s=%s',
				(($First)?(''):(', ')),
				$Field,
				$Value
			);
			$First = FALSE;
		}

		return "{$String} ";
	}

	protected function
	GetSortString($Sorts) {

		$Sort = NULL;
		$First = TRUE;
		$String = 'ORDER BY ';

		foreach($Sorts as $Sort) {
			$String .= sprintf(
				'%s%s %s',
				(($First)?(''):(', ')),
				$Sort->Query,
				$this->GetSortType($Sort->Flags)
			);
			$First = FALSE;
		}

		return "{$String} ";
	}

	protected function
	GetGroupString($Groups) {

		$String = sprintf(
			'GROUP BY %s ',
			implode(', ',$Groups)
		);

		return $String;
	}

	protected function
	GetCreateFieldsString(array $Sets, array $Indexes, array $ForeignKeys):
	string {

		$Value = NULL;
		$First = TRUE;
		$String = '';

		// generate the list of fields on this table.

		foreach($Sets as $Value) {
			$String .= sprintf(
				'%s%s',
				(($First)?("(\n\t"):(",\n\t")),
				$Value
			);

			$First = FALSE;
		}

		// generate primary key on this table.

		foreach($Sets as $Value) {
			if($Value instanceof TableField)
			if($Value->PrimaryKey instanceof PrimaryKey) {
				$String .= sprintf(
					'%sPRIMARY KEY (`%s`) USING BTREE',
					(($First)?("(\n\t"):(",\n\t")),
					$Value->Name
				);

				$First = FALSE;
			}
		}

		// generate the list of indexes on this table.

		foreach($Indexes as $Value) {
			if($Value instanceof TableField)
			if($Value->Index instanceof FieldIndex) {
				$String .= sprintf(
					'%sINDEX `%s` (`%s`) USING %s',
					(($First)?("(\n\t"):(",\n\t")),
					$Value->Index->Name,
					$Value->Name,
					$Value->Index->Method ?? 'BTREE'
				);

				$First = FALSE;
			}
		}

		// generate the list of foreign keys on this table.

		foreach($ForeignKeys as $Value) {
			if($Value instanceof TableField)
			if($Value->ForeignKey instanceof ForeignKey) {
				if(!array_key_exists($Value->ForeignKey->Name,$Indexes))
				$String .= sprintf(
					'%sINDEX `%s` (`%s`) USING BTREE',
					(($First)?("(\n\t"):(",\n\t")),
					$Value->ForeignKey->Name,
					$Value->Name
				);

				$String .= sprintf(
					'%sCONSTRAINT `%s` FOREIGN KEY(`%s`) REFERENCES `%s` (`%s`) ON UPDATE %s ON DELETE %s',
					(($First)?("(\n\t"):(",\n\t")),
					$Value->ForeignKey->Name,
					$Value->Name,
					$Value->ForeignKey->Table,
					$Value->ForeignKey->Key,
					$Value->ForeignKey->Update,
					$Value->ForeignKey->Delete
				);

				$First = FALSE;
			}
		}

		return "{$String}\n)";
	}

	protected function
	GetCreateCharsetString(string $Input):
	string {

		return "CHARSET={$Input}";
	}

	protected function
	GetCreateCollateString(string $Input):
	string {

		return "COLLATE={$Input}";
	}

	protected function
	GetCreateEngineString(string $Input):
	string {

		return "ENGINE={$Input}";
	}

	protected function
	GetCreateCommentString(?string $Input):
	string {

		if(!$Input)
		return '';

		$Input = str_replace('"', '\\"', $Input);

		return "COMMENT=\"{$Input}\"";
	}

	////////////////
	////////////////

	protected function
	GenerateDeleteQuery() {
	/*//
	@return string
	generate a DELETE style query.
	//*/

		// there are some differences in syntax based on if we need multi
		// table or join searches in the delete. we consider the need for
		// alternate syntax to generate the most performant and featured
		// query possible.

		$Joins = NULL;
		$Conds = NULL;
		$Havings = NULL;
		$Limit = NULL;

		$Joins = NULL;
		$Conds = NULL;
		$Havings = NULL;
		$Limit = NULL;

		$MultiTable = FALSE;

		if(count($this->Verse->GetTables()) > 1)
		$MultiTable = TRUE;
		elseif(count($this->Verse->GetJoins()) > 0)
		$MultiTable = TRUE;

		////////

		if(!$MultiTable)
		$this->QueryString = sprintf(
			'DELETE FROM %s ',
			join(', ',$this->Verse->GetTables())
		);

		else
		$this->QueryString = sprintf(
			'DELETE %s FROM %s ',
			join(', ',array_map(
				function($Val){
					if(strpos($Val,' ') === FALSE)
					return $Val;
					else
					return trim(substr($Val,strpos($Val,' ')));
				},
				$this->Verse->GetTables()
			)),
			join(', ',$this->Verse->GetTables())
		);

		////////

		if($MultiTable)
		if($Joins = $this->Verse->GetJoins())
		$this->QueryString .= $this->GetJoinString($Joins);

		if($Conds = $this->Verse->GetConditions())
		$this->QueryString .= $this->GetConditionString($Conds);

		if($Havings = $this->Verse->GetHavings())
		$this->QueryString .= $this->GetHavingString($Havings);

		if(!$MultiTable)
		if(($Limit = $this->Verse->GetLimit()) !== 0)
		$this->QueryString .= $this->GetLimitString($Limit);

		return $this->QueryString;
	}

	protected function
	GenerateInsertQuery() {
	/*//
	@return string
	generate an INSERT style query.
	//*/

		$Table = current($this->Verse->GetTables());
		$Fields = join(',',array_keys($this->Verse->GetFields()));
		$Values = join(',',array_values($this->Verse->GetFields()));
		$Ignore = (($this->Verse->GetFlags() & Verse::InsertIgnore) === Verse::InsertIgnore);
		$Update = (($this->Verse->GetFlags() & Verse::InsertUpdate) === Verse::InsertUpdate);

		$this->QueryString = sprintf(
			'%s INTO %s (%s) VALUES (%s) ',
			((!$Ignore)?('INSERT'):('INSERT IGNORE')),
			$Table,
			$Fields,
			$Values
		);

		if($Update) $this->QueryString .= sprintf(
			'ON DUPLICATE KEY UPDATE %s ',
			preg_replace(
				'/^SET /', '',
				$this->GetSetString($this->Verse->GetFields())
			)
		);

		return $this->QueryString;
	}

	protected function
	GenerateSelectQuery() {
	/*//
	@return string
	generate a SELECT style query.
	//*/

		$Joins = NULL;
		$Conds = NULL;
		$Groups = NULL;
		$Havings = NULL;
		$Sorts = NULL;
		$Limit = NULL;
		$Offset = NULL;

		$Joins = NULL;
		$Conds = NULL;
		$Groups = NULL;
		$Havings = NULL;
		$Sorts = NULL;
		$Limit = NULL;
		$Offset = NULL;

		$this->QueryString = sprintf(
			'SELECT %s FROM %s ',
			implode(', ',$this->Verse->GetFields()),
			implode(', ',$this->Verse->GetTables())
		);

		if($Joins = $this->Verse->GetJoins())
		$this->QueryString .= $this->GetJoinString($Joins);

		if($Conds = $this->Verse->GetConditions())
		$this->QueryString .= $this->GetConditionString($Conds);

		if($Groups = $this->Verse->GetGroups())
		$this->QueryString .= $this->GetGroupString($Groups);

		if($Havings = $this->Verse->GetHavings())
		$this->QueryString .= $this->GetHavingString($Havings);

		if($Sorts = $this->Verse->GetSorts())
		$this->QueryString .= $this->GetSortString($Sorts);

		if(($Limit = $this->Verse->GetLimit()) !== 0)
		$this->QueryString .= $this->GetLimitString($Limit);

		if(($Offset = $this->Verse->GetOffset()) !== 0)
		$this->QueryString .= $this->GetOffsetString($Offset);

		return trim($this->QueryString);
	}

	protected function
	GenerateUpdateQuery() {
	/*//
	@return string
	generate an UPDATE style query.
	//*/

		$Joins = NULL;
		$Fields = NULL;
		$Conds = NULL;
		$Havings = NULL;
		$Limit = NULL;

		$Joins = NULL;
		$Fields = NULL;
		$Conds = NULL;
		$Havings = NULL;
		$Limit = NULL;

		$this->QueryString = sprintf(
			'UPDATE %s ',
			join(', ',$this->Verse->GetTables())
		);

		if($Joins = $this->Verse->GetJoins())
		$this->QueryString .= $this->GetJoinString($Joins);

		if($Fields = $this->Verse->GetFields())
		$this->QueryString .= $this->GetSetString($Fields);

		if($Conds = $this->Verse->GetConditions())
		$this->QueryString .= $this->GetConditionString($Conds);

		if($Havings = $this->Verse->GetHavings())
		$this->QueryString .= $this->GetHavingString($Havings);

		if(($Limit = $this->Verse->GetLimit()) !== 0)
		$this->QueryString .= $this->GetLimitString($Limit);

		return $this->QueryString;
	}

	protected function
	GenerateCreateQuery() {
	/*//
	@date 2021-08-24
	//*/

		$this->QueryString .= sprintf(
			"CREATE TABLE `%s` %s\n%s\n%s\n%s\n%s",
			current($this->Verse->GetTables()),
			$this->GetCreateFieldsString(
				$this->Verse->GetFields()
				?? [],
				$this->Verse->GetIndexes()
				?? [],
				$this->Verse->GetForeignKeys()
				?? []
			),
			$this->GetCreateCharsetString(
				$this->Verse->GetCharset()
				?? 'utf8mb4'
			),
			$this->GetCreateCollateString(
				$this->Verse->GetCollate()
				?? 'utf8mb4_general_ci'
			),
			$this->GetCreateEngineString(
				$this->Verse->GetEngine()
				?? 'InnoDB'
			),
			$this->GetCreateCommentString(
				$this->Verse->GetComment()
			)
		);

		return trim($this->QueryString);
	}

}
