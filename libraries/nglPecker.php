<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___
  
# pecker
## nglPecker *extends* nglBranch [2018-08-15]
Operaciones con datos

https://github.com/hytcom/wiki/blob/master/nogal/docs/pecker.md

1001 = objeto de base de datos indefinido
1002 = 
1003 = tabla indefinida
1004 = grouper indefinido
1005 = columna indefinida
1006 = tabla inexistente
1007 = hash indefinido
1008 = campo key inexistente
1009 = la tabla para analizar esta vacía
1010 = tabla cruzada indefinida
1011 = datos de la tabla de caracteristicas indefinidos/erroneos/incompletos
1012 = falta analisis de la tabla

*/
namespace nogal;

class nglPecker extends nglBranch implements inglBranch {

	private $db;
	private $sSaveId;
	private $aSavedData;

	final protected function __declareArguments__() {
		$vArguments							= array();
		$vArguments["analyse_datatype"]		= array('self::call()->isTrue($mValue)', false);
		$vArguments["col"]					= array('(int)$mValue', null);
		$vArguments["cols"]					= array('(array)$mValue', null);
		$vArguments["datafile"]				= array('$this->SetDataFile($mValue)', null);
		$vArguments["db"]					= array('$this->SetDb($mValue)', null);
		$vArguments["exec"]					= array('$mValue', false);
		$vArguments["features"]				= array('$this->SetFeatures($mValue)', null);
		$vArguments["fields"]				= array('$this->SetFields($mValue)', null);
		$vArguments["file"]					= array('$mValue', null);
		$vArguments["force"]				= array('self::call()->isTrue($mValue)', false);
		$vArguments["grouper"]				= array('$this->SetGrouper($mValue)', null);
		$vArguments["hashappend"]			= array('self::call()->isTrue($mValue)', false);
		$vArguments["hittest"]				= array('$mValue', false); // test | show | true
		$vArguments["id"]					= array('$mValue', null);
		$vArguments["inverse"]				= array('self::call()->isTrue($mValue)', false);
		$vArguments["key"]					= array('$this->SecureName($mValue)', null);
		$vArguments["limit"]				= array('$mValue', 20);
		$vArguments["markas"]				= array('$mValue', "1");
		$vArguments["markon"]				= array('$mValue', "pecked");
		$vArguments["newnames"]				= array('(array)$mValue', null);
		$vArguments["output"]				= array('strtolower($mValue)', "print"); // print | table | data
		$vArguments["overwrite"]			= array('self::call()->isTrue($mValue)', true);
		$vArguments["policy"]				= array('(array)$mValue', null);
		$vArguments["rules"]				= array('(array)$mValue', null);
		$vArguments["skip"]					= array('self::call()->isTrue($mValue)', true);
		$vArguments["splitter"]				= array('$mValue', "\t");
		$vArguments["table"]				= array('$this->SetTable($mValue)', null);
		$vArguments["truncate"]				= array('self::call()->isTrue($mValue)', false);
		$vArguments["where"]				= array('$mValue', null);
		$vArguments["xtable"]				= array('$this->SecureName($mValue)', null);
		return $vArguments;

	}

	final protected function __declareAttributes__() {
		$vAttributes						= array();
		$vAttributes["analysis"]			= null;
		$vAttributes["grouper_str"]			= null;
		$vAttributes["fields_str"]			= null;
		$vAttributes["features_schema"]		= null;
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
		$this->__errorMode__("die");
	}

	// analiza la tabla y sugiere el mejor tipo de dato para cada columna
	public function analyse() {
		list($sTable,$bForce) = $this->getarguments("table,force", func_get_args());
		$this->ChkSource();
		if(isset($this->aSavedData["ANALYSIS"], $this->aSavedData["ANALYSIS"][$sTable]) && !$bForce) {
			$this->attribute("analysis", $this->aSavedData["ANALYSIS"][$sTable]);
			return $this->Output($this->attribute("analysis"));
		} else {
			if(!isset($this->aSavedData["ANALYSIS"])) { $this->aSavedData["ANALYSIS"] = array(); }
			return $this->Output($this->BuildAnalysis($sTable));
		}
	}
	
	public function backup() {
		$sTable = $this->ChkSource();
		$sCreate = $this->db->query("SHOW CREATE TABLE `".$sTable."`")->get("Create Table");
		$sDate = date("YmdHi");
		$sCreate = str_replace("TABLE `".$sTable."` (", "TABLE `".$sTable."_".$sDate."` (", $sCreate);
		$this->db->query($sCreate);
		$insert = $this->db->query("INSERT INTO `".$sTable."_".$sDate."` SELECT * FROM `".$sTable."`");
		$this->Output(array(array("table"=> $sTable."_".$sDate, "rows"=>$insert->rows())));
	}

	public function colsid() {
		list($sXTable) = $this->getarguments("xtable", func_get_args());
		$aAnalysis = $this->ChkAnalysis();
		$aCols = array();
		
		if($sXTable!==null && isset($this->aSavedData["ANALYSIS"][$sXTable])) {
			$aXAnalysis = $this->ChkAnalysis($sXTable);
			$n = (count($aXAnalysis) > count($aAnalysis)) ? count($aXAnalysis) : count($aAnalysis);
			for($x=0; $x<$n; $x++) {
				$aCol = array("col"=>$x);
				$aCol["field"] = (isset($aAnalysis[$x])) ? $this->GetCols($x) : "";
				$aCol["x_col"] = $x;
				$aCol["x_field"] = (isset($aXAnalysis[$x])) ? $this->GetCols($x, $sXTable) : "";
				$aCols[] = $aCol;
			}
		} else {
			foreach($aAnalysis as $aRow) {
				$aCols[] = array("col"=>$aRow["col"], "field"=>$aRow["field"]);
			}
		}

		return $this->Output($aCols);
	}

	public function getgrouper() {
		return $this->Output($this->attribute("grouper_str"));
	}

