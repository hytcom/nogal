<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# mysql
https://hytcom.net/nogal/docs/objects/mysql.md
*/
namespace nogal;
class nglDBMySQL extends nglBranch implements iNglDataBase {

	private $link;
	private $vModes;
	private $aQueries;

	final protected function __declareArguments__() {
		$vArguments							= [];
		$vArguments["autoconn"]				= ['self::call()->istrue($mValue)', false];
		$vArguments["base"]					= ['$mValue', "test"];
		$vArguments["charset"]				= ['$mValue', "utf8mb4"];
		$vArguments["collate"]				= ['$mValue', "utf8mb4_unicode_ci"];
		$vArguments["check_colnames"]		= ['self::call()->istrue($mValue)', true];
		$vArguments["debug"]				= ['self::call()->istrue($mValue)', false];
		$vArguments["do"]					= ['self::call()->istrue($mValue)', false];
		$vArguments["engine"]				= ['$mValue', "MyISAM"];
		$vArguments["error_description"]	= ['self::call()->istrue($mValue)', false];
		$vArguments["error_query"]			= ['self::call()->istrue($mValue)', false];
		$vArguments["field"]				= ['$mValue', null];
		$vArguments["file"]					= ['$mValue', null];
		$vArguments["file_eol"]				= ['$mValue', "\\n"];
		$vArguments["file_local"]			= ['self::call()->istrue($mValue)', true];
		$vArguments["file_separator"]		= ['$mValue', "\\t"];
		$vArguments["file_enclosed"]		= ['$mValue', '"'];
		$vArguments["host"]					= ['$mValue', "localhost"];
		$vArguments["insert_mode"]			= ['\strtoupper($mValue)', "INSERT", ["INSERT","REPLACE","IGNORE"]];
		$vArguments["pass"]					= ['$mValue', "root"];
		$vArguments["port"]					= ['(int)$mValue', null];
		$vArguments["socket"]				= ['$mValue', null];
		$vArguments["sql"]					= ['$mValue', null];
		$vArguments["table"]				= ['(string)$mValue', null];
		$vArguments["update_mode"]			= ['\strtoupper($mValue)', "UPDATE", ["UPDATE","REPLACE","IGNORE"]];
		$vArguments["user"]					= ['$mValue', "root"];
		$vArguments["values"]				= ['$mValue', null];
		$vArguments["where"]				= ['$mValue', null];

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes						= [];
		$vAttributes["last_query"]			= null;
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		$vModes 				= [];
		$vModes["INSERT"] 		= "INSERT";
		$vModes["UPDATE"] 		= "UPDATE";
		$vModes["REPLACE"] 		= "REPLACE";
		$vModes["IGNORE"] 		= "IGNORE";
		$this->vModes 			= $vModes;
		$this->aQueries			= [];
	}

	final public function __init__() {
		if($this->autoconn) {
			$this->connect();
		}
	}

	public function jsql() {
		return self::call("jsqlmysql")->db($this);
	}

	public function close() {
		return $this->link->close();
	}

	public function connect() {
		list($sHost, $sUser, $sPass, $sBase, $nPort, $sSocket) = $this->getarguments("host,user,pass,base,port,socket", \func_get_args());
		$sPass = self::passwd($sPass, true);
		$this->link = @new \mysqli($sHost, $sUser, $sPass, $sBase, $nPort, $sSocket);
		if($this->link->connect_error) {
			$this->Error();
			return false;
		}

		return $this;
	}

	public function chkgrants() {
		$grants = $this->query("SHOW GRANTS FOR CURRENT_USER");
		return ($grants->rows()) ? $grants->getall() : null;
	}

