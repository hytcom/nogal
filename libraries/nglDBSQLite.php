<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# sqlite
https://hytcom.net/nogal/docs/objects/sqlite.md
*/
namespace nogal;
class nglDBSQLite extends nglBranch implements iNglDataBase {

	private $link;
	private $vModes;

	final protected function __declareArguments__() {
		$vArguments							= [];
		$vArguments["autoconn"]				= ['self::call()->istrue($mValue)', false];
		$vArguments["base"]					= ['$mValue', null];
		$vArguments["check_colnames"]		= ['self::call()->istrue($mValue)', true];
		$vArguments["debug"]				= ['self::call()->istrue($mValue)', false];
		$vArguments["do"]					= ['self::call()->istrue($mValue)', false];
		$vArguments["error_description"]	= ['self::call()->istrue($mValue)', false];
		$vArguments["error_query"]			= ['self::call()->istrue($mValue)', false];
		$vArguments["field"]				= ['$mValue', null];
		$vArguments["flags"]				= ['(int)$mValue', (SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE)];
		$vArguments["insert_mode"]			= ['\strtoupper($mValue)', "INSERT",["INSERT","REPLACE","IGNORE"]];
		$vArguments["pass"]					= ['$mValue', null];
		$vArguments["sql"]					= ['$mValue', null];
		$vArguments["table"]				= ['(string)$mValue', null];
		$vArguments["update_mode"]			= ['\strtoupper($mValue)', "UPDATE",["UPDATE","REPLACE","IGNORE"]];
		$vArguments["values"]				= ['$mValue', null];
		$vArguments["where"]				= ['$mValue', null];

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes						= [];
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		$vModes 			= [];
		$vModes["INSERT"] 	= "";
		$vModes["UPDATE"] 	= "";
		$vModes["REPLACE"] 	= "OR REPLACE";
		$vModes["IGNORE"] 	= "OR IGNORE";
		$this->vModes 		= $vModes;
	}

	final public function __init__() {
		if($this->autoconn) {
			$this->connect();
		}
	}

	public function close() {
		return $this->link->close();
	}

	public function connect() {
		list($sBase, $sPass, $nFlags) = $this->getarguments("base,pass,flags", \func_get_args());
		$sPass = self::passwd($sPass, true);
		$sBase = self::call()->sandboxPath($sBase);
		$this->link = new \SQLite3($sBase, $nFlags, $sPass);
		return $this;
	}

	public function destroy() {
		$this->link->close();
		return parent::__destroy__();
	}

	private function Error() {
		$sMsgError = "";
		if($this->error_description) {
			$sMsgError = $this->link->lastErrorMsg();
			if($this->error_query) {
				$sMsgError .= " -> ". $this->sql;
			}
		}

		return self::errorMessage("SQLite", $this->link->lastErrorCode(), $sMsgError);
	}

	public function describe() {
		list($sTable) = $this->getarguments("table", \func_get_args());

		$columns = $this->link->query("PRAGMA table_info(".$sTable.")");
		$aFields = [];
		while($aGetColumn = $columns->fetchArray(SQLITE3_ASSOC)) {
			$aFields[$aGetColumn["cid"]] = [
				"name" => $aGetColumn["name"],
				"type" => $aGetColumn["type"],
				"default" => $aGetColumn["dflt_value"],
				"nullable" => $aGetColumn["notnull"] ? 'NO' : 'YES',
				"index" => $aGetColumn["pk"] ? "PRIMARY KEY" : ""
			];
		}

		$indexes = $this->link->query("PRAGMA index_list(".$sTable.")");
		while($aIndex = $indexes->fetchArray(SQLITE3_ASSOC)) {
			$index = $this->link->query("PRAGMA index_info('".$aIndex["name"]."')");
			$aIndexCID = $index->fetchArray(SQLITE3_ASSOC);
			$aFields[$aIndexCID["cid"]]["index"] = $aIndex["unique"] ? "UNIQUE" : "INDEX";
		}

		return $aFields;
	}

