<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# jsqlpgsql
Parser jsql para MySQL
https://hytcom.net/nogal/docs/objects/jsql.md
*/
namespace nogal;
self::call("jsql",null,true);
class nglJSQLPostgreSQL extends nglJSQL implements iNglJSQL {

	public function colAdd($aJSQL) {
		// $aColDef = $this->coldef($aJSQL["column"]);
		// return "ALTER TABLE ".$this->column($aJSQL["table"])." ADD COLUMN ".\implode(" ", $aColDef).";";
	}

	public function colDef($aColumn) {
		$sOldName = (!empty($aColumn["oldname"]) && $aColumn["oldname"]!=$aColumn["name"]) ? "`".$aColumn["oldname"]."` " : "";
		$sName = $sOldName."\"".$aColumn["name"]."\"";

		if(!empty($aColumn["autoinc"]) && self::call()->isTrue($aColumn["autoinc"])) {
			$sType = "SERIAL";
		} else {
			$sType = !empty($this->aTypes[\strtoupper($aColumn["type"])]) ? $this->aTypes[\strtoupper($aColumn["type"])] : "TEXT";
		}

		$sLength = !empty($aColumn["length"]) ? "(".$aColumn["length"].")" : "";
		$sNull = (!isset($aColumn["null"]) || \strtolower($aColumn["null"])==="false" || $aColumn["null"]===false) ? "NOT NULL" : "NULL";
		$sDefault = "";
		if(\array_key_exists("default", $aColumn) && $aColumn["default"]!="NONE" && $aColumn["default"]!="") {
			$sDefault = (\strtoupper($sDefault)=="NULL") ? "DEFAULT NULL" : "DEFAULT ".$aColumn["default"]."";
		}
		if($sNull=="NULL" && \strtoupper($sDefault)=="DEFAULT NULL") { $sNull = ""; }
		$sAfter = !empty($aColumn["after"]) ? "AFTER `".$aColumn["after"]."`" : "";
		$sComment = !empty($aColumn["comment"]) ? \addslashes($aColumn["comment"]) : "";

		return [
			"name"		=> $sName,
			"type"		=> $sType,
			"autoinc"	=> "",
			"length"	=> $sLength,
			"default"	=> $sDefault,
			"null"		=> $sNull,
			"attribs"	=> "",
			"comment"	=> $sComment,
			"after"		=> $sAfter
		];
	}

	public function colDrop($aJSQL) {
		// return "ALTER TABLE ".$this->column($aJSQL["table"])." DROP `".$aJSQL["column"]."`;";
	}

	public function colModify($aJSQL) {
		// $aColDef = $this->coldef($aJSQL["column"]);
		// return "ALTER TABLE ".$this->column($aJSQL["table"])." MODIFY COLUMN ".\implode(" ", $aColDef).";";
	}

	public function colRename($aJSQL) {
		// $aColDef = $this->coldef($aJSQL["column"]);
		// return "ALTER TABLE ".$this->column($aJSQL["table"])." CHANGE COLUMN ".\implode(" ", $aColDef).";";
	}

	public function column($mColumn, $bTableOnly=false) {
		$aColumn = (\is_array($mColumn)) ? $mColumn : [$mColumn];
		$aColumnName = \explode(".", \trim($aColumn[0]));
		$sColumn = '"'.\implode('"."', $aColumnName).'"';
		if(isset($aColumn[1])) {
			$sColumn .= $bTableOnly ? $aColumn[1] : ' "'.$aColumn[1].'"';
		}
		return $sColumn;
	}

	public function comment($aJSQL) {
		// return "ALTER TABLE ".$this->column($aJSQL["table"])." COMMENT '".\addslashes($aJSQL["comment"])."';";
	}

