<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# sqliteq
https://hytcom.net/nogal/docs/objects/sqliteq.md
*/
namespace nogal;
class nglDBSQLiteQuery extends nglBranch implements iNglDBQuery {

	private $db		= null;
	private $cursor = null;

	final protected function __declareArguments__() {
		$vArguments							= [];
		$vArguments["column"]				= ['$mValue', null];
		$vArguments["get_mode"]				= ['$this->GetMode($mValue)', \SQLITE3_ASSOC];
		$vArguments["get_group"]			= ['$mValue', null];
		$vArguments["link"]					= ['$mValue', null];
		$vArguments["query"]				= ['$mValue', null];
		$vArguments["sentence"]				= ['(string)$mValue', null];
		$vArguments["query_time"]			= ['$mValue', null];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes						= [];
		$vAttributes["_allrows"]			= null;
		$vAttributes["_columns"]			= null;
		$vAttributes["_rows"]				= null;
		$vAttributes["crud"]				= null;
		$vAttributes["sql"]					= null;
		$vAttributes["time"]				= null;

		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
	}

	public function allrows() {
		if($this->attribute("_allrows")!==null) { return $this->attribute("_allrows"); }

		$nRows = null;
		if($this->attribute("crud")=="SELECT") {
			$sSQL = $this->attribute("sql");
			$sSQL = \trim($sSQL);
			if(\preg_match("/LIMIT *[0-9]+ *,? *[0-9]*$/i", $sSQL)) {
				$sSQL = \preg_replace("/LIMIT *[0-9]+ *,? *[0-9]*$/i", "", $sSQL);

				$sSQL = "SELECT COUNT(*) FROM (".$sSQL.")";
				$getrows = $this->db->query($sSQL);
				$nRows = (int)$getrows->fetchArray(\SQLITE3_NUM)[0];
				$getrows->finalize();
			} else {
				$nRows = (int)$this->rows();
			}
		}

		$this->attribute("_allrows", $nRows);
		return $nRows;
	}

	public function columns() {
		if($this->attribute("columns")!==null) { return $this->attribute("columns"); }

		$aGetColumns = [];
		$nCols = $this->cursor->numColumns();
		if($nCols) {
			for($x=0; $x<$nCols; $x++) {
				$aGetColumns[] = $this->cursor->columnName($x);
			}
		}

		$this->attribute("columns", $aGetColumns);
		return $aGetColumns;
	}

	public function count() {
		if($this->attribute("rows")!==null) { return $this->attribute("rows"); }

		if(\in_array($this->attribute("crud"), ["INSERT", "UPDATE", "REPLACE", "DELETE"])) {
			$nRows = $this->db->changes();
		} else {
			$sSQL = $this->attribute("sql");
			$rows = $this->db->query("SELECT COUNT(*) FROM (".$sSQL.")");
			$nRows = $rows->fetchArray(\SQLITE3_NUM)[0];
		}

		$this->attribute("rows", $nRows);
		return $nRows;
	}

	public function destroy() {
		if(!\is_bool($this->cursor)) { $this->cursor->finalize(); }
		return parent::__destroy__();
	}

	public function get() {
		list($sColumn,$sMode) = $this->getarguments("column,get_mode", \func_get_args());
		$aRow = $this->cursor->fetchArray($sMode);
		if(!empty($sColumn) && $sColumn[0]=="#") { $sColumn = \substr($sColumn, 1); }
		return ($sColumn!==null && $aRow!==false && \array_key_exists($sColumn, $aRow)) ? $aRow[$sColumn] : $aRow;
	}

	public function getall() {
		list($sColumn,$sMode,$aGroup) = $this->getarguments("column,get_mode,get_group", \func_get_args());

		$bIndexMode = false;
		if(!empty($sColumn) && $sColumn[0]=="#") {
			$sColumn = \substr($sColumn, 1);
			$bIndexMode = true;
		}

		$bGroupByMode = false;
		if(!empty($sColumn) && $sColumn[0]=="@") {
			$sGroupBy = \substr($sColumn, 1);
			$sColumn = null;
			$bGroupByMode = true;
			$aGroup = (\is_array($aGroup)) ? $aGroup : null;
		}

		$this->reset();
		$aRow = $this->cursor->fetchArray($sMode);

		$aGetAll = [];
		if($sColumn!==null && $aRow!==false && !\array_key_exists($sColumn, $aRow)) { return $aGetAll; }
		$this->reset();

		if($sColumn!==null) {
			if($bIndexMode) {
				while($aRow = $this->cursor->fetchArray($sMode)) {
					if(isset($aGetAll[$aRow[$sColumn]])) {
						if(!isset($aMultiple[$aRow[$sColumn]])) {
							$aGetAll[$aRow[$sColumn]] = [$aGetAll[$aRow[$sColumn]]];
							$aMultiple[$aRow[$sColumn]] = true;
						}
						$aGetAll[$aRow[$sColumn]][] = $aRow;
					} else {
						$aGetAll[$aRow[$sColumn]] = $aRow;
					}
				}
			} else {
				while($aRow = $this->cursor->fetchArray($sMode)) {
					$aGetAll[] = $aRow[$sColumn];
				}
			}
		} else {
			while($aRow = $this->cursor->fetchArray($sMode)) {
				$aGetAll[] = $aRow;
			}
		}

		if($bGroupByMode) {
			$aGetAll = self::call()->arrayGroup($aGetAll, $aGroup);
		}

		$this->reset();
		return $aGetAll;
	}

	protected function GetMode($sMode) {
		$aModes 				= [];
		$aModes["both"] 		= \SQLITE3_BOTH;
		$aModes["num"] 			= \SQLITE3_NUM;
		$aModes["assoc"] 		= \SQLITE3_ASSOC;
		$aModes[3] 				= \SQLITE3_BOTH;
		$aModes[2] 				= \SQLITE3_NUM;
		$aModes[1] 				= \SQLITE3_ASSOC;

		$sMode = \strtolower($sMode);
		return (isset($aModes[$sMode])) ? $aModes[$sMode] : \SQLITE3_ASSOC;
	}

	public function getobj() {
		return (object)$this->cursor->fetchArray(\SQLITE3_ASSOC);
	}

	public function free() {
		if(!\is_bool($this->cursor)) { $this->cursor->finalize(); }
		return $this;
	}

	public function lastid() {
		if($this->attribute("crud")=="INSERT") {
			return $this->db->lastInsertRowID();
		} else {
			return null;
		}
	}

	public function load() {
		list($link, $query, $sQuery, $nQueryTime) = $this->getarguments("link,query,sentence,query_time", \func_get_args());

		$this->db = $link;
		$this->cursor = $query;
		$this->attribute("sql", $sQuery);
		$this->attribute("time", $nQueryTime);

		$sSQL = $sQuery;
		$sSQL = \preg_replace("/^[^A-Z]*/i", "", $sSQL);
		$sSQLCommand = \strtok($sSQL, " ");
		$sSQLCommand = \strtoupper($sSQLCommand);

		if(\in_array($sSQLCommand, ["SELECT", "INSERT", "UPDATE", "REPLACE", "DELETE"])) {
			$this->attribute("crud", $sSQLCommand);
		} else {
			$this->attribute("crud", false);
		}

		return $this;
	}

	public function reset() {
		$this->cursor->reset();
		return $this;
	}

	public function rows() {
		return $this->count();
	}

	public function toArray() {
		$this->reset();
		$aGetAll = [];
		while(($aRow = $this->cursor->fetchArray(\SQLITE3_ASSOC))!==false) {
			$aGetAll[] = $aRow;
		}
		$this->reset();
		return $aGetAll;
	}
}

?>