	public function escape() {
		list($mValues) = $this->getarguments("values", \func_get_args());

		if(\is_array($mValues)) {
			$mEscapedValues = [];
			foreach($mValues as $sField => $mValue) {
				if($mValue===null) {
					$mEscapedValues[$sField] = null;
				} else if($mValue!==NGL_NULL) {
					if(\is_array($mValue)) {
						$mEscapedValues[$sField] = $this->escape($mValue);
					} else {
						$mEscapedValues[$sField] = $this->link->escapeString($mValue);
					}
				}
			}
		} else {
			if($mValues===null) {
				$mEscapedValues = null;
			} else if($mValues!==NGL_NULL) {
				$mEscapedValues = $mValues;
				if(\is_array($mEscapedValues)) {
					$mEscapedValues = $this->escape($mEscapedValues);
				} else {
					$mEscapedValues = $this->link->escapeString($mEscapedValues);
				}
			}
		}

		return $mEscapedValues;
	}

	public function exec() {
		list($sQuery) = $this->getarguments("sql", \func_get_args());
		if($this->debug) { return $sQuery; }
		if(!$query = @$this->link->query($sQuery)) {
			return $this->Error();
		}
		return $query;
	}

	public function handler() {
		return $this->link;
	}

	public function jsqlParser() {
		list($mJSQL, $sEOL) = $this->getarguments("jsql,jsql_eol", \func_get_args());
		$aJSQL = (\is_string($mJSQL)) ? self::call("jsql")->decode($mJSQL) : $mJSQL;
		$sType = (isset($aJSQL["type"])) ? \strtolower($aJSQL["type"]) : "select";

		$vSQL = [];
		$vSQL["columns"]	= "";
		$vSQL["tables"]		= "";
		$vSQL["where"]		= "";
		$vSQL["group"]		= "";
		$vSQL["having"]		= "";
		$vSQL["order"]		= "";
		$vSQL["limit"] 		= "";

		// select
		switch($sType) {
			case "select":
				$aSelect = [];
				if(isset($aJSQL["columns"])) {
					foreach($aJSQL["columns"] as $sField) {
						$aSelect[] = self::call("jsql")->column($sField);
					}
				} else {
					$aSelect[] = "*";
				}
				$vSQL["columns"] = "SELECT ".$sEOL.\implode(", ".$sEOL, $aSelect).$sEOL;
				break;

			case "insert":
			case "update":
				$aSelect = [];
				if(isset($aJSQL["columns"])) {
					$sSelect = self::call("jsql")->conditions($aJSQL["columns"], true);
				}
				$vSQL["columns"] = "SET ".$sSelect.$sEOL;
				break;

			case "where":
				return self::call("jsql")->conditions($aJSQL["where"]);
				break;
		}

		// tables
		if(isset($aJSQL["tables"])) {
			$sFirstTable = array_shift($aJSQL["tables"]);
			$aFrom = [self::call("jsql")->column($sFirstTable,"")];
			foreach($aJSQL["tables"] as $aTable) {
				if(!\is_array($aTable) || (\is_array($aTable) && !isset($aTable[2]))) {
					$aFrom[] = ", ".$sEOL.self::call("jsql")->column($aTable, "");
				} else {
					$aFrom[] = "LEFT JOIN ".self::call("jsql")->column($aTable, "")." ON (".self::call("jsql")->conditions($aTable[2]).")".$sEOL;
				}
			}

			switch($sType) {
				case "select":
					$vSQL["tables"] = "FROM ".$sEOL.\implode(" ", $aFrom);
					break;

				case "insert":
					$vSQL["tables"] = "INSERT INTO ".$sEOL.\implode(" ", $aFrom);
					break;

				case "update":
					$vSQL["tables"] = "UPDATE ".$sEOL.\implode(" ", $aFrom);
					break;
			}
		}

		// where
		$vSQL["where"] = (isset($aJSQL["where"])) ? "WHERE ".$sEOL.self::call("jsql")->conditions($aJSQL["where"]) : "";

		// group by
		if(isset($aJSQL["group"])) {
			$aGroup = [];
			foreach($aJSQL["group"] as $sField) {
				$aGroup[] = self::call("jsql")->column($sField);
			}
			$vSQL["group"] = "GROUP BY ".$sEOL.\implode(", ", $aGroup);
		}

		// having
		if(isset($aJSQL["having"])) { $vSQL["having"] = "HAVING ".$sEOL.self::call("jsql")->conditions($aJSQL["having"]); }

		// order by
		if(isset($aJSQL["order"])) {
			$aOrder = [];
			foreach($aJSQL["order"] as $sField) {
				if($sField==="RANDOM") { $aOrder[] = "RANDOM()"; break; }
				$aField = \explode(":", $sField);
				$sOrder = (\is_numeric($aField[0])) ? $aField[0] : self::call("jsql")->column($aField[0]);
				if(isset($aField[1])) { $sOrder .= " ".$aField[1]; }
				$aOrder[] = $sOrder;
			}
			$vSQL["order"] = "ORDER BY ".$sEOL.\implode(", ".$sEOL, $aOrder);
		}

		if(isset($aJSQL["limit"])) {
			if(isset($aJSQL["offset"])) {
				$vSQL["limit"] = "LIMIT ".(int)$aJSQL["offset"].", ".(int)$aJSQL["limit"];
			} else {
				$vSQL["limit"] = "LIMIT ".(int)$aJSQL["limit"];
			}
		}

		// sentencia SQL
		$sSQL = "";
		switch($sType) {
			case "select":
				$sSQL = \implode(" ", $vSQL);
				break;

			case "insert":
			case "update":
				$sSQL = $vSQL["tables"]." ".$vSQL["columns"]." ".$vSQL["where"]." ".$vSQL["order"]." ".$vSQL["limit"];
				$sSQL = trim($sSQL);
				break;
		}

		$this->sql($sSQL);
		return $sSQL;
	}

