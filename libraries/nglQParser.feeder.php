<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# qparser
https://hytcom.net/nogal/docs/objects/qparser.md
*/
namespace nogal;
class nglQParser extends nglFeeder implements inglFeeder {

	final public function __init__($mArguments=null) {
	}

	public function query($sSQL) {
		$sSQL = \trim($sSQL);
		$sSQLCase = (\strstr($sSQL, " ")) ? \strtoupper(\substr($sSQL, 0, \strpos($sSQL, " "))) : $sSQL;

		switch($sSQLCase) {
			case "DESCRIBE":
				return ["DESCRIBE", [\substr($sSQL, 8)]];

			case "CREATE":
				\preg_match("/(create table )(.*?)\((.*?)\)$/is", $sSQL, $aMatchs);
				return ["CREATE", [$aMatchs[2], self::call()->explodeTrim(",", $aMatchs[3])]];

			case "TRUNCATE":
				return ["TRUNCATE", [\substr($sSQL, 8)]];
				break;

			case "SELECT":
				$aReturn = [];
				\preg_replace_callback([
						"/(SELECT)(.+?)(?=FROM)/is",
						"/(FROM)(.+?)(?=(WHERE|ORDER BY|LIMIT|$))/is",
						"/(WHERE)(.+?)(?=(ORDER BY|LIMIT|$))/is",
						"/(ORDER BY)(.+?)(?=(LIMIT|$))/is",
						"/(LIMIT)(.+[^ ]$)/is"
					],
					function($aMatchs) use (&$aReturn) {
						return $aReturn[\strtoupper($aMatchs[1])] = \trim($aMatchs[2]);
					},
					$sSQL
				);

				$aReturn["FROM"] = $this->ParseTables($aReturn["FROM"]);
				$aReturn["FIELDS"] = $this->ParseFields($aReturn["SELECT"], $aReturn["FROM"][0]);
				if(isset($aReturn["ORDER BY"])) { $aReturn["ORDER BY"] = $this->ParseFields($aReturn["ORDER BY"], $aReturn["FROM"][0]); }
				if(isset($aReturn["LIMIT"])) { $aReturn["LIMIT"] = self::call()->explodeTrim(",", $aReturn["LIMIT"]); }
				if(isset($aReturn["WHERE"])) {
					$aWhere = [];
					$sWhere = $this->ParseWhere($aReturn["WHERE"], $aReturn["FROM"][0], $aWhere);
					$aReturn["WHERE"] = $sWhere;
					$aReturn["WHERE_PARTS"] = $aWhere;
				}
				unset($aReturn["SELECT"]);
				return ["SELECT", $aReturn];

			case "INSERT":
				$aReturn = $aParse = [];
				\preg_replace_callback([
						"/(INSERT INTO)(.+?)(?=\()/is",
						"/(\()(.+?)(?=\)) ?(?=\) *VALUES)/is",
						"/(VALUES) *\((.+?)(?=\) *$)/is"
					],
					function($aMatchs) use (&$aParse) {
						return $aParse[strtoupper($aMatchs[1])] = trim($aMatchs[2]);
					},
					$sSQL
				);

				$aReturn["FROM"] = $this->ParseTables($aParse["INSERT INTO"])[0];
				$aReturn["FIELDS"] = $this->ParseFields($aParse["("], false);
				$aReturn["VALUES"] = $this->ParseFields($aParse["VALUES"]);
				$aReturn["SET"] = \array_combine($aReturn["FIELDS"], $aReturn["VALUES"]);
				return ["INSERT", $aReturn];

			case "UPDATE":
				$aReturn = $aParse = [];
				\preg_replace_callback([
						"/(UPDATE)(.+?)(?=SET)/is",
						"/(SET)(.+?)(?=WHERE|$)/is",
						"/(WHERE)(.+)/is"
					],
					function($aMatchs) use (&$aParse) {
						return $aParse[strtoupper($aMatchs[1])] = trim($aMatchs[2]);
					},
					$sSQL
				);

				$aReturn["FROM"] = $this->ParseTables($aParse["UPDATE"])[0];
				$aSet = [];
				$this->ParseWhere($aParse["SET"], false, $aSet);
				$aReturn["SET"] = [];
				for($x=0; $x < \count($aSet); $x=$x+3) {
					$aReturn["SET"][$aSet[$x]] = $aSet[$x+2];
				}

				if(isset($aParse["WHERE"])) {
					$aWhere = [];
					$sWhere = $this->ParseWhere($aParse["WHERE"], $aReturn["FROM"], $aWhere);
					$aReturn["WHERE"] = $sWhere;
					$aReturn["WHERE_PARTS"] = $aWhere;
				}
				return ["UPDATE", $aReturn];

			case "DELETE":
				$aReturn = $aParse = [];
				\preg_replace_callback([
						"/(DELETE FROM)(.+?)(?=WHERE|$)/is",
						"/(WHERE)(.+)/is"
					],
					function($aMatchs) use (&$aParse) {
						return $aParse[\strtoupper($aMatchs[1])] = \trim($aMatchs[2]);
					},
					$sSQL
				);

				$aReturn["FROM"] = $this->ParseTables($aParse["DELETE FROM"])[0];
				if(isset($aParse["WHERE"])) {
					$aWhere = [];
					$sWhere = $this->ParseWhere($aParse["WHERE"], $aReturn["FROM"], $aWhere);
					$aReturn["WHERE"] = $sWhere;
					$aReturn["WHERE_PARTS"] = $aWhere;
				}
				return ["DELETE", $aReturn];

			default:
				return false;
		}
	}