	// crea la columa __pecker__
	public function hash() {
		list($sTable,$aGrouper,$aPolicy) = $this->getarguments("table,grouper,policy", func_get_args());

		$sGrouper = $this->attribute("grouper_str");
		if(!$sGrouper) { self::errorMessage($this->object, 1004); }

		$chk = $this->db->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$this->db->base."' AND `TABLE_NAME`='".$sTable."' AND `COLUMN_NAME`='__pecker__'");
		if(!$chk->rows()) {
			$this->db->query("ALTER TABLE `".$sTable."` ADD COLUMN `__wano__` TINYINT(1) NULL DEFAULT NULL FIRST, ADD INDEX `__wano__` (`__wano__`)");
			$this->db->query("ALTER TABLE `".$sTable."` ADD COLUMN `__pecked__` INT NULL DEFAULT NULL FIRST, ADD INDEX `__pecked__` (`__pecked__`)");
			$this->db->query("ALTER TABLE `".$sTable."` ADD COLUMN `__pecker__` CHAR(32) NULL DEFAULT NULL FIRST, ADD INDEX `__pecker__` (`__pecker__`)");
		}

		$sToHash = (strstr($sGrouper, ",")) ? "CONCAT(".$sGrouper.")" : $sGrouper;
		if(is_array($aPolicy)) {
			$sToHash = $this->Sanitizer($sToHash, $aPolicy);
		}