	public function mexec() {
		list($sQuery) = $this->getarguments("sql", \func_get_args());
		$aQueries = self::call()->strtoArray($sQuery, ";");
		if($this->debug) { return $aQueries; }

		$aResults = [];
		foreach($aQueries as $sQuery) {
			$sQuery = trim($sQuery);
			if(!empty($sQuery)) {
				if(!$query = @$this->link->query($sQuery)) {
					$aResults[] = $this->Error(true);
				} else {
					$aResults[] = $query;
				}
			}
		}

		return $aResults;
	}

	public function mquery() {
		list($sQuery,$bDO) = $this->getarguments("sql,do", \func_get_args());
		$sQuery = \preg_replace("/^--(.*?)$/m", "", $sQuery);
		$aQueries = self::call()->strtoArray($sQuery, ";");
		if($this->debug) { return $aQueries; }

		$aResults = [];
		foreach($aQueries as $sQuery) {
			$sQuery = trim($sQuery);
			if(!empty($sQuery)) {
				$nTimeIni = \microtime(true);
				if(!$query = @$this->link->query($sQuery)) {
					if(!$bDO) { $aResults[] = $this->Error(true); }
				} else {
					if($bDO) {
						if(\method_exists($query, "free")) { $query->free(); }
						$aResults = true;
					} else {
						$nQueryTime		= self::call("dates")->microtimer($nTimeIni);
						$sQueryName 	= "sqliteq".\strstr($this->me, ".")."_".self::call()->unique();
						$aResults[] 	= self::call($sQueryName)->load($this->link, $query, $sQuery, $nQueryTime);
					}
				}
			}
		}

		return $aResults;
	}