	public function create($aJSQL) {
		$aFields = $aIndexes = $aComments = $aTypes = [];
		if(!isset($aJSQL["table"], $aJSQL["columns"])) { return ""; }
		foreach($aJSQL["columns"] as $aField) {
			$aColDef = $this->colDef($aField);
			$sRelationName = \strtolower(\preg_replace("/[^0-9a-z_]/is", "", $aJSQL["table"]."_".$aField["name"]."_".self::call()->unique(6)));
			if($aColDef["type"]=="enum") {
				$aColDef["type"] = "owl_state";
				if(\strtolower($aField["name"])!="state") {
					$aColDef["type"] = $sRelationName;
					$aTypes[] = "CREATE TYPE ".$this->column($aColDef["type"])." AS ENUM ".$aColDef["length"];
				}
				$aColDef["length"] = "";
			}

			if(!empty($aField["index"])) {
				switch(\strtoupper($aField["index"])) {
					case "INDEX": $aIndexes[] = "CREATE INDEX ".$this->column($sRelationName)." ON ".$this->column($aJSQL["table"])." USING btree (".$this->column($aField["name"]).")"; break;
					case "UNIQUE": $aIndexes[] = "CREATE UNIQUE INDEX ".$this->column($sRelationName)." ON ".$this->column($aJSQL["table"])." USING btree (".$this->column($aField["name"]).")"; break;
					case "FULLTEXT": $aIndexes[] = "CREATE INDEX ".$this->column($sRelationName)." ON ".$this->column($aJSQL["table"])." USING gin (".$this->column($aField["name"]).")"; break;
					case "PRIMARY": $aIndexes[] = "ALTER TABLE ".$this->column($aJSQL["table"])." ADD PRIMARY KEY (".$this->column($aField["name"]).")"; break;
				}
			}

			if(!empty($aColDef["comment"])) {
				$aComments[] = "COMMENT ON COLUMN ".$this->column($aJSQL["table"].".".$aField["name"])." IS '".$aColDef["comment"]."'";
				unset($aColDef["comment"]);
			}

			$aFields[] = \implode(" ", $aColDef);
		}

		$sFields = \implode(",\n\t", $aFields);
		$sType = !empty($aJSQL["type"]) ? $aJSQL["type"]." " : "";
		if(!empty($aJSQL["comment"])) { $aComments[] = "COMMENT ON TABLE ".$this->column($aJSQL["table"])." IS '".\addslashes($aJSQL["comment"])."'"; }

		$sSQL  = (!empty($aJSQL["drop"]) && self::call()->isTrue($aJSQL["drop"])) ? "DROP TABLE IF EXISTS ".$this->column($aJSQL["table"]).";\n" : "";
		if(\count($aTypes)) { $sSQL.= \implode(";\n", $aTypes).";\n"; }
		$sSQL .= "CREATE ".$sType."TABLE ".$this->column($aJSQL["table"])." (\n";
		$sSQL .= "\t".$sFields."\n";
		$sSQL .= ");";
		if(\count($aIndexes)) { $sSQL.= "\n".\implode(";\n", $aIndexes).";"; }
		if(\count($aComments)) { $sSQL.= "\n".\implode(";\n", $aComments).";"; }
		return $sSQL;
	}

	public function datatypes() {
		return [
			"TINYINT"		=> "smallint",
			"SMALLINT"		=> "smallint",
			"MEDIUMINT"		=> "integer",
			"INT"			=> "integer",
			"BIGINT"		=> "bigint",
			"DECIMAL"		=> "decimal",
			"FLOAT"			=> "real",
			"DOUBLE"		=> "double precision",
			"CHAR"			=> "char",
			"VARCHAR"		=> "varchar",
			"TINYTEXT"		=> "text",
			"TEXT"			=> "text",
			"MEDIUMTEXT"	=> "text",
			"BIGTEXT"		=> "text",
			"JSON"			=> "text",
			"TINYBLOB" 		=> "bytea",
			"BLOB" 			=> "bytea",
			"MEDIUMBLOB" 	=> "bytea",
			"BIGBLOB"		=> "bytea",
			"ENUM"			=> "enum",
			"DATE"			=> "date",
			"TIME"			=> "time",
			"DATETIME"		=> "timestamp",
			"TIMESTAMP"		=> "timestamp",
			"YEAR"			=> "date"
		];
	}

	public function delete($aJSQL) {
		// return $this->SelDel("delete", $aJSQL);
	}

	public function drop($aJSQL) {
		// return "DROP TABLE IF EXISTS ".$this->column($aJSQL["table"]).";";
	}

	public function index($aJSQL) {
		// $sIndex = !empty($aJSQL["type"]) ? \strtoupper($aJSQL["type"]) : "";
		// return "CREATE INDEX ".$sIndex." `".$aJSQL["column"]."` ON ".$this->column($aJSQL["table"])." (`".$aJSQL["column"]."`);";
	}

	public function indexDrop($aJSQL) {
		// return "DROP INDEX `".$aJSQL["column"]."` ON ".$this->column($aJSQL["table"]).";";
	}

	public function insert($aJSQL) {
		// return $this->UpSert("insert", $aJSQL);
	}

	public function rename($aJSQL) {
		// return "RENAME TABLE ".$this->column($aJSQL["table"])." TO `".$aJSQL["newname"]."`;\n";
	}

	public function select($aJSQL) {
		return $this->SelDel("select", $aJSQL);
	}

	public function update($aJSQL) {
		// return $this->UpSert("update", $aJSQL);
	}