		if($this->argument("hashappend")) {
			$hashing = $this->db->query("UPDATE `".$sTable."` SET `__pecker__` = IF(LENGTH(".$sToHash.")>0, MD5(CONCAT(`__pecker__`, ".$sToHash.")), `__pecker__`) WHERE `__pecked__` IS NOT NULL");
		} else {
			$sWhere = ($this->argument("skip")) ? "WHERE `__pecked__` IS NULL" : "";
			$hashing = $this->db->query("UPDATE `".$sTable."` SET `__pecker__` = IF(LENGTH(".$sToHash.")>0, MD5(".$sToHash."), NULL) ".$sWhere);
		}
		$this->ClearAnalysis();
		$this->BuildAnalysis($sTable);
		return $this->Output(array(array("affected"=>$hashing->rows())));
	}

	// resetea la columna __pecker__
	public function reset() {
		list($sTable,$sWhere) = $this->getarguments("table,where", func_get_args());
		$this->ChkSource();
		if($sWhere===null) { $sWhere = " 1 "; } 
		if($this->ChkHash(null, false)) { $this->db->query("UPDATE `".$sTable."` SET `__pecker__` = NULL WHERE ".$sWhere); }
		return $this;
	}

	// resetea las columnas __pecker__, __pecked__ y __wano__
	public function resetall() {
		list($sTable,$sWhere) = $this->getarguments("table,where", func_get_args());
		$this->ChkSource();
		if($sWhere===null) { $sWhere = " 1 "; } 
		if($this->ChkHash(null, false)) { $this->db->query("UPDATE `".$sTable."` SET `__pecker__` = NULL, `__pecked__` = NULL, `__wano__` = NULL WHERE ".$sWhere); }
		return $this;
	}

	// elimina la columna __pecker__
	public function unhash() {
		list($sTable) = $this->getarguments("table", func_get_args());
		$this->ChkSource();
		$this->ChkHash();
		$drop = $this->db->query("ALTER TABLE `".$sTable."` DROP COLUMN `__pecker__`,  DROP COLUMN `__pecked__`,  DROP COLUMN `__wano__`");
		return $this;
	}

	// resumen de registros duplicados
	public function duplicates() {
		list($sTable) = $this->getarguments("table", func_get_args());
		$this->ChkSource();
		$this->ChkHash();
		$this->ChkKey();
		$all = $this->db->query("SELECT COUNT(*) 'all' FROM `".$sTable."`");
		$aDuplicates = $all->get();
		$duplicates = $this->db->query("SELECT COUNT(DISTINCT `__pecker__`) 'uniques' FROM `".$sTable."` WHERE `__pecker__` IS NOT NULL");
		$aDuplicates["uniques"] = $duplicates->get("uniques");
		$duplicate = $this->db->query("SELECT `__pecker__` FROM `".$sTable."` WHERE `__pecker__` IS NOT NULL GROUP BY `__pecker__` HAVING COUNT(*) > 1");
		$aDuplicates["duplicates"] = $duplicate->rows();
		return $this->Output($aDuplicates);
	}

	// muestra los duplicados
	public function twins() {
		list($sLimit) = $this->getarguments("limit", func_get_args());
		$sTable = $this->ChkSource();
		$this->ChkHash();
		$sKey = ($this->argument("key")!==null) ? "`".$this->argument("key")."`, " : "";
		$sFields = ($this->attribute("fields_str")!==null) ? $this->attribute("fields_str") : "`".$sTable."`.* ";
		$show = $this->db->query("
			SELECT 
				COUNT(`__pecker__`) AS '__duplicates__', 
				".$sKey." ".$sFields." 
			FROM `".$sTable."` 
			WHERE `__pecker__` IS NOT NULL 
			GROUP BY `__pecker__` 
				HAVING COUNT(*) > 1 
			ORDER BY `__duplicates__` DESC, `__pecker__` 
			LIMIT ".$sLimit
		);
		return $this->Output($show->getall());
	}

	// muestra todos los duplicados
	public function twinsall() {
		list($sLimit) = $this->getarguments("limit", func_get_args());
		$sTable = $this->ChkSource();
		$this->ChkHash();
		$sKey = ($this->argument("key")!==null) ? "`".$this->argument("key")."`, " : "";
		$sFields = ($this->attribute("fields_str")!==null) ? $this->attribute("fields_str") : "`".$sTable."`.* ";
		$show = $this->db->query("
			SELECT 
				".$sKey." ".$sFields." 
			FROM `".$sTable."` 
				WHERE `__pecker__` IS NOT NULL AND `__pecker__` IN (SELECT `__pecker__` FROM `".$sTable."` WHERE `__pecker__` IS NOT NULL GROUP BY `__pecker__` HAVING COUNT(*) > 1) 
			ORDER BY `__pecker__` 
			LIMIT ".$sLimit
		);
		return $this->Output($show->getall());
	}

	// muestra los registros donde key = id
	public function show() {
		list($sId) = $this->getarguments("id", func_get_args());
		$sTable = $this->ChkSource();
		$sKey = $this->ChkKey();
		$this->ChkHash();
		$sFields = ($this->attribute("fields_str")!==null) ? $this->attribute("fields_str") : "`".$sTable."`.* ";
		$sId = $this->db->escape($sId);
		$show = $this->db->query("
			SELECT 
				`".$sKey."`, ".$sFields." 
			FROM `".$sTable."` 
			WHERE `".$sKey."` = '".$sId."'
		");
		return $this->Output($show->getall());
	}

	public function unify() {
		list($aUnify) = $this->getarguments("rules", func_get_args());
		$sTable = $this->ChkSource();
		$this->ChkHash();
		$aAnalysis = $this->attribute("analysis");

		$aFields = $aRules = array();
		foreach($aUnify as $nCol => $sRule) {
			$aFields[] = $aAnalysis[$nCol]["field"];
			$aRules[$aAnalysis[$nCol]["field"]] = $sRule;
		}
		$sFields = "`".implode("`,`", $aFields)."`";

		$unify = $this->db->query("
			SELECT `__pecker__`, ".$sFields." 
			FROM `".$sTable."` 
				WHERE `__pecker__` IN (
					SELECT `__pecker__` 
					FROM `".$sTable."` 
						WHERE `__pecker__` IS NOT NULL 
					GROUP BY `__pecker__`
						HAVING COUNT(*) > 1
			) ORDER BY 1
		");

		if($unify->rows()) {
			$sCurrent = null;
			$aUnify = array();
			while($aRow = $unify->get()) {
				if($sCurrent===null) { $sCurrent = $aRow["__pecker__"]; }
				if($sCurrent!=$aRow["__pecker__"]) {
					$aMixed = $this->Mixer($aUnify, $aFields, $aRules);
					$sCurrent = $aMixed["__pecker__"];
					unset($aMixed["id"], $aMixed["__pecker__"]);
					$this->db->update($sTable, $aMixed, "`__pecker__`='".$sCurrent."'");
					$sCurrent = $aRow["__pecker__"];
					$aUnify = array();
				}
				$aUnify[] = $aRow;
			}

			$aMixed = $this->Mixer($aUnify, $aFields, $aRules);
			$sCurrent = $aMixed["__pecker__"];
			unset($aMixed["id"], $aMixed["__pecker__"]);
			$this->db->update($sTable, $aMixed, "`__pecker__`='".$sCurrent."'");
		}

		return $this->Output(array(array("unified"=>$unify->rows())));
	}

	// marca los registros de la tabla principal con el campo key de la tabla secundaria,
	// donde los campos __pecker__ de ambas tablas sean iguales
	public function hit() {
		list($sXTable,$sHitTest,$sLimit,$aCols) = $this->getarguments("xtable,hittest,limit,cols", func_get_args());
		$sHitTest = trim(strtolower($sHitTest));
		$sTable = $this->ChkSource();
		$this->ChkHash();
		$this->ChkHash($sXTable);

		if($sHitTest==="test") {
			$nRows = $this->db->query("SELECT COUNT(*) AS 'count' FROM `".$sTable."`")->get("count");
			$test = $this->db->query("
				SELECT COUNT(DISTINCT `".$sTable."`.`__pecker__`) AS 'hits', '".$nRows."' AS 'count'  
				FROM `".$sTable."`, `".$sXTable."` 
				WHERE 
					`".$sTable."`.`__pecker__` IS NOT NULL AND 
					`".$sTable."`.`__pecked__` IS NULL AND 
					`".$sTable."`.`__pecker__` = `".$sXTable."`.`__pecker__`
			");
			return $this->Output($test->getall());
		} else if($sHitTest==="show") {
			if(!isset($this->aSavedData["GROUPER"][$sTable], $this->aSavedData["GROUPER"][$sXTable])) { self::errorMessage($this->object, 1004); }
			$aFields = array();
			foreach($this->aSavedData["GROUPER"][$sTable] as $sField) {
				$aFields[] = "`".$sTable."`.`".$sField."`";
			}
			foreach($this->aSavedData["GROUPER"][$sXTable] as $sField) {
				$aFields[] = "`".$sXTable."`.`".$sField."` AS 'x_".$sField."'";
			}

			if(is_array($aCols)) {
				$this->ChkAnalysis();
				$this->ChkAnalysis($sXTable);

				$aFields[] = "'' AS '.'";
				foreach($aCols as $nCol => $nXCol) {
					$aFields[] = "`".$sTable."`.`".$this->GetCols($nCol, $sTable)."`";
					$aFields[] = "`".$sXTable."`.`".$this->GetCols($nXCol, $sXTable)."` AS 'x_".$this->GetCols($nXCol, $sXTable)."'";
				}
			}

			$test = $this->db->query("
				SELECT 
					".implode(", ", $aFields)." 
				FROM `".$sTable."`, `".$sXTable."` 
				WHERE 
					`".$sTable."`.`__pecked__` IS NULL AND 
					`".$sTable."`.`__pecker__` IS NOT NULL AND 
					`".$sTable."`.`__pecker__` = `".$sXTable."`.`__pecker__` 
					ORDER BY RAND() LIMIT ".$sLimit
			);
			return $this->Output($test->getall());
		} else {
			$sKey = $this->argument("key");
			$pecked = $this->db->query("
				UPDATE `".$sTable."`, `".$sXTable."` 
					SET `".$sTable."`.`__pecked__` = `".$sXTable."`.`".$sKey."` 
					WHERE 
						`".$sTable."`.`__pecked__` IS NULL AND 
						`".$sTable."`.`__pecker__` IS NOT NULL AND 
						`".$sTable."`.`__pecker__` = `".$sXTable."`.`__pecker__`
			");
			return $this->Output(array(array("affected"=>$pecked->rows())));
		}
	}

	public function mark() {
		list($sWhere,$mMark,$sType) = $this->getarguments("where,markas,markon", func_get_args());
		$sTable = $this->ChkSource();

		$chk = $this->db->query("SELECT COUNT(*) AS 'chk' FROM `".$sTable."` WHERE ".$sWhere);
		if($chk->get("chk")) {
			$sType = (strtolower($sType)=="pecked") ? "__pecked__" : "__wano__";
			$sMark = ($mMark===null) ? "NULL" : 1;
			$pecked = $this->db->query("UPDATE `".$sTable."` SET `".$sType."` = ".$sMark." WHERE ".$sWhere);
			return $this->Output(array(array("affected"=>$pecked->rows())));
		}
		return $this->Output(array(array("message"=>"empty result")));
	}

	public function loadfile() {
		list($sFileName,$bTruncate) = $this->getarguments("file,truncate", func_get_args());
		$sFileName = self::call()->sandboxPath($sFileName);
		if(file_exists($sFileName)) {
			$sLoadFile = $sFileName;
			$sType = strtolower(pathinfo($sFileName, PATHINFO_EXTENSION));
			
			$sSplitter = $this->argument("splitter");
			if($sType=="xls" || $sType=="xlsx") {
				$sLoadFile = "/tmp/".self::call()->unique(8).".csv";
				self::call("excel")->load($sFileName)->csv_splitter($sSplitter)->write($sLoadFile);
			}
			chmod($sLoadFile, 0777);
			
			$sTable = $this->argument("table");
			if($bTruncate) {
				if($this->IfTableExist($sTable)) { $this->db->query("TRUNCATE TABLE `".$sTable."`"); }
			}
			$this->db->import($sLoadFile, $sTable);
		}

		return $this;
	}

	public function clear() {
		list($bRun) = $this->getarguments("exec", func_get_args());
		$sTable = $this->ChkSource();
		$this->ChkHash();
		$nClean = 0;
		$clear = $this->db->query("SELECT `__pecker__`, (COUNT(*)-1) AS `count` FROM `".$sTable."` WHERE `__pecker__` IS NOT NULL GROUP BY `__pecker__` HAVING COUNT(*) > 1");
		if($clear->rows()) {
			while($aClear = $clear->get()) {
				$nClean += (int)$aClear["count"];
				if($bRun) { $this->db->query("DELETE FROM `".$sTable."` WHERE `__pecker__` = '".$aClear["__pecker__"]."' LIMIT ".$aClear["count"]); }
			}
		}

		$sTitle = ($bRun) ? "cleaned" : "to clean";
		return $this->Output(array(array($sTitle=>$nClean)));
	}

	public function clearall() {
		$this->ChkHash();
		$clear = $this->db->query("SELECT `__pecker__`, (COUNT(*)-1) AS `count` FROM `".$this->sTable."` WHERE `__pecker__` IS NOT NULL GROUP BY `__pecker__` HAVING COUNT(*) > 1");
		if($clear->rows()) {
			$this->db->query("DELETE FROM `".$this->sTable."` WHERE `__pecker__` IN (SELECT `__pecker__` FROM `".$this->sTable."` WHERE `__pecker__` IS NOT NULL GROUP BY `__pecker__` HAVING COUNT(*) > 1)");
		}
		return $this;
	}

	public function clearwano() {
		list($sField) = $this->getarguments("field", func_get_args());
		$sTable = $this->ChkSource();
		$this->ChkHash();
		$blank = $this->db->query("DELETE FROM `".$sTable."` WHERE `__wano__` = '1'");
		return $this->Output(array(array("affected"=>$blank->rows())));
	}

	public function rename() {
		list($aRename) = $this->getarguments("newnames", func_get_args());
		$sTable = $this->ChkSource();
		$aAnalysis = $this->ChkAnalysis();

		$aRen = array();
		foreach($aRename as $nCol => $sNewName) {
			$aRen[] = "RENAME COLUMN `".$aAnalysis[$nCol]["field"]."` TO `".$sNewName."`";
		}
		$sRename = "ALTER TABLE `".$sTable."` ".implode(" , ", $aRen)." ;";

		if($this->db->query($sRename)!==null) {
			foreach($aRename as $nCol => $sNewName) {
				$aAnalysis[$nCol]["field"] = $sNewName;
			}
			$this->UpdateAnalysis($sTable, $aAnalysis);
		}

		return $this->Output($this->analyse());
	}

	public function pecked() {
		$sTable = $this->ChkSource();
		$this->ChkHash();
		$not = $this->db->query("SELECT SUM(IF(`__pecked__` IS NOT NULL, 1, 0)) AS 'pecked', COUNT(*) AS `count` FROM `".$sTable."`");
		if($not->rows()) {
			return $this->Output($not->getall());
		}
	}

	public function notpecked() {
		$sTable = $this->ChkSource();
		$this->ChkHash();
		$not = $this->db->query("SELECT SUM(IF(`__pecked__` IS NULL, 1, 0)) AS 'not', COUNT(*) AS `count` FROM `".$sTable."`");
		if($not->rows()) {
			return $this->Output($not->getall());
		}
	}

	// analiza mejores tipos de campos para las columnas
	public function improve() {
		list($aCols) = $this->getarguments("cols", func_get_args());
		$sTable = $this->ChkSource();
		$this->ChkAnalysis();
		$aAnalysis = $this->attribute("analysis");

		$aChange = array();
		foreach($aCols as $nCol) {
			$sChange  = "CHANGE COLUMN `".$aAnalysis[$nCol]["field"]."` `".$aAnalysis[$nCol]["field"]."` ";
			$sChange .= $aAnalysis[$nCol]["improve"];
			if($aAnalysis[$nCol]["improve"]=="char" || $aAnalysis[$nCol]["improve"]=="varchar") {
				$aLengths = array(4,8,16,32,64,128,255,$aAnalysis[$nCol]["length"]);
				sort($aLengths);
				$nIdx = array_search($aAnalysis[$nCol]["length"], $aLengths);
				$sChange .= " (".$aLengths[$nIdx+1].") ";
			}
			$sChange .= " NULL DEFAULT NULL";
			$aChange[] = $sChange;
		}
		$sImprove = "ALTER TABLE `".$sTable."` ".implode(" ,\n ", $aChange)." ;";

		$this->db->query($sImprove);
		return $this->Output($this->BuildAnalysis($sTable));
	}

	// muestra/inserta los valores unicos de una columna
	public function uniques() {
		list($mCols,$bInsert) = $this->getarguments("cols,exec", func_get_args());
		$sTable = $this->ChkSource();
		if($mCols===null) { self::errorMessage($this->object, 1005); }
		if(is_int($mCols)) { $mCols = array($mCols); }
		$aColumns = $this->GetCols($mCols);

		if(!$bInsert) {
			$aOutput = array();
			foreach($aColumns as $sField) {
				$uniques = $this->db->query("SELECT DISTINCT `".$sField."` AS 'unique' FROM `".$sTable."` WHERE TRIM(`".$sField."`) != '' ORDER BY 1");
				$aOutput = array_merge($aOutput, $uniques->getall("#unique"));
			}
			return $this->Output($aOutput);
		} else {
			$aFeatures = $this->ChkFeatures();
			$sFeaturesTable = $aFeatures["table"];
			$sColName = $aFeatures["match"];
			$sSelect = ($aFeatures["imya"]) ? " NULL, func.imya(), '1', NULL, " : " NULL, ";

			$aNew = array();
			foreach($aColumns as $sField) {
				$new = $this->db->query("
					SELECT `".$sField."` AS 'unique'  
					FROM `".$sTable."` 
					WHERE 
						TRIM(`".$sField."`) != '' AND 
						`".$sField."` NOT IN (SELECT `".$sColName."` FROM `".$sFeaturesTable."`) 
					GROUP BY `".$sField."` 
					ORDER BY 1
				");
				$aNew = array_merge($aNew, $new->getall("#unique"));
			}

			if(count($aNew)) {
				foreach($aNew as $aNewValue) {
					$this->db->query("INSERT INTO `".$sFeaturesTable."` () VALUES (".$sSelect." '".$aNewValue["unique"]."');");
				}
				return $this->Output($aNew);
			}

			return $this->Output(array(array("message"=>"empty result")));
		}

		return false;
	}

	public function getfeatures() {
		list($mCols,$sDestine) = $this->getarguments("cols,xtable", func_get_args());
		$sTable = $this->ChkSource();
		if($mCols===null) { self::errorMessage($this->object, 1005); }
		if(is_int($mCols)) { $mCols = array($mCols); }
		$aColumns = $this->GetCols($mCols);

		if(!$sDestine) { self::errorMessage($this->object, 1010); }
		$aFeatures = $this->ChkFeatures();
		$sFeaturesTable = $aFeatures["table"];
		$sFeaturesId = $aFeatures["id"];
		$sFeaturesMatch = $aFeatures["match"];

		$sSelect = ($this->IsOwlTable($sDestine)) ? " NULL, func.imya(), '1', " : "";

		$nAffected = 0;
		foreach($aColumns as $sField) {
			$features = $this->db->query("
				INSERT INTO `".$sDestine."` 
					SELECT ".$sSelect." `p`.`__pecked__`, `f`.`".$sFeaturesId."` 
					FROM `".$sTable."` p 
						LEFT JOIN `".$sFeaturesTable."` f ON `f`.`".$sFeaturesMatch."` = `p`.`".$sField."`
					WHERE 
						`p`.`__pecked__` IS NOT NULL AND 
						`p`.`".$sField."` != '' AND 
						`p`.`".$sField."` IS NOT NULL 
					GROUP BY `p`.`__pecked__`
			");

			$nAffected += $features->rows();
		}

		$this->Output(array(array("affected"=>$nAffected)));
	}

	// normaliza los datos de una columna basandose en la tabla features
	// antes de normalizar, agrega a features los valores inexistentes
	public function normalize() {
		list($nCol) = $this->getarguments("col", func_get_args());
		$sTable = $this->ChkSource();
		$sField = $this->GetCols($nCol);
		$aFeatures = $this->ChkFeatures();
		$sFeaturesTable = $aFeatures["table"];
		$sFeaturesId = $aFeatures["id"];
		$sFeaturesMatch = $aFeatures["match"];

		$this->uniques(array($nCol), true);

		$sFieldBack = $sField."_".self::call()->unique(6);
		$this->db->query("ALTER TABLE `".$sTable."` RENAME COLUMN `".$sField."` TO `".$sFieldBack."`");
		$this->db->query("ALTER TABLE `".$sTable."` ADD COLUMN `".$sField."` INT NULL DEFAULT NULL, ADD INDEX `".$sField."` (`".$sField."`)");
		$normalize = $this->db->query("
			UPDATE 
				`".$sTable."` p, `".$sFeaturesTable."` f 
				SET `p`.`".$sField."` = `f`.`".$sFeaturesId."` 
			WHERE 
				`p`.`".$sFieldBack."` != '' AND 
				`p`.`".$sFieldBack."` IS NOT NULL AND 
				`p`.`".$sFieldBack."` = `f`.`".$sFeaturesMatch."`
		");

		$this->BuildAnalysis($sTable);
		$this->Output(array(array("normalized"=>$normalize->rows())));
	}

	public function filltest() {
		list($sDestine,$aCols,$bInverse) = $this->getarguments("xtable,cols,inverse", func_get_args());
		$sTable = $this->ChkSource();
		$sWhere = (!$bInverse) ? "IS NOT NULL" : "IS NULL";
		$chk = $this->db->query("SELECT COUNT(*) AS 'chk' FROM `".$sTable."` WHERE `__pecked__` ".$sWhere);
		$this->Output(array(array("to insert"=>$chk->get("chk"))));
	}

	public function fill() {
		list($sDestine,$aCols,$bInverse) = $this->getarguments("xtable,cols,inverse", func_get_args());
		$sTable = $this->ChkSource();
		$sWhere = (!$bInverse) ? "IS NOT NULL" : "IS NULL";
		$chk = $this->db->query("SELECT COUNT(*) AS 'chk' FROM `".$sTable."` WHERE `__pecked__` ".$sWhere);

		if($chk->get("chk")) {
			$aSelect = $this->GetCols(array_keys($aCols));
			$aInsert = $this->GetCols($aCols, $sDestine);
			if($bInverse) {
				$sImyaField = self::call()->unique(8);
				$this->db->query("ALTER TABLE `".$sTable."` ADD COLUMN `".$sImyaField."` CHAR(32) NULL DEFAULT NULL FIRST, ADD INDEX `".$sImyaField."` (`".$sImyaField."`)");
				$this->db->query("UPDATE `".$sTable."` SET `".$sImyaField."` = func.imya() WHERE `__pecked__` ".$sWhere);
				$fill = $this->db->query("
					INSERT INTO `".$sDestine."` (`id`, `imya`, `state`, `".implode("`,`", $aInsert)."`) 
						SELECT NULL, `".$sImyaField."`, '1', `".implode("`,`", $aSelect)."` FROM `".$sTable."` WHERE `__pecked__` ".$sWhere
				);
				$this->db->query("UPDATE `".$sTable."` a, `".$sDestine."` b SET `a`.`__pecked__` = `b`.`id` WHERE `a`.`".$sImyaField."` = `b`.`imya`");
				$this->db->query("ALTER TABLE `".$sTable."` DROP COLUMN `".$sImyaField."`");
			} else {
				$sSQL = "INSERT INTO `".$sDestine."` (`id`, `imya`, `state`, `".implode("`,`", $aInsert)."`) ";
				$sSQL .= "SELECT NULL, func.imya(), '1', `".implode("`,`", $aSelect)."` FROM `".$sTable."` WHERE `__pecked__` ".$sWhere;
				$fill = $this->db->query($sSQL);
			}
		}

		$this->Output(array(array("inserted"=>$fill->rows())));
	}

	// completa campos de xtable con datos de la tabla principal
	public function complete() {
		list($sDestine,$aCols,$bOverwrite) = $this->getarguments("xtable,cols,overwrite", func_get_args());
		$sTable = $this->ChkSource();
		$chk = $this->db->query("SELECT COUNT(*) AS 'chk' FROM `".$sTable."` WHERE `__pecked__` IS NOT NULL");
		$sKey = $this->argument("key");

		if($chk->get("chk") && $sKey) {
			$aAnalysis = $this->ChkAnalysis();
			$aXAnalysis = $this->ChkAnalysis($sDestine);
			$sWhere = (!$bOverwrite) ? " (".$sXField." = '' OR ".$sXField." IS NULL) AND " : "";
			$nCount = 0;
			foreach($aCols as $x => $y) {
				$sXField = "`a`.`".$aXAnalysis[$y]["field"]."`";
				$sField = "`b`.`".$aAnalysis[$x]["field"]."`";
				$sSQL = "
					UPDATE `".$sDestine."` a, `".$sTable."` b 
						SET ".$sXField." = ".$sField." 
					WHERE 
						".$sWhere." 
						(".$sField." != '' AND ".$sField." IS NOT NULL) AND 
						`a`.`".$sKey."` = `b`.`__pecked__`
				";
				$nCount += $this->db->query($sSQL)->rows();
			}
		}

		$this->Output(array(array("completed"=>$nCount)));
	}

	public function concat() {
		list($aCols,$nCol,$sSeparator,$sWhere) = $this->getarguments("cols,col,splitter,where", func_get_args());
		$sTable = $this->ChkSource();
		$chk = $this->db->query("SELECT COUNT(*) AS 'chk' FROM `".$sTable."` WHERE ".$sWhere);
		if($chk->get("chk")) {
			$sSeparator = addslashes($sSeparator);
			$aConcat = array();
			$aColumns = $this->GetCols($aCols);
			foreach($aColumns as $sColname) {
				$aConcat[] = " IF(`".$sColname."`!='' AND `".$sColname."` IS NOT NULL, CONCAT(`".$sColname."`,'".$sSeparator."'), '') ";
			}
			$sConcat = " CONCAT(".implode(",", $aConcat).") ";
			$sField = $this->GetCols($nCol);
			$concat = $this->db->query("UPDATE `".$sTable."` SET `".$sField."` = ".$sConcat." WHERE ".$sWhere);
			return $this->Output(array(array("affected"=>$concat->rows())));
		}

		return $this->Output(array(array("message"=>"empty result")));
	}
	
	public function sanitize() {
		list($mCols,$aPolicy,$sWhere) = $this->getarguments("cols,policy,where", func_get_args());
		if($sWhere===null) { $sWhere = 1; }
		$sTable = $this->ChkSource();
		$chk = $this->db->query("SELECT COUNT(*) AS 'chk' FROM `".$sTable."` WHERE ".$sWhere);
		if($chk->get("chk")) {
			$aToUpdate = array();
			$mFields = $this->GetCols($mCols);
			if(!is_array($mFields)) { $mFields = array($mFields); }
			foreach($mFields as $sField) {
				$aToUpdate[] = "`".$sField."` = ".$this->Sanitizer($sField, $aPolicy);
			}
			// die("UPDATE `".$sTable."` SET ".implode(",", $aToUpdate)." WHERE ".$sWhere);
			$sanitized = $this->db->query("UPDATE `".$sTable."` SET ".implode(",", $aToUpdate)." WHERE ".$sWhere);
			return $this->Output(array(array("affected"=>$sanitized->rows())));
		}

		return $this->Output(array(array("message"=>"empty result")));	
	}


	public function drop($mCols) {
		$sTable = $this->ChkSource();
		$this->ChkAnalysis();
		if(!is_array($mCols)) { $mCols = array($mCols); }

		$aDrop = array();
		foreach($mCols as $nCol) {
			$aDrop[] = "DROP COLUMN `".$this->aAnalysis[$nCol]["field"]."`";
		}
		$sDrop = "ALTER TABLE `".$this->sTable."` ".implode(" , ", $aDrop)." ;";

		if($this->db->query($sDrop)!==null) {
			unset($this->aAnalysis[$nCol]);
		}

		return $this;
	}

	public function filter($lambda=null) {
		if($lambda===null) { return array_keys($this->aAnalysis); }
		return array_keys(array_filter($this->aAnalysis, $lambda));
	}

	
	private function Sanitizer($sField, $aPolicy) {
		foreach($aPolicy as $sPolicy) {
			if(strstr($sPolicy, ":")) {
				$aPolParts = explode(":", $sPolicy, 2);
				$sPolicy = $aPolParts[0];
				$sPolicyArg = $aPolParts[1];
			}
			switch($sPolicy) {
				case "trim":
					if(isset($sPolicyArg)) {
						$sField = "TRIM(BOTH '".$sPolicyArg."' FROM ".$sField.")";
					} else {
						$sField = "TRIM(".$sField.")";
					}
					break;
				case "lcase": $sField = "LCASE(".$sField.")"; break;
				case "ucase": $sField = "UCASE(".$sField.")"; break;
				case "letters": $sField = "REGEXP_REPLACE(".$sField.", '[^A-Za-z]', '')"; break;
				case "digits": $sField = "REGEXP_REPLACE(".$sField.", '[^0-9]', '')"; break;
				case "numbers": $sField = "REGEXP_REPLACE(".$sField.", '[^0-9\\,\\.\\-]', '')"; break;
				case "email": $sField = "REGEXP_REPLACE(".$sField.", '[^0-9a-zA-Z\\@\\_\\.\\-]', '')"; break;
				case "words": $sField = "REGEXP_REPLACE(".$sField.", '[^0-9a-zA-Z]', '')"; break;
				case "nospaces": $sField = "REGEXP_REPLACE(".$sField.", '\s', '')"; break;
				case "consonants": $sField = "REGEXP_REPLACE(".$sField.", '([^B-Zb-z]|[eiouEIOU])', '')"; break;
				case "right": $sField = "RIGHT(".$sField.", ".$sPolicyArg.")"; break;
				case "left": $sField = "LEFT(".$sField.", ".$sPolicyArg.")"; break;
			}
		}

		return $sField;
	}

	private function GetCols($mCols, $sTable=null) {
		if($sTable===null) { $sTable = $this->ChkSource(); }
		$aAnalysis = $this->ChkAnalysis($sTable);
		if(!is_array($mCols)) { return $aAnalysis[$mCols]["field"]; }
		$aColumns = array();
		foreach($mCols as $nCol) {
			$aColumns[] = $aAnalysis[$nCol]["field"];
		}
		return $aColumns;
	}

	private function ChkAnalysis($sTable=null) {
		if($sTable===null) { $sTable = $this->ChkSource(); }
		if(!isset($this->aSavedData["ANALYSIS"][$sTable])) {
			$this->BuildAnalysis($sTable);
		}
		return $this->aSavedData["ANALYSIS"][$sTable];
	}

	private function BuildAnalysis($sTable) {
		$nRows = $this->db->query("SELECT COUNT(*) AS 'rows' FROM `".$sTable."`")->get("rows");
		if(!$nRows) { self::errorMessage($this->object, 1009); }
		$aColumns = array();

		$structure = $this->db->query("SELECT `COLUMN_NAME`, `DATA_TYPE`, `COLUMN_TYPE` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$this->db->base."' AND `TABLE_NAME`='".$sTable."'");
		$aStructure = $structure->getall("#COLUMN_NAME"); 

		$aAnalyse = $this->db->query("SELECT * FROM `".$sTable."` PROCEDURE ANALYSE (".$nRows.")")->getall();
		$bAnalyseDataType = $this->argument("analyse_datatype");

		$x = 0;
		foreach($aAnalyse as $aTable) {
			$sField = substr($aTable["Field_name"], strrpos($aTable["Field_name"], ".")+1);
			if($sField=="__pecker__" || $sField=="__pecked__" || $sField=="__wano__") { continue; }
			$aColumns[$x]["col"] = $x;
			$aColumns[$x]["field"] = $sField;
			$aColumns[$x]["type"] = $aStructure[$sField]["COLUMN_TYPE"];
			$aColumns[$x]["improve"] = "text";
			$aColumns[$x]["length"] = $aTable["Max_length"];
			$aColumns[$x]["min"] = $aTable["Min_value"];
			$aColumns[$x]["max"] = $aTable["Max_value"];
			$aColumns[$x]["empties"] = $aTable["Empties_or_zeros"];
			$aColumns[$x]["nulls"] = $aTable["Nulls"];
			$aColumns[$x]["normalizable"] = 0;
			$aColumns[$x]["rows"] = $nRows;
	
			if($aTable["Max_length"]>255) {
				$aColumns[$x]["improve"] = "text";
			} else if($aTable["Max_length"]==-1) {
				$aColumns[$x]["improve"] = "varchar";
			} else {
				$aColumns[$x]["improve"] = "varchar";
				if($bAnalyseDataType) {
					$aTypes = $this->db->query("
						SELECT 
							COUNT(`".$sField."` REGEXP '^-?[1-9][0-9]*$' OR NULL) AS 'int',
							COUNT(REPLACE(`".$sField."`, ',', '') REGEXP '^-?[0-9]+\\\.[0-9]+$' OR NULL) AS 'decimal', 
							COUNT(REPLACE(`".$sField."`, '/', '-') REGEXP '^[0-9]{2,4}-([0-9]{2}|[a-z]{3})(-[0-9]{2,4})?$' OR NULL) AS 'date', 
							COUNT(`".$sField."` REGEXP '^[0-9]{1,2}:[0-9]{1,2}(:[0-9]{1,2})?$' OR NULL) AS 'time' 
						FROM `".$sTable."`
					")->get();
					arsort($aTypes);

					$aType = array(key($aTypes), current($aTypes));
					if($aType[1]>0 && ($aType[1]*100/$nRows) > 85) {
						$aColumns[$x]["improve"] = $aType[0];
						$aColumns[$x]["length"] = $aTable["Max_length"];
					}
			
					if($aColumns[$x]["improve"]=="varchar") {
						$aDistincts = $this->db->query("
							SELECT 
								COUNT(DISTINCT `".$sField."`) AS 'distincts', 
								COUNT(*) AS 'total' 
							FROM `".$sTable."` 
							WHERE `".$sField."` IS NOT NULL AND `".$sField."`!=''
						")->get();
			
						if($aDistincts["distincts"]==1 && $aDistincts["total"]<=$nRows) {
							if($aTable["Max_length"]>5) {
								$aColumns[$x]["improve"] = "int";
								$aColumns[$x]["normalizable"] = (int)$aDistincts["distincts"];
							} else {
								$aColumns[$x]["improve"] = "boolean";
							}
						} else if($aDistincts["total"] > 0 && ($aDistincts["distincts"]*100/$aDistincts["total"]) < 10) {
							$aColumns[$x]["improve"] = "int";
							$aColumns[$x]["normalizable"] = (int)$aDistincts["distincts"];
						}
					} else if($aColumns[$x]["improve"]=="int") {
						$nValue = $aTable["Max_value"];
						switch(true) {
							case ($nValue >= -128 && $nValue <= 127): $aColumns[$x]["improve"] = "tinyint"; break;
							case ($nValue >= -32768 && $nValue <= 32767): $aColumns[$x]["improve"] = "smallint"; break;
							case ($nValue >= -8388608 && $nValue <= 8388607): $aColumns[$x]["improve"] = "mediumint"; break;
							case ($nValue > 2147483647): $aColumns[$x]["improve"] = "bigint"; break;
						}
					}
				}
			}
			$x++;
		}

		$this->UpdateAnalysis($sTable, $aColumns);
		return $aColumns;
	}

	private function ChkSource() {
		$sTable = $this->argument("table");
		$chk = $this->db->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$this->db->base."' AND `TABLE_NAME`='".$sTable."'");
		if(!$chk->rows()) { self::errorMessage($this->object, 1006); }
		return $sTable;
	}

	private function ChkHash($sTable=null, $bAbortOnError=true) {
		if($sTable===null) { $sTable = $this->argument("table"); }
		$chk = $this->db->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$this->db->base."' AND `TABLE_NAME`='".$sTable."' AND `COLUMN_NAME`='__pecker__'");
		if(!$chk->rows()) {
			if($bAbortOnError) {
				self::errorMessage($this->object, 1007, $sTable);
			} else {
				return false;
			}
		}
		return true;
	}

	private function ChkKey() {
		$sTable = $this->argument("table");
		$sKey = $this->argument("key");
		$chk = $this->db->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$this->db->base."' AND `TABLE_NAME`='".$sTable."' AND `COLUMN_NAME`='".$sKey."'");
		if(!$chk->rows()) { self::errorMessage($this->object, 1008); }
		return $sKey;
	}

	private function ChkFeatures() {
		if($this->attribute("features_schema")===null) { self::errorMessage($this->object, 1011); }
		return $this->attribute("features_schema");
	}

	
	protected function SetGrouper($aGrouper) {
		$this->attribute("grouper_str", "`".implode("`,`", $aGrouper)."`");
		$sTable = $this->argument("table");
		if($sTable!==null) {
			$this->aSavedData["GROUPER"][$sTable] = $aGrouper;
			$this->SaveData();
		}
		return $aGrouper;
	}

	protected function SetFields($aFields) {
		$this->attribute("fields_str", "`".implode("`,`", $aFields)."`");
		$sTable = $this->argument("table");
		if($sTable!==null) {
			$this->aSavedData["FIELDS"][$sTable] = $aFields;
			$this->SaveData();
		}
		return $aFields;
	}

	protected function SecureName($sName) {
		return preg_replace("/[^A-Za-z0-9\_\-]/is", "", $sName);
	}

	protected function SetTable($sTableName) {
		$sTableName = $this->SecureName($sTableName);
		if(isset($this->aSavedData["ANALYSIS"], $this->aSavedData["ANALYSIS"][$sTableName])) {
			$this->attribute("analysis", $this->aSavedData["ANALYSIS"][$sTableName]);
			if(isset($this->aSavedData["GROUPER"][$sTableName])) { $this->attribute("grouper_str", $this->aSavedData["GROUPER"][$sTableName]); }
			if(isset($this->aSavedData["FIELDS"][$sTableName])) { $this->attribute("fields_str", $this->aSavedData["FIELDS"][$sTableName]); }
		}
		return $sTableName;
	}

	protected function SetDb($db) {
		$this->db = $db;
		if(!$this->db->connect()) { self::errorMessage($this->object, 1001); }
		return $db;
	}

	protected function SetDataFile($sFileName) {
		$this->sSaveId = $sFileName.".pecker";
		$this->aSavedData = $this->LoadData();
		return $sFileName;
	}

	protected function SetFeatures($aFeatures) {
		if(!is_array($aFeatures) || count($aFeatures)<3) { self::errorMessage($this->object, 1011); }
		$sFeTable = $this->SecureName($aFeatures[0]);
		$sFeId = $this->SecureName($aFeatures[1]);
		$sFeMatch = $this->SecureName($aFeatures[2]);

		$schema = $this->db->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$this->db->base."' AND `TABLE_NAME`='".$sFeTable."'");
		if($schema->rows()) {
			$aGetSchema = $schema->getall("#COLUMN_NAME");
			if(!isset($aGetSchema[$sFeId]) || !isset($aGetSchema[$sFeMatch])) { self::errorMessage($this->object, 1012); }
			$aSchema = array(
				"table" => $sFeTable,
				"id" => $sFeId,
				"match" => $sFeMatch,
				"imya" => (isset($aGetSchema["imya"])) ? true : false
			);
		} else {
			self::errorMessage($this->object, 1012);
		}

		$this->attribute("features_schema", $aSchema);
		return $aFeatures;
	}

	private function Output($mData) {
		$sOutputMode = $this->argument("output");
		if(is_array($mData) && count($mData)) {
			if($sOutputMode=="print") {
				echo self::call("shift")->convert($mData, "array-ttable");
			} else if($sOutputMode=="table") {
				return self::call("shift")->convert($mData, "array-ttable");
			} else {
				return $mData;
			}
		}
	}

	private function IfTableExist($sTable) {
		$exist = $this->db->query("SELECT * FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`='".$this->db->base."' AND `TABLE_NAME`='".$sTable."'");
		return ($exist->rows()) ? true : false;
	}

	private function IsOwlTable($sTable) {
		$schema = $this->db->query("SELECT * FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$this->db->base."' AND `TABLE_NAME`='".$sTable."' AND `COLUMN_NAME`='imya'");
		return ($schema->rows()) ? true : false;
	}

	private function LoadData() {
		return self::call()->dataFileLoad("/tmp/".$this->sSaveId);
	}

	private function SaveData() {
		return self::call()->dataFileSave("/tmp/".$this->sSaveId, $this->aSavedData);
	}

	private function UpdateAnalysis($sTable, $aAnalysis) {
		$this->attribute("analysis", $aAnalysis);
		$this->aSavedData["ANALYSIS"][$sTable] = $aAnalysis;
		$this->SaveData();
	}

	private function ClearAnalysis() {
		$sTable = $this->argument("table");
		$this->attribute("analysis", null);
		unset($this->aSavedData["ANALYSIS"][$sTable]);
		$this->SaveData();
	}

	private function Mixer($aUnify, $aFields, $aRules) {
		$aUnique = array_shift($aUnify);
		$sSplitter = $this->argument("splitter");
		foreach($aFields as $sField) {
			if($aRules[$sField]===false) { continue; }
			
			$sSaveIdx = trim(strtolower($aUnique[$sField]));
			$nSaveIdx = strlen($sSaveIdx);
			if($aRules[$sField]=="join") {
				$aToSave = array(md5($sSaveIdx) => $aUnique[$sField]);
			} else if($aRules[$sField]=="longer") {
				$aToSave = array($nSaveIdx => array(md5($sSaveIdx) => $aUnique[$sField]));
			} else {
				$aToSave = array();
			}

			foreach($aUnify as $aRow) {
				switch($aRules[$sField]) {
					case "any":
						if($aUnique[$sField]==="") { $aUnique[$sField] = $aRow[$sField]; }
						break;

					case "noempty":
						if(empty($aUnique[$sField])) { $aUnique[$sField] = $aRow[$sField]; }
						break;
					
					case "join":
						$aToSave[md5(trim(strtolower($aRow[$sField])))] = $aRow[$sField];
						break;
					
					case "longer":
						$sVal = trim(strtolower($aRow[$sField]));
						$nVal = strlen($sVal);
						if(!isset($aToSave[$nVal])) { $aToSave[$nVal] = array(); }
						$aToSave[$nVal][md5($sVal)] = $aRow[$sField];
						break;
				}
			}

			if(count($aToSave)) {
				if($aRules[$sField]=="longer") {
					krsort($aToSave);
					$aToSave = current($aToSave);
				}
				$aUnique[$sField] = implode($sSplitter, $aToSave);
			}
		}
		
		return $aUnique;
	}
}

?>