	public function insert() {
		list($sTable, $mValues, $sMode, $bCheckColumns) = $this->getarguments("table,values,insert_mode,check_colnames", \func_get_args());

		if(!empty($sTable)) {
			$aToInsert = $this->PrepareValues("INSERT", $sTable, $mValues, $bCheckColumns);

			if(\is_array($aToInsert) && \count($aToInsert)) {
				$sMode = \strtoupper($sMode);
				$sInsertMode = (isset($this->vModes[$sMode])) ? $this->vModes[$sMode] : "";
				$sSQL  = "INSERT ".$sInsertMode." INTO `".$sTable."` ";
				$sSQL .= "(`".\implode("`, `", \array_keys($aToInsert))."`) ";
				$sSQL .= "VALUES (".\implode(",", $aToInsert).")";
				return $this->query($sSQL);
			}
		}

		return false;
	}

	private function PrepareValues($sType, $sTable, $mValues, $bCheckColumns) {
		if(\is_array($mValues)) {
			$aValues = $mValues;
		} else if(\is_string($mValues)){
			\parse_str($mValues, $aValues);
			$aValues = $this->escape($aValues);
		} else {
			return false;
		}

		// campos validos
		$aFields = \array_keys($aValues);
		if($bCheckColumns) {
			$columns = $this->link->query("PRAGMA table_info(".$sTable.")");
			$aFields = [];
			while($aGetColumn = $columns->fetchArray(SQLITE3_ASSOC)) {
				$aFields[] = $aGetColumn["name"];
			}
			$columns->finalize();
			$columns = null;
		}

		// limpieza de campos inexistentes
		$aNewValues = [];
		if($bCheckColumns && !\count($aFields)) { return $aNewValues; }

		if(\is_array($aFields) && \count($aFields)) {
			if($sType=="INSERT") {
				foreach($aValues as $sField => $mValue) {
					if($bCheckColumns && !\in_array($sField, $aFields)) { unset($aValues[$sField]); continue; }
					$mValue = ($mValue===null) ? "NULL" : "'".$mValue."'";
					$aNewValues[$sField] = $mValue;
				}
			} else {
				foreach($aValues as $sField => $mValue) {
					if($bCheckColumns && !\in_array($sField, $aFields)) { unset($aValues[$sField]); continue; }
					$mValue = ($mValue===null) ? "NULL" : "'".$mValue."'";
					$aNewValues[] = "`".$sField."` = ".$mValue."";
				}
			}
		}

		return $aNewValues;
	}

	public function query() {
		list($sQuery,$bDO) = $this->getarguments("sql,do", \func_get_args());

		if($this->debug) { return $sQuery; }

		$nTimeIni = \microtime(true);
		if(!$query = @$this->link->query($sQuery)) {
			return $this->Error();
		}

		if($bDO) {
			if(method_exists($query, "finalize")) { $query->finalize(); }
			return true;
		}

		$nQueryTime = self::call("dates")->microtimer($nTimeIni);
		$sQueryName = "sqliteq".\strstr($this->me, ".")."_".self::call()->unique();
		return self::call($sQueryName)->load($this->link, $query, $sQuery, $nQueryTime);
	}

	public function quote() {
		list($sField) = $this->getarguments("field", \func_get_args());
		$sField = \str_replace('"','',$sField);
		return '"'.\str_replace(".",'"."',$sField).'"';
	}

	public function update() {
		list($sTable, $mValues, $sWhere, $sMode, $bCheckColumns, $bDO) = $this->getarguments("table,values,where,update_mode,check_colnames,do", \func_get_args());

		if(!empty($sTable)) {
			$aToUpdate = $this->PrepareValues("UPDATE", $sTable, $mValues, $bCheckColumns);
			if(\is_array($aToUpdate) && count($aToUpdate)) {
				$sMode = \strtoupper($sMode);
				$sUpdateMode = (isset($this->vModes[$sMode])) ? $this->vModes[$sMode] : "";
				$sSQL = "UPDATE ".$sUpdateMode." `".$sTable."` SET ".\implode(", ", $aToUpdate)." WHERE ".$sWhere;
				return $this->query($sSQL, $bDO);
			}
		}

		return false;
	}
}

?>