	private function SelDel($sType, $aJSQL) {
		$aSQL = [];

		// columns
		if($sType=="select") { $aSQL["columns"] = !empty($aJSQL["columns"]) ? \implode(", ", \array_map([$this,"column"], $aJSQL["columns"])) : "*"; }

		// tables
		if(isset($aJSQL["tables"])) {
			$sFirstTable = \array_shift($aJSQL["tables"]);
			$aFrom = [$this->column($sFirstTable, true)];
			foreach($aJSQL["tables"] as $aTable) {
				if(!\is_array($aTable) || (\is_array($aTable) && !isset($aTable[2]))) {
					$aFrom[] = ", ".$this->column($aTable, true);
				} else {
					$aFrom[] = "LEFT JOIN ".$this->column($aTable, true)." ON (".$this->where($aTable[2]).")";
				}
			}

			$aSQL["tables"] = "FROM ".\implode(" ", $aFrom);
		}

		// where
		$aSQL["where"] = (isset($aJSQL["where"])) ? "WHERE ".$this->where($aJSQL["where"]) : "";

		// group by
		if(isset($aJSQL["group"])) {
			$aGroup = [];
			foreach($aJSQL["group"] as $sField) {
				$aGroup[] = $this->column($sField);
			}
			$aSQL["group"] = "GROUP BY ".\implode(", ", $aGroup);
		}

		// having
		if(isset($aJSQL["having"])) { $aSQL["having"] = "HAVING ".$this->where($aJSQL["having"]); }

		// order by
		if(isset($aJSQL["order"])) {
			$aOrder = [];
			foreach($aJSQL["order"] as $sField) {
				if($sField==="RANDOM") { $aOrder[] = "RAND()"; break; }
				$aField = \explode(":", $sField);
				$sOrder = (\is_numeric($aField[0])) ? $aField[0] : $this->column($aField[0]);
				if(isset($aField[1])) { $sOrder .= " ".$aField[1]; }
				$aOrder[] = $sOrder;
			}
			$aSQL["order"] = "ORDER BY ".\implode(", ", $aOrder);
		}

		// limit
		if(isset($aJSQL["limit"])) {
			if(isset($aJSQL["offset"])) {
				$aSQL["limit"] = "LIMIT ".(int)$aJSQL["offset"].", ".(int)$aJSQL["limit"];
			} else {
				$aSQL["limit"] = "LIMIT ".(int)$aJSQL["limit"];
			}
		}

		// sentencia SQL
		return \strtoupper($sType)." ".\implode(" ", $aSQL);
	}


	private function UpSert($sType, $aJSQL) {
		$aSQL = [];

		// tables
		if(isset($aJSQL["tables"])) {
			$sFirstTable = \array_shift($aJSQL["tables"]);
			$aFrom = [$this->column($sFirstTable, "")];
			foreach($aJSQL["tables"] as $aTable) {
				if(!\is_array($aTable) || (\is_array($aTable) && !isset($aTable[2]))) {
					$aFrom[] = ", ".$this->column($aTable, true);
				} else {
					$aFrom[] = "LEFT JOIN ".$this->column($aTable, true)." ON (".$this->where($aTable[2]).")";
				}
			}

			if($sType=="insert") {
				$aSQL["tables"] = "INSERT INTO ".\implode(" ", $aFrom);
			} else {
				$aSQL["tables"] = "UPDATE ".\implode(" ", $aFrom);
			}
		}

		// columns
		$aJSQL["columns"] = \array_map([$this,"condition"], $aJSQL["columns"]);
		$aSQL["columns"] = "SET ".\implode(", ", $aJSQL["columns"]);

		// where
		if($sType=="update") {
			$aSQL["where"] = (isset($aJSQL["where"])) ? "WHERE ".$this->where($aJSQL["where"]) : "";
		}

		// order by
		if(isset($aJSQL["order"])) {
			$aOrder = [];
			foreach($aJSQL["order"] as $sField) {
				if($sField==="RANDOM") { $aOrder[] = "RAND()"; break; }
				$aField = \explode(":", $sField);
				$sOrder = (\is_numeric($aField[0])) ? $aField[0] : $this->column($aField[0]);
				if(isset($aField[1])) { $sOrder .= " ".$aField[1]; }
				$aOrder[] = $sOrder;
			}
			$aSQL["order"] = "ORDER BY ".\implode(", ", $aOrder);
		}

		// limit
		if(isset($aJSQL["limit"])) {
			if(isset($aJSQL["offset"])) {
				$aSQL["limit"] = "LIMIT ".(int)$aJSQL["offset"].", ".(int)$aJSQL["limit"];
			} else {
				$aSQL["limit"] = "LIMIT ".(int)$aJSQL["limit"];
			}
		}

		// sentencia SQL
		return \implode(" ", $aSQL);
	}
}

?>