	private function ParseWhere($sWhere, $sTableName=null, &$aWhere) {
		$sReturn = "";
		\preg_replace_callback(
			"/([\( ]*)((\'|\").*?(\'|\")|(\`?[0-9a-z\_\-]+\`?)(\.\`?[0-9a-z\_\-]+\`?)?) ?(=|!=|<|>|<=|>=) ?((\'|\").*?(\'|\")|(\`?[0-9a-z\_\-]+\`?)(\.\`?[0-9a-z\_\-]+\`?)?)?([\) ]*)(AND|OR)?/is",
			function($aMatchs) use (&$sReturn, $sTableName, &$aWhere) {
				$mField1 = $this->Keyword($aMatchs[2], $sTableName);
				$mField2 = $this->Keyword($aMatchs[8], $sTableName);
				$aCondition = $aConditionParts = [];
				$aCondition[] = (!\is_array($mField1)) ? $mField1 : '$'.$mField1[0].'["'.$mField1[1].'"]';
				$aCondition[] = $this->Operator($aMatchs[7]);
				$aCondition[] = (!\is_array($mField2)) ? $mField2 : '$'.$mField2[0].'["'.$mField2[1].'"]';
				$aConditionParts[] = $mField1;
				$aConditionParts[] = $aCondition[1];
				$aConditionParts[] = $mField2;
				if(\trim($aMatchs[1])!="") { \array_unshift($aCondition, "("); \array_unshift($aConditionParts, "("); }
				if(\trim($aMatchs[13])!="") { $aCondition[] = \trim(")");  $aConditionParts[] = \trim(")"); }
				if(isset($aMatchs[14])) { $aCondition[] = " ".trim($aMatchs[14])." "; $aConditionParts[] = " ".trim($aMatchs[14])." "; }
				$aWhere = \array_merge($aWhere, $aConditionParts);
				$sReturn .= \implode("", $aCondition);
				return $sReturn;
			},
			$sWhere
		);

		if($sReturn==="") { $sReturn = "1"; $aWhere[] = 1; }
		return $sReturn;
	}

	private function ParseTables($sTables) {
		$sTables = \str_replace("`", "", $sTables);
		return self::call()->explodeTrim(",", $sTables);
	}

	private function ParseFields($sFields, $sTableName=null) {
		if($sFields!="*" && \strtoupper($sFields)!="ALL") {
			$aFields = self::call()->explodeTrim(",", $sFields);
			foreach($aFields as &$sField) {
				$sField = $this->Keyword($sField, $sTableName);
			}
		} else {
			$aFields = ["*"];
		}

		return $aFields;
	}

	private function Operator($sOperator) {
		$sSing = \trim($sOperator);
		if($sSing=="=") { $sSing = "=="; }
		return $sSing;
	}

	private function Keyword($sKeyword, $sTableName=null) {
		if(!empty($sKeyword)) {
			if($sKeyword[0]=="'" || $sKeyword[0]=='"') {
				return '"'.\substr($sKeyword, 1, -1).'"';
			} else {
				if(\strpos($sKeyword, ".")) {
					$aKeyword = \explode(".", $sKeyword);
					$sTableName = $aKeyword[0];
					$sKeyword = $aKeyword[1];
				}

				if($sTableName===false) {
					$sKeyword = \str_replace("`", "", $sKeyword);
					return \trim($sKeyword);
				} else {
					if(\strpos($sTableName, ",")) {
						$sTableName = \substr($sTableName, 0, \strpos($sTableName, ","));
					}
				}

				$sTableName = \str_replace("`", "", $sTableName);
				$sKeyword = \str_replace("`", "", $sKeyword);

				// return '$'.$sTableName.'["'.trim($sKeyword).'"]';
				return [\trim($sTableName), \trim($sKeyword)];
			}
		}
	}
}

?>