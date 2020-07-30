<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___
  
# mysql
## nglDBase *extends* nglBranch *implements* iNglDataBase [2020-03-30]
Gestor de conexciones con dbase

https://github.com/hytcom/wiki/blob/master/nogal/docs/dbase.md

*/
namespace nogal;

class nglDBase extends nglBranch implements iNglDataBase {

	private $link;
	private $vModes;
	private $sTable;
	private $aResult;
	private $nResult;

	final protected function __declareArguments__() {
		$vArguments							= array();
		$vArguments["autoconn"]				= array('self::call()->istrue($mValue)', false);
		$vArguments["base"]					= array('(string)$mValue', null);
		$vArguments["table"]				= array('(string)$mValue', null);
		$vArguments["get_mode"]				= array('$this->GetMode($mValue)', 3);
		$vArguments["utf8"]					= array('self::call()->istrue($mValue)', true);
		$vArguments["deleted"]				= array('self::call()->istrue($mValue)', false);
		
		
		$vArguments["mode"]					= array('(int)$mValue', 2);
		$vArguments["debug"]				= array('self::call()->istrue($mValue)', false);
		$vArguments["sql"]					= array('$mValue', null);

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes						= array();
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
		$this->aResult = null;
		$this->nResult = 0;
		if($this->argument("autoconn")) {
			$this->connect();
		}
	}

	/** FUNCTION {
		"name" : "close",
		"type" : "public",
		"description" : "Finaliza la conexión con la base de datos",
		"return": "boolean"
	} **/
	public function close() {
		return $this->link->dbase_close();
	}

	/** FUNCTION {
		"name" : "connect",
		"type" : "public",
		"description" : "Establece la conexión con la base de datos",
		"parameters" : { 
			"$sBase" : ["string", "", "argument::base"]
		},
		"return": "$this"
	} **/
	public function connect() {
		list($sBase,$nMode) = $this->getarguments("base,mode", func_get_args());
		$nMode = ((int)$nMode===0) ? 0 : 2;
		$sBase = self::call()->sandboxPath($sBase);
		$this->link = dbase_open($sBase, $nMode);
		$this->sTable = basename(strtolower($sBase), ".dbf");
		return $this;
	}

	/** FUNCTION {
		"name" : "destroy",
		"type" : "public",
		"description" : "Cierra la conexión y destruye el objeto",
		"return": "boolean"
	} **/
	public function destroy() {
		$this->link->close();
		return parent::__destroy__();
	}	

	public function query() {
		list($sQuery) = $this->getarguments("sql", func_get_args());
		$aQuery = self::call("qparser")->query($sQuery);
		if($aQuery!==false) {
			switch($aQuery[0]) {
				case "SELECT":
					$this->Select($aQuery[1]);
					break;

				case "DESCRIBE":
					return $this->Describe($aQuery[1]);
					break;
			}
		}

		return $this;
	}

	public function get() {
		list($sColumn,$nMode) = $this->getarguments("column,get_mode", func_get_args());
		if(@$aRow = $this->Fetch($nMode)) {
			if(!$this->argument("deleted")) { unset($aRow["deleted"]); }
			$this->nResult++;
			if($sColumn[0]=="#") { $sColumn = substr($sColumn, 1); }
			return ($sColumn!==null && array_key_exists($sColumn, $aRow)) ? $aRow[$sColumn] : $aRow;
		} else {
			$this->nResult = 0;
			return false;
		}
	}

	public function getall() {
		list($sColumn,$nMode) = $this->getarguments("column,get_mode", func_get_args());
		$bGroupByMode = $bIndexMode = $bKeyValue = false;

		if(is_array($sColumn)) {
			$aGroup = $sColumn;
			$sColumn = null;
			$bGroupByMode = true;
		} else {
			if($sColumn[0]=="#") {
				$sColumn = substr($sColumn, 1);
				$bIndexMode = true;
			}

			if($sColumn[0]=="@") {
				$aColumn = explode(";", substr($sColumn, 1));
				$sColumn = $aColumn[0];
				$sValue = (count($aColumn)>1) ? $aColumn[1] : $aColumn[0];
				$bKeyValue = true;
			}			
		}

		$this->nResult = 0;
		$aRow = $this->Fetch($nMode);

		$aGetAll = array();
		if($sColumn!==null && $aRow!==false && $aRow!==null && !array_key_exists($sColumn, $aRow)) { return $aGetAll; }
		$this->nResult = 0;

		if($sColumn!==null) {
			if($bIndexMode) {
				$aMultiple = array();
				while(@$aRow = $this->Fetch($nMode)) {
					if(isset($aGetAll[$aRow[$sColumn]])) {
						if(!isset($aMultiple[$aRow[$sColumn]])) {
							$aGetAll[$aRow[$sColumn]] = array($aGetAll[$aRow[$sColumn]]);
							$aMultiple[$aRow[$sColumn]] = true;
						}
						$aGetAll[$aRow[$sColumn]][] = $aRow;
					} else {
						$aGetAll[$aRow[$sColumn]] = $aRow;
					}
				}
			} else if($bKeyValue) {
				while(@$aRow = $this->Fetch($nMode)) {
					$aGetAll[$aRow[$sColumn]] = $aRow[$sValue];
				}			
			} else {
				while(@$aRow = $this->Fetch($nMode)) {
					$aGetAll[] = $aRow[$sColumn];
					
				}			
			}
		} else {
			while(@$aRow = $this->Fetch($nMode)) {
				$aGetAll[] = $aRow;
			}
		}

		if($bGroupByMode) {
			$aGetAll = self::call()->arrayGroup($aGetAll, $aGroup);
		}

		$this->reset();
		return $aGetAll;
	}

