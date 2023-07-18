<?php

namespace Nether\Database\Verse;

use Nether\Database\Verse;
use Nether\Database\Verse\Compiler;
use Nether\Database\Meta\TableField;
use Nether\Database\Meta\FieldIndex;
use Nether\Database\Meta\MultiFieldIndex;
use Nether\Database\Meta\ForeignKey;
use Nether\Database\Meta\PrimaryKey;

use Nether\Database\Error;

class MySQL
extends Compiler {

	protected function
	GenerateDeleteQuery():
	string {
	/*//
	@implements Compiler
	@date 2022-02-17
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

		////////

		if(count($this->Verse->GetTables()) > 1)
		$MultiTable = TRUE;

		elseif(count($this->Verse->GetJoins()) > 0)
		$MultiTable = TRUE;

		////////

		if(!$MultiTable)
		$this->QueryString = sprintf(
			'DELETE FROM %s ',
			join(', ', $this->Verse->GetTables())
		);

		else
		$this->QueryString = sprintf(
			'DELETE %s FROM %s ',
			join(', ', array_map(
				function($Val){
					if(strpos($Val,' ') === FALSE)
					return $Val;

					else
					return trim(substr($Val, strpos($Val,' ')));
				},
				$this->Verse->GetTables()
			)),
			join(', ', $this->Verse->GetTables())
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

		return trim($this->QueryString);
	}

	protected function
	GenerateInsertQuery():
	string {
	/*//
	@implements Compiler
	@date 2022-02-17
	generate an INSERT style query.
	//*/

		$Table = current($this->Verse->GetTables());
		$Flags = $this->Verse->GetFlags();
		$Fields = '`'.join('`,`', array_keys($this->Verse->GetFields())).'`';
		$Values = join(',', array_values($this->Verse->GetFields()));
		$Ignore = (($Flags & Verse::InsertIgnore) === Verse::InsertIgnore);
		$Update = (($Flags & Verse::InsertUpdate) === Verse::InsertUpdate);
		$ReuseUnique = (($Flags & Verse::InsertReuseUnique) === Verse::InsertReuseUnique);
		$PrimaryKey = $this->Verse->GetPrimaryKey();

		////////

		$this->QueryString = sprintf(
			'%s INTO %s (%s) VALUES (%s) ',
			((!$Ignore) ? ('INSERT') : ('INSERT IGNORE')),
			$Table,
			$Fields,
			$Values
		);

		////////

		if($Update) {
			$FieldsToUp = $this->Verse->GetFields();

			if($ReuseUnique) {
				if(!$PrimaryKey)
				throw new Error\PrimaryKeyInvalid;

				$FieldsToUp = array_merge($FieldsToUp, [
					$this->Verse->GetPrimaryKey()
					=> "LAST_INSERT_ID(`{$PrimaryKey}`)"
				]);
			}

			$this->QueryString .= sprintf(
				'ON DUPLICATE KEY UPDATE %s ',
				preg_replace('/^SET /', '', $this->GetSetString($FieldsToUp))
			);
		}

		elseif($ReuseUnique) {
			if(!$this->Verse->GetPrimaryKey())
			throw new Error\PrimaryKeyInvalid;

			$this->QueryString .= sprintf(
				'ON DUPLICATE KEY UPDATE `%1$s`=LAST_INSERT_ID(`%1$s`) ',
				$this->Verse->GetPrimaryKey()
			);
		}

		////////

		return trim($this->QueryString);
	}

	protected function
	GenerateSelectQuery():
	string {
	/*//
	@implements Compiler
	@date 2022-02-17
	generate a SELECT style query.
	//*/

		$Flags = NULL;
		$Fields = NULL;
		$Tables = NULL;
		$Joins = NULL;
		$Conds = NULL;
		$Groups = NULL;
		$Havings = NULL;
		$Sorts = NULL;
		$Limit = NULL;
		$Offset = NULL;

		////////

		$this->QueryString = 'SELECT ';

		if($Flags = $this->Verse->GetFlags())
		$this->QueryString .= $this->GetSelectFlagString($Flags);

		if($Fields = $this->Verse->GetFields())
		$this->QueryString .= $this->GetSelectFieldString($Fields);

		if($Tables = $this->Verse->GetTables())
		$this->QueryString .= $this->GetSelectFromString($Tables);

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
	GenerateUpdateQuery():
	string {
	/*//
	@implements Compiler
	@date 2022-02-17
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
			join(', ', $this->Verse->GetTables())
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

		return trim($this->QueryString);
	}

	protected function
	GenerateCreateQuery():
	string {
	/*//
	@implements Compiler
	@date 2021-08-24
	generate a CREATE style query.
	//*/

		$CharNL = " ";
		$CharSL = "\n";

		if($this->Verse->Pretty)
		$CharNL = "\n";

		$this->QueryString = sprintf(
			"CREATE TABLE `%s` %s{$CharNL}%s{$CharNL}%s{$CharNL}%s{$CharNL}%s",
			current($this->Verse->GetTables()),
			$this->GetCreateFieldsString(
				$this->Verse->GetFields() ?? [],
				$this->Verse->GetIndexes() ?? [],
				$this->Verse->GetForeignKeys() ?? []
			),
			$this->GetCreateCharsetString(
				$this->Verse->GetCharset() ?? 'utf8mb4'
			),
			$this->GetCreateCollateString(
				$this->Verse->GetCollate() ?? 'utf8mb4_general_ci'
			),
			$this->GetCreateEngineString(
				$this->Verse->GetEngine() ?? 'InnoDB'
			),
			$this->GetCreateCommentString(
				$this->Verse->GetComment()
			)
		);

		return trim($this->QueryString);
	}

	protected function
	GenerateDropTableQuery():
	string {
	/*//
	@implements Compiler
	@date 2022-02-19
	generate a DROP TABLE style query.
	//*/

		$this->QueryString = sprintf(
			'DROP TABLE IF EXISTS `%s`',
			$this->Verse->GetTables()[0]
		);

		return rtrim($this->QueryString);
	}

	protected function
	GenerateSetVarQuery():
	string {
	/*//
	@implements Compiler
	@date 2023-01-04
	generate a SET style query that can set global/local variables.
	//*/

		// same syntax as an update set noice.

		$this->QueryString = $this->GetSetString(
			$this->Verse->GetFields()
		);

		return rtrim($this->QueryString);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GetJoinType(int $Flags):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Type = '';

		////////

		if(($Flags & Verse::JoinInner) !== 0)
		$Type .= 'INNER ';

		if(($Flags & Verse::JoinOuter) !== 0)
		$Type .= 'OUTER ';

		if(($Flags & Verse::JoinNatural) !== 0)
		$Type .= 'NATURAL ';

		if(($Flags & Verse::JoinLeft) !== 0)
		$Type .= 'LEFT ';

		if(($Flags & Verse::JoinRight) !== 0)
		$Type .= 'RIGHT ';

		////////

		return "{$Type}JOIN";
	}

	protected function
	GetWhereType(int $Flags):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Type = '';

		////////

		if($Flags & Verse::WhereAnd)
		$Type .= 'AND ';

		if($Flags & Verse::WhereOr)
		$Type .= 'OR ';

		if($Flags & Verse::WhereNot)
		$Type .= 'NOT ';

		////////

		return rtrim($Type);
	}

	protected function
	GetSortType($Flags):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Type = '';

		////////

		if($Flags & Verse::SortAsc)
		$Type = 'ASC';

		if($Flags & Verse::SortDesc)
		$Type = 'DESC';

		////////

		return $Type;
	}

	protected function
	GetConditionString(array $Conds):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Cond = NULL;
		$First = TRUE;
		$Output = 'WHERE ';

		////////

		foreach($Conds as $Cond) {
			if($First) {
				$Output .= "({$Cond->Query}) ";
				$First = FALSE;
			}

			else {
				$Output .= sprintf(
					'%s (%s) ',
					$this->GetWhereType($Cond->Flags),
					$Cond->Query
				);
			}
		}

		////////

		return $Output;
	}

	protected function
	GetHavingString(array $Conds):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Cond = NULL;
		$First = TRUE;
		$Output = 'HAVING ';

		////////

		foreach($Conds as $Cond) {
			if($First) {
				$Output .= "({$Cond->Query}) ";
				$First = FALSE;
			}

			else {
				$Output .= sprintf(
					'%s (%s) ',
					$this->GetWhereType($Cond->Flags),
					$Cond->Query
				);
			}
		}

		////////

		return $Output;
	}

	protected function
	GetJoinString(array $Joins):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Join = NULL;
		$Output = '';

		foreach($Joins as $Join) {
			$Output .= sprintf(
				'%s %s ',
				$this->GetJoinType($Join->Flags),
				$Join->Query
			);
		}

		return $Output;
	}

	protected function
	GetLimitString(int $Limit):
	string {
	/*//
	@date 2022-02-17
	//*/

		return "LIMIT {$Limit} ";
	}

	protected function
	GetOffsetString(int $Offset):
	string {
	/*//
	@date 2022-02-17
	//*/

		return "OFFSET {$Offset} ";
	}

	protected function
	GetSetString(array $Sets):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Field = NULL;
		$Value = NULL;
		$First = TRUE;
		$Output = 'SET ';

		////////

		foreach($Sets as $Field => $Value) {
			$Output .= sprintf(
				'%s`%s`=%s',
				(($First)?(''):(',')),
				$Field,
				$Value
			);

			$First = FALSE;
		}

		////////

		return "{$Output} ";
	}

	protected function
	GetSortString(array $Sorts):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Sort = NULL;
		$First = TRUE;
		$Output = 'ORDER BY ';

		////////

		foreach($Sorts as $Sort) {
			$Output .= sprintf(
				'%s%s %s',
				(($First)?(''):(', ')),
				$Sort->Query,
				$this->GetSortType($Sort->Flags)
			);

			$First = FALSE;
		}

		////////

		return "{$Output} ";
	}

	protected function
	GetGroupString(array $Groups):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Output = sprintf(
			'GROUP BY %s ',
			implode(', ',$Groups)
		);

		return $Output;
	}

	protected function
	GetSelectFlagString(int $Flags):
	string {
	/*//
	@date 2022-12-18
	//*/

		$Output = '';

		if(($Flags & Verse::SelectCalcFound) === Verse::SelectCalcFound)
		$Output .= 'SQL_CALC_FOUND_ROWS ';

		return $Output;
	}

	protected function
	GetSelectFromString(array $Tables):
	string {
	/*//
	@date 2022-12-18
	//*/

		$Output = implode(',', $Tables);

		return "FROM {$Output} ";
	}

	protected function
	GetSelectFieldString(array $Fields):
	string {
	/*//
	@date 2022-12-18
	//*/

		// the goal here is to protect fields from tripping over
		// reserved words but also allowing for having specified
		// advanced field stuff which becomes your problem.

		$Output = '';
		$Field = NULL;

		foreach($Fields as $Field) {
			if(preg_match('/^[a-zA-Z0-9]+$/', $Field))
			$Output .= "`{$Field}`,";
			else
			$Output .= "{$Field},";
		}

		$Output = trim($Output, ',');

		return "{$Output} ";
	}

	protected function
	GetCreateFieldsString(array $Sets, array $Indexes, array $ForeignKeys):
	string {
	/*//
	@date 2022-02-17
	//*/

		$Value = NULL;
		$First = TRUE;
		$Output = '';
		$CharNL = " ";
		$CharNLT = " ";

		////////

		if($this->Verse->Pretty) {
			$CharNL = "\n";
			$CharNLT = "\n\t";
		}

		////////

		// generate the list of fields on this table.

		foreach($Sets as $Value) {
			$Output .= sprintf(
				'%s%s',
				(($First)?("({$CharNLT}"):(",{$CharNLT}")),
				$Value
			);

			$First = FALSE;
		}

		// generate primary key on this table.

		foreach($Sets as $Value) {
			if($Value instanceof TableField)
			if($Value->PrimaryKey instanceof PrimaryKey) {
				$Output .= sprintf(
					'%sPRIMARY KEY (`%s`) USING BTREE',
					(($First)?("({$CharNLT}"):(",{$CharNLT}")),
					$Value->Name
				);

				$First = FALSE;
			}
		}

		// generate the list of indexes on this table.

		foreach($Indexes as $Value) {

			if($Value instanceof TableField) {
				if($Value->Index instanceof FieldIndex) {
					if($Value->Index->Unique)
					$Output .= sprintf(
						'%sUNIQUE `%s` (`%s`)',
						(($First)?("({$CharNLT}"):(",{$CharNLT}")),
						$Value->Index->Name,
						$Value->Name
					);

					else
					$Output .= sprintf(
						'%sINDEX `%s` (`%s`) USING %s',
						(($First)?("({$CharNLT}"):(",{$CharNLT}")),
						$Value->Index->Name,
						$Value->Name,
						($Value->Index->Method ?? 'BTREE')
					);

					$First = FALSE;
					continue;
				}
			}

			if($Value instanceof MultiFieldIndex) {
				if($Value->Unique)
				$Output .= sprintf(
					'%sUNIQUE `%s` (`%s`)',
					(($First)?("({$CharNLT}"):(",{$CharNLT}")),
					$Value->Name,
					join('`,`', $Value->Fields)
				);

				else
				$Output .= sprintf(
					'%sINDEX `%s` (`%s`) USING %s',
					(($First)?("({$CharNLT}"):(",{$CharNLT}")),
					$Value->Name,
					join('`,`', $Value->Fields),
					($Value->Method ?? 'BTREE')
				);

				$First = FALSE;
				continue;
			}
		}

		// generate the list of foreign keys on this table.

		foreach($ForeignKeys as $Value) {
			if($Value instanceof TableField)
			if($Value->ForeignKey instanceof ForeignKey) {
				if(!array_key_exists($Value->ForeignKey->Name,$Indexes))
				$Output .= sprintf(
					'%sINDEX `%s` (`%s`) USING BTREE',
					(($First)?("({$CharNLT}"):(",{$CharNLT}")),
					$Value->ForeignKey->Name,
					$Value->Name
				);

				$Output .= sprintf(
					'%sCONSTRAINT `%s` FOREIGN KEY(`%s`) REFERENCES `%s` (`%s`) ON UPDATE %s ON DELETE %s',
					(($First)?("({$CharNLT}"):(",{$CharNLT}")),
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

		return "{$Output}{$CharNL})";
	}

	protected function
	GetCreateCharsetString(string $Input):
	string {
	/*//
	@date 2022-02-17
	//*/

		return "CHARSET={$Input}";
	}

	protected function
	GetCreateCollateString(string $Input):
	string {
	/*//
	@date 2022-02-17
	//*/

		return "COLLATE={$Input}";
	}

	protected function
	GetCreateEngineString(string $Input):
	string {
	/*//
	@date 2022-02-17
	//*/

		return "ENGINE={$Input}";
	}

	protected function
	GetCreateCommentString(?string $Input):
	string {
	/*//
	@date 2022-02-17
	//*/

		if(!$Input)
		return '';

		$Input = str_replace('"', '\\"', $Input);

		return "COMMENT=\"{$Input}\"";
	}

}