	public function describe() {
		list($sTable) = $this->getarguments("table", \func_get_args());
		$sSplitter = self::call()->unique(6);
		$bDebug = $this->debug;
		$this->debug = false;
		$describe = $this->query("
			SELECT
				`name`, `type`, `length`, `attributes`, `default`, `nullable`, `index`, `extra`, `comment`
				FROM (
					SELECT
						@type := REPLACE(REPLACE(`COLUMN_TYPE`, '(', '".$sSplitter."'), ')', '".$sSplitter."'),
						@len := ROUND((LENGTH(@type) - LENGTH(REPLACE(@type, '".$sSplitter."', '') )) / 6),
						`COLUMN_NAME` AS 'name',
						TRIM(SUBSTRING_INDEX(@type, '".$sSplitter."', 1)) AS 'type',
						IF(@len>=2, TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(@type, '".$sSplitter."', 2), '".$sSplitter."', -1)), '') AS 'length',
						IF(@len>=2, TRIM(SUBSTRING_INDEX(@type, '".$sSplitter."', -1)), '') AS 'attributes',
						`COLUMN_DEFAULT` AS 'default',
						`IS_NULLABLE` AS 'nullable',
						CASE
							WHEN `COLUMN_KEY`='PRI' THEN 'PRIMARY KEY'
							WHEN `COLUMN_KEY`='UNI' THEN 'UNIQUE'
							WHEN `COLUMN_KEY`='MUL' THEN 'INDEX'
							ELSE ''
						END AS 'index',
						`EXTRA` AS 'extra',
						`COLUMN_COMMENT` AS 'comment'
					FROM `information_schema`.`COLUMNS`
					WHERE
						`TABLE_SCHEMA` = '".$this->base."' AND
						`TABLE_NAME` = '".$sTable."'
				) info
		");
		$this->debug = $bDebug;
		return ($describe->rows()) ? $describe->getall() : null;
	}

	public function describeView() {
		list($sTable) = $this->getarguments("table", \func_get_args());
		$bDebug = $this->debug;
		$this->debug = false;
		$sName = "_tmpviewfields_".self::call()->unique(8);
		$this->query("CREATE TEMPORARY TABLE `".$sName."` SELECT * FROM `".$sObject."` ORDER BY RAND() LIMIT 30");
		$aFields = $this->describe($sName);
		$this->query("DROP TEMPORARY TABLE `".$sName."`");
		$aView = [];
		foreach($aFields as $aField) {
			$sType = \substr($aField["type"], 0, \strpos($aField["type"], ")"));
			$aType = \explode("(", $sType);
			$aView[$aField["Field"]] = [
				"name" => $aField["Field"],
				"label" => \ucfirst(\str_replace("_", " ", \strtolower($aField["name"]))),
				"type" => $aType[0],
				"length" => $aType[1]
			];
		}
		$this->debug = $bDebug;
		return $aView;
	}

	public function destroy() {
		foreach($this->aQueries as $query) {
			self::call($query)->destroy();
		}
		$this->link->close();
		return parent::__destroy__();
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
						$mEscapedValues[$sField] = $this->link->real_escape_string($mValue);
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
					$mEscapedValues = $this->link->real_escape_string($mEscapedValues);
				}
			}
		}

		return $mEscapedValues;
	}

	public function exec() {
		list($sQuery) = $this->getarguments("sql", \func_get_args());
		if(!$query = @$this->link->query($sQuery)) {
			$this->Error();
			return null;
		}
		return $query;
	}

	public function export() {
		list($sQuery,$sFilePath) = $this->getarguments("sql,file", \func_get_args());

		if($sFilePath===null) { $sFilePath = NGL_PATH_TMP."/export_".\date("YmdHis").".csv"; }
		$sFilePath = self::call()->sandboxPath($sFilePath);

		$sEnclosed	= \addslashes($this->file_enclosed);
		$sOutput = "
			INTO OUTFILE '".$sFilePath."'
			CHARACTER SET ".$this->charset."
			FIELDS TERMINATED BY '".$this->file_separator."' OPTIONALLY ENCLOSED BY '".$sEnclosed."' ESCAPED BY '\\\\\\'
			LINES TERMINATED BY '".$this->file_eol."'
			FROM
		";

		$sQuery = \preg_replace("/FROM/i", $sOutput, $sQuery, 1);
		return ($this->query($sQuery)) ? $sFilePath : false;
	}

	public function file() {
		list($sFilePath) = $this->getarguments("file", \func_get_args());
		if($sFilePath===null) { return false; }
		$sFilePath = self::call()->sandboxPath($sFilePath);
		if(!\file_exists($sFilePath)) { return false; }
		$sSQL = \file_get_contents($sFilePath);
		return $this->mexec($sSQL);
	}

	public function handler() {
		return $this->link;
	}

	public function ifexists() {
		list($sTable) = $this->getarguments("table", \func_get_args());
		$chk = $this->link->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".$this->base."' AND TABLE_NAME = '".$sTable."' LIMIT 1");
		return $chk->num_rows ? true : false;
	}

	public function import() {
		list($sFilePath,$sTable) = $this->getarguments("file,table", \func_get_args());
		if($sFilePath===null) { return false; }
		$sFilePath = self::call()->sandboxPath($sFilePath);
		if(!\file_exists($sFilePath)) { return false; }

		$sEnclosed	= \addslashes($this->file_enclosed);
		$sLocal		= ($this->file_local==true) ? "LOCAL" : "";
		$sSeparator	= $this->file_separator;

		$nChk = $this->query("SHOW TABLES LIKE '".$sTable."'")->rows();
		if(!$nChk) {
			if(($fp=@\fopen($sFilePath, "r"))!==FALSE) {
				if(\strlen($sSeparator)>1) { $sSeparator = self::call()->unescape($sSeparator); }
				while(($aColumns = \fgetcsv($fp, 5000, $sSeparator))!==FALSE) {
					$aColumns; break;
				}
				\fclose($fp);

				$aColsChecker = [];
				foreach($aColumns as &$sColumn) {
					$sColumn = self::call()->secureName($sColumn);
					if(isset($aColsChecker[$sColumn]) || empty($sColumn)) { $sColumn .= "_".self::call()->unique(); }
					$aColsChecker[$sColumn] = true;
				}
				$sCreate = "CREATE TABLE `".$sTable."` (`".implode("` TEXT NULL, `", $aColumns)."` TEXT NULL) ENGINE=".$this->engine." DEFAULT CHARSET=".$this->charset.";";

				if($this->query($sCreate)===null) { return false; }
			}
		}

		$sInput = "
			LOAD DATA ".$sLocal." INFILE '".$sFilePath."'
			INTO TABLE `".$sTable."`
			CHARACTER SET ".$this->charset."
			FIELDS TERMINATED BY '".$sSeparator."' OPTIONALLY ENCLOSED BY '".$sEnclosed."' ESCAPED BY '\\\\'
			LINES TERMINATED BY '".$this->file_eol."'
		";

		$bLoad = ($this->query($sInput)===null) ? false : true;
		if($bLoad===true && !$nChk) { $this->query("DELETE FROM `".$sTable."` LIMIT 1"); }
		return $bLoad;
	}

	public function insert() {
		list($sTable, $mValues, $sMode, $bCheckColumns, $bDO) = $this->getarguments("table,values,insert_mode,check_colnames,do", \func_get_args());
		if(!empty($sTable)) {
			$aToInsert = $this->PrepareValues("INSERT", $sTable, $mValues, $bCheckColumns);
			if(\is_array($aToInsert) && \count($aToInsert)) {
				$sMode = \strtoupper($sMode);
				$sInsertMode = (isset($this->vModes[$sMode])) ? $this->vModes[$sMode] : "INSERT";
				if($sInsertMode=="IGNORE") { $sInsertMode = "INSERT IGNORE"; }
				$sSQL  = $sInsertMode." INTO `".$sTable."` ";
				$sSQL .= "(`".\implode("`, `", \array_keys($aToInsert))."`) ";
				$sSQL .= "VALUES (".\implode(",", $aToInsert).")";
				return $this->query($sSQL, $bDO);
			}
		}

		return null;
	}

	public function replace() {
		list($sTable, $mValues, $bCheckColumns, $bDO) = $this->getarguments("table,values,check_colnames,do", \func_get_args());
		if(!empty($sTable)) {
			$aToInsert = $this->PrepareValues("INSERT", $sTable, $mValues, $bCheckColumns);
			if(\is_array($aToInsert) && \count($aToInsert)) {
				$sSQL  = "REPLACE INTO `".$sTable."` ";
				$sSQL .= "(`".\implode("`, `", \array_keys($aToInsert))."`) ";
				$sSQL .= "VALUES (".\implode(",", $aToInsert).")";
				return $this->query($sSQL, $bDO);
			}
		}

		return null;
	}

	public function mexec() {
		list($sQuery) = $this->getarguments("sql", \func_get_args());
		$sQuery = \preg_replace(array("/^--.*$/m", "/^\/\*(.*?)\*\//m"), "", $sQuery);
		if(empty($sQuery)) { return []; }
		$aQueries = self::call()->strToArray($sQuery, ";");
		if($this->debug) { return $aQueries; }

		$aResults = [];
		if(\count($aQueries)) {
			foreach($aQueries as $sQuery) {
				$sQuery = \trim($sQuery);
				if(!empty($sQuery)) {
					if(!$query = @$this->link->query($sQuery)) {
						$aResults[] = $this->Error();
					} else {
						$aResults[] = $query;
					}
				}
			}
		}

		return $aResults;
	}

	public function mquery() {
		list($sQuery) = $this->getarguments("sql", \func_get_args());
		$sQuery = \preg_replace(["/^--.*$/m", "/^\/\*(.*?)\*\//m"], "", $sQuery);
		if(empty($sQuery)) { return []; }
		$aQueries = self::call()->strToArray($sQuery, ";");
		if($this->debug) { return \implode(PHP_EOL, $aQueries); }

		$aErrors = [];
		if(\count($aQueries)) {
			foreach($aQueries as $sQuery) {
				if(!$query = $this->query($sQuery, true)) {
					$aErrors[] = $this->Error();
				}
			}
		}

		return (\count($aErrors)) ? $aErrors : true;
	}

	public function pkey() {
		list($sTable) = $this->getarguments("table", \func_get_args());
		$bDebug = $this->debug;
		$this->debug = false;
		$pk = $this->query("
			SELECT
				k.column_name
			FROM
				INFORMATION_SCHEMA.table_constraints t
				JOIN INFORMATION_SCHEMA.key_column_usage k ON (
					k.constraint_name = t.constraint_name AND
					k.constraint_schema = t.constraint_schema AND
					k.table_name = t.table_name
				)
			WHERE
				t.constraint_schema = '".$this->base."' AND
				k.table_name = '".$sTable."' AND
				t.constraint_type = 'PRIMARY KEY'
		");
		$this->debug = $bDebug;
		return $pk->rows() ? $pk->get("column_name") : null;
	}

	public function query() {
		list($sQuery,$bDO) = $this->getarguments("sql,do", \func_get_args());
		if($this->debug) { return $sQuery; }

		// juego de caracteres
		$this->link->query("SET NAMES ".$this->charset);

		$sQuery = \trim($sQuery);
		if(empty($sQuery)) { return null; }

		$nTimeIni = \microtime(true);
		$this->attribute("last_query", $sQuery);
		if(!$query = $this->link->query($sQuery)) {
			return $this->Error();
		}

		if($bDO) {
			if(\method_exists($query, "free")) { $query->free(); }
			return true;
		}

		$nQueryTime = self::call("dates")->microtimer($nTimeIni);
		$sQueryName = "mysqlq".\strstr($this->me, ".")."_".self::call()->unique();
		$this->aQueries[] = $sQueryName;
		return self::call($sQueryName)->load($this->link, $query, $sQuery, $nQueryTime);
	}

	public function quote() {
		list($sField) = $this->getarguments("field", \func_get_args());
		$sField = \str_replace("`","",$sField);
		return "`".\str_replace(".","`.`",$sField)."`";
	}

	public function tables() {
		list($sTable) = $this->getarguments("where", \func_get_args());
		$bDebug = $this->debug;
		$this->debug = false;
		$tables = $this->query("
			SELECT TABLE_NAME \"name\"
			FROM INFORMATION_SCHEMA.TABLES
			WHERE
				TABLE_SCHEMA = '".$this->base."' AND
				TABLE_NAME LIKE '%".$sTable."%'
			ORDER BY 1
		");
		$this->debug = $bDebug;

		return ($tables->rows()) ? $tables->getall() : [];
	}

	public function update() {
		list($sTable, $mValues, $sWhere, $sMode, $bCheckColumns, $bDO) = $this->getarguments("table,values,where,update_mode,check_colnames,do", \func_get_args());

		if(!empty($sTable)) {
			$aToUpdate = $this->PrepareValues("UPDATE", $sTable, $mValues, $bCheckColumns);
			if(\is_array($aToUpdate) && \count($aToUpdate)) {
				$sMode = \strtoupper($sMode);
				$sUpdateMode = (isset($this->vModes[$sMode])) ? $this->vModes[$sMode] : "";
				$sSQL = $sUpdateMode." `".$sTable."` SET ".\implode(", ", $aToUpdate)." WHERE ".$sWhere;
				return $this->query($sSQL, $bDO);
			}
		}

		return null;
	}

	private function Error() {
		$sMsgError = "";
		if(!$this->link->connect_error && $this->error_description) {
			$sMsgError = $this->link->error;
		} else if($this->link->connect_error && $this->error_description) {
			$sMsgError = "unknown database ".$this->base;
		}

		if(!$this->link->connect_error && $this->error_query) {
			$sMsgError .= " -> ". $this->attribute("last_query");
		}

		$nError = ($this->link->connect_error) ? 1049 : $this->link->errno;
		return self::errorMessage("MySQL", $nError, $sMsgError);
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
			$columns = $this->link->query("DESCRIBE `".$sTable."`");
			$aFields = [];
			while($aGetColumn = $columns->fetch_array(MYSQLI_ASSOC)) {
				$aFields[] = $aGetColumn["Field"];
			}
			$columns->free();
			$columns = null;
			unset($columns);
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
}

?>