	public function allrows() {
		return dbase_numrecords($this->link);
	}

	public function reset() {
		$this->nResult = 0;
	}

	public function columns() {
		if(@$aColumns = $this->aResult[0]) {
			if(!$this->argument("deleted")) { unset($aColumns["deleted"]); }
			return array_keys($aColumns);
		}
		return array();
	}

	public function escape() {
	}

	public function exec() {
	}

	public function jsqlParser() {
	}

	public function mexec() {
	}

	public function mquery() {
	}
	
	public function insert() {
	}

	public function update() {
	}

	private function PrepareValues($sType, $sTable, $mValues, $bCheckColumns) {
	}

	private function Fetch($nMode) {
		if(@$aRow = $this->aResult[$this->nResult]) {
			if(!$this->argument("deleted")) { unset($aRow["deleted"]); }
			if($nMode===2) { $aRow = array_values($aRow); }
			if($nMode===1) { $aRow = array_merge($aRow, array_values($aRow)); }
			$this->nResult++;
			return ($this->argument("utf8")) ? array_map("utf8_encode", $aRow) : $aRow;
		} else {
			return false;
		}
	}

	private function Select($aQuery) {
		$aReturn = array();

		if($aQuery["FIELDS"][0]=="*") {
			$aFields = array_keys(dbase_get_record_with_names($this->link, 1));
			if($aFields===false) { return $aReturn; }
		} else {
			$aFields = self::call()->arrayColumn($aQuery["FIELDS"], 1);
		}

		$nFrom = $n = 1;
		$nLimit = $y = dbase_numrecords($this->link);
		if(isset($aQuery["LIMIT"])) {
			if(isset($aQuery["LIMIT"][1])) {
				$nFrom = (int)$aQuery["LIMIT"][0] + 1;
				$nLimit = (int)$aQuery["LIMIT"][1];
			} else {
				$nLimit = (int)$aQuery["LIMIT"][0] + 1;
			}
		}

		$sTable = $aQuery["FROM"][0];
		if($sTable===1) { $sTable = $this->sTable; }
		for($x=$nFrom; $x<=$y; $x++) {
			$$sTable = dbase_get_record_with_names($this->link, $x);
			if($$sTable===false) { break; }
			
			$n++;
			if(isset($aQuery["WHERE"])) {
				eval(self::call()->EvalCode("if(".$aQuery["WHERE"].") { \$bEval = true; } else { \$bEval = false; }"));
				if(!$bEval) { $n--; continue; }
			}

			foreach($aFields as $sField) {
				$aRow[$sField] = $$sTable[$sField];
			}
			
			$aReturn[] = $aRow;
			if($n==$nLimit) { break; }
		}

		$this->nResult = 0;
		return $this->aResult = $aReturn;
	}

	/** FUNCTION {
		"name" : "GetMode",
		"type" : "protected",
		"description" : "Selecciona el modo de salida para los métodos <b>get</b> y <b>getall</b>",
		"parameters" : { "$sMode" : ["mixed", "", "argument::get_mode"]},
		"return": "int"
	} **/
	protected function GetMode($sMode) {
		$aModes 				= array();
		$aModes["both"] 		= 1;
		$aModes["num"] 			= 2;
		$aModes["assoc"] 		= 3;
		$aModes[3] 				= 1;
		$aModes[2] 				= 2;
		$aModes[1]	 			= 3;

		$sMode = strtolower($sMode);
		return (isset($aModes[$sMode])) ? $aModes[$sMode] : 3;
	}

	private function Describe($aQuery) {
		return dbase_get_header_info($this->link);
	}
}

?>