<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# validate
https://hytcom.net/nogal/docs/objects/validate.md
*/
namespace nogal;
class nglValidate extends nglTrunk {

	protected $class		= "nglValidate";
	protected $me			= "validate";
	protected $object		= "validate";
	private $bCheckError 	= false;
	private $aConfig;
	private $mVariables;
	private $vVariables;
	private $vRegex;

	public function __builder__() {
		$this->vRegex = self::call("sysvar")->REGEX;

		if(\file_exists(NGL_PATH_CONF.NGL_DIR_SLASH."validate.conf")) {
			$sConfig = \file_get_contents(NGL_PATH_CONF.NGL_DIR_SLASH."validate.conf");
			$aConfig = self::parseConfigString($sConfig, true);
			$this->aConfig = $aConfig;

			if(isset($aConfig["request"])) {
				if(!empty($aConfig["request"]["proccess"])) {
					if(isset($aConfig["request"]["from"])) {
						$this->request($aConfig["request"]["from"]);
					} else {
						$this->request();
					}
				}
			}
		}
	}

	public function addvar($sVarName, $mValue) {
		$this->vVariables[$sVarName] = $mValue;
		return $this;
	}

	private function CheckValue($mSource, $vRules) {
		if(!isset($vRules["options"])) { $vRules["options"] = []; }

		if(isset($vRules["options"]["multiple"])) {
			$aValues = \preg_split("/".$vRules["options"]["multiple"]."/s", $mSource);
			unset($vRules["options"]["multiple"]);

			$mValue = [];
			foreach($aValues as $mSource) {
				$mValue[] = $this->validate($mSource, $vRules);
			}
		} else {
			$mValue = $mSource;

			if(isset($vRules["options"]["decode"])) {
				if(\strtolower($vRules["options"]["decode"])=="urldecode") {
					$mValue = \urldecode($mValue);
				} else if(\strtolower($vRules["options"]["decode"])=="rawurldecode ") {
					$mValue = \rawurldecode($mValue);
				}
			}

			$mSource = (isset($vRules["options"]["striptags"])) ? \strip_tags($mSource, $vRules["options"]["striptags"]) : $mSource;
			$mValue = $this->ValidateByType($mSource, $vRules["type"], $vRules["options"]);

			if(empty($mValue) && !empty($mSource)) {
				$this->bCheckError = true;
				return "type";
			}

			$nLength = self::call("unicode")->strlen($mValue);

			// minlength
			if(isset($vRules["minlength"]) && $nLength < (int)$vRules["minlength"]) {
				$this->bCheckError = true;
				return "minlength";
			}

			// maxlength
			if(isset($vRules["maxlength"]) && $nLength > (int)$vRules["maxlength"]) {
				$this->bCheckError = true;
				return "maxlength";
			}

			// lessthan y greaterthan
			$bLess = (isset($vRules["lessthan"]));
			$bGreat = (isset($vRules["greaterthan"]));
			if($bLess || $bGreat) {
				$mLess = ($bLess) ? $vRules["lessthan"] : $vRules["greaterthan"];
				$mGreat = ($bGreat) ? $vRules["greaterthan"] : $vRules["lessthan"];

				$nBetween = self::call()->between($mValue, $mLess, $mGreat);

				if($bLess && !$bGreat && $nBetween>0) { $this->bCheckError = true; return "lessthan"; }
				if($bGreat && !$bLess && $nBetween<2) { $this->bCheckError = true; return "greaterthan"; }
				if($bGreat && $bLess && $nBetween!=1) { $this->bCheckError = true; return "between"; }
			}

			// in
			if(isset($vRules["in"])) {
				$aIn = self::call("unicode")->explode(",", $vRules["in"]);
				if(!\in_array($mValue, $aIn)) { $this->bCheckError = true; return "in"; }
			}
		}

		if(isset($vRules["options"]["addslashes"]) && self::call()->istrue($vRules["options"]["addslashes"])) {
			$mValue = \addslashes($mValue);
		}

		if(isset($vRules["options"]["quotemeta"]) && self::call()->istrue($vRules["options"]["quotemeta"])) {
			$mValue = \quotemeta($mValue);
		}

		return $mValue;
	}

	private function ClearCharacters1($sString, $aToClean, $bInvert=false) {
		$sNewString = $sInvertString = $sChar = "";
		$nString = \strlen($sString);
		for($x=0; $x<$nString; $x++) {
			if((\ord($sString[$x])&0xC0)!=0x80) {
				if(\strlen($sChar)) {
					$nOrd = self::call("unicode")->ord($sChar);
					if(!isset($aToClean[$nOrd])) {
						$sNewString .= $sChar;
					} else {
						$sInvertString .= $sChar;
					}
					$sChar = "";
				}
				$sChar .= $sString[$x];
			} else {
				$sChar .= $sString[$x];
			}
		}

		$nOrd = self::call("unicode")->ord($sChar);
		if(!isset($aToClean[$nOrd])) {
			$sNewString .= $sChar;
		} else {
			$sInvertString .= $sChar;
		}

		return ($bInvert) ? $sInvertString : $sNewString;
	}

	private function GetRulesFile($sRulesFile) {
		$sRulesFile = self::call("files")->absPath($sRulesFile);
		$sRulesFile = self::call()->sandboxPath($sRulesFile);
		if(\file_exists($sRulesFile)) {
			$sRules = \file_get_contents($sRulesFile);
			$sRules = \trim($sRules);
			return \json_decode($sRules, true);
		}

		return null;
	}

	public function request($sFrom="LOCAL") {
		$bProccess = false;
		if(isset($_SERVER["HTTP_REFERER"])) {
			$aURL = \parse_url($_SERVER["HTTP_REFERER"]);
			$sRequestHost = $aURL["host"];
			$sIP = \gethostbyname($sRequestHost);

			$aHost = \parse_url($_SERVER["HTTP_HOST"]);
			$sHost = (isset($aHost["host"])) ? $aHost["host"] : $aHost["path"];
			if($sHost==$sRequestHost) {
				$bProccess = true;
			} else {
				$aRequestsFroms = $this->RequestFrom($sFrom);
				switch(1) {
					case (!isset($aRequestsFroms["LOCAL"])):
					case (isset($aRequestsFroms["ALL"])):
					case (isset($aRequestsFroms[$sRequestHost])):
					case (isset($aRequestsFroms[$sIP])):
						$bProccess = true;
					break;
				}
			}
		}

		if($bProccess || !isset($_SERVER["HTTP_REFERER"])) {
			$_REQUEST = $this->validate($_REQUEST, $this->aConfig["request"]["proccess"]);
		} else {
			$_REQUEST = [];
		}
	}

	private function RequestFrom($sFrom) {
		$aRequestsFroms = ["LOCAL" => true];
		if(!empty($sFrom)) {
			$sFrom = \strtoupper($sFrom);
			$sFrom = \trim($sFrom);
			if($sFrom!="ALL" && $sFrom!="LOCAL") {
				$aRequestsFroms = self::call()->explodeTrim(",", $sFrom);
			} else {
				$aRequestsFroms = [$sFrom => true];
			}
		}

		return $aRequestsFroms;
	}

	public function resetvars() {
		$this->vVariables = [];
	}

	public function validate($mVariables, $mRules, $bIgnoreDefault=false) {
		if(\is_array($mRules)) {
			$aRules = $mRules;
		} else {
			$sRules = \trim($mRules);

			$aRules = self::call()->isJson($sRules, "array");
			if($aRules===false) {
				$aRules = $this->GetRulesFile($sRules);
			}
		}

		if($aRules===null) { return null; }

		if(!\is_array($mVariables)) {
			$this->bCheckError = false;
			$mCheck = $this->CheckValue($mVariables, $aRules);

			if($this->bCheckError && !\is_array($mCheck)) {
				$mCheck = "error => ".$mCheck;
			}

			return $mCheck;
		}

		$vReport = $vValidated = [];
		foreach($aRules as $sField => $vRules) {
			unset($mValue);
			foreach($vRules as $sRule => $mRule) {
				if(\is_string($mRule) && \preg_match("/^\{".self::call("sysvar")->REGEX["phpvar"]."\}$/s", $mRule)) {
					$sVarname = \substr($mRule, 2, -1);
					$vRules[$sRule] = (isset($this->vVariables[$sVarname])) ? $this->vVariables[$sVarname] : $mRule;
				}
			}

			if(!isset($vRules["type"])) {
				$vReport[$sField] = "type";
				continue;
			}

			if(isset($mVariables[$sField])) {
				$mValue = $mVariables[$sField];
				if(\is_array($mValue)) {
					$bError = false;
					foreach($mValue as $mKey => $mSubValue) {
						$this->bCheckError = false;
						$mSubValue = $this->CheckValue($mSubValue, $vRules);
						if($this->bCheckError) {
							$bError = true;
							$vReport[$sField." => ".$mKey] = $mSubValue;

							// valor por defecto
							if(!$bIgnoreDefault && isset($vRules["default"])) {
								$mValue[$mKey] = $vRules["default"];
							} else {
								unset($mValue[$mKey]);
							}
						} else {
							$mValue[$mKey] = $mSubValue;
						}
					}
				} else {
					$this->bCheckError = false;
					$mValue = $this->CheckValue($mValue, $vRules);
					if($this->bCheckError) {
						// valor por defecto
						if(!$bIgnoreDefault && isset($vRules["default"])) {
							$mValue = $vRules["default"];
						} else {
							$vReport[$sField] = $mValue;
							continue;
						}
					}
				}
			} else {
				if(!$bIgnoreDefault && isset($vRules["default"])) {
					$mValue = $vRules["default"];
				} else if(isset($vRules["required"]) && self::call()->istrue($vRules["required"])) {
					$vReport[$sField] = "required";
					continue;
				}
			}

			if(isset($mValue)) { $vValidated[$sField] = $mValue; }
		}

		$vCheck 			= [];
		$vCheck["source"]	= $mVariables;
		$vCheck["rules"]	= $aRules;
		$vCheck["values"]	= $vValidated;
		$vCheck["errors"]	= \count($vReport);
		$vCheck["report"]	= $vReport;

		return $vCheck;
	}

	private function ValidateByType($mValue, $sType, $vOptions=[]) {
		if(\ini_get("magic_quotes_sybase") && (\strtolower(\ini_get("magic_quotes_sybase"))!="off")) {
			$mValue = \stripslashes($mValue);
		}

		$aParams = [];
		$mNewValue = "";
		$sType = \strtolower($sType);
		switch($sType) {
			case "all":
				if(isset($vOptions["allow"])) {
					$aToClean = self::call()->explodeTrim(",", $vOptions["allow"]);
					$bDeny = false;
				} else if(isset($vOptions["deny"])) {
					$bDeny = true;
					$aToClean = self::call()->explodeTrim(",", $vOptions["deny"]);
				}

				if(isset($bDeny)) {
					$bSpaces = false;
					foreach($aToClean as $sValue) {
						$sValue = \strtoupper($sValue);

						if($sValue=="SPACES") { $bSpaces = true; }
						if(\strlen($sValue)==3) {
							$aParams["types"][$sValue] = true;
						} else {
							$aParams["groups"][$sValue] = true;
						}
					}

					if($bSpaces) { $aParams["chars"] = [9=>true,10=>true,13=>true,32=>true]; }
					$mNewValue = $this->ClearCharacters($mValue, $aParams, $bDeny);
				} else {
					$mNewValue = $mValue;
				}
				break;

			case "html":
				$vFlags 					= [];
				$vFlags["ENT_COMPAT"]		= ENT_COMPAT;
				$vFlags["ENT_QUOTES"]		= ENT_QUOTES;
				$vFlags["ENT_NOQUOTES"]		= ENT_NOQUOTES;
				$vFlags["ENT_IGNORE"]		= ENT_IGNORE;
				$vFlags["ENT_SUBSTITUTE"]	= ENT_SUBSTITUTE;
				$vFlags["ENT_DISALLOWED"]	= ENT_DISALLOWED;
				$vFlags["ENT_HTML401"]		= ENT_HTML401;
				$vFlags["ENT_XML1"]			= ENT_XML1;
				$vFlags["ENT_XHTML"]		= ENT_XHTML;
				$vFlags["ENT_HTML5"]		= ENT_HTML5;

				$sFlags = (isset($vOptions["htmlentities"])) ? $vOptions["htmlentities"] : "ENT_COMPAT,ENT_HTML401";
				$aFlags = \explode(",", $sFlags);

				$nFlag = 0;
				foreach($aFlags as $sFlag) {
					$sFlag = \trim($sFlag);
					$nFlag |= $vFlags[\strtoupper($sFlag)];
				}

				$sEncoding = (isset($vOptions["encoding"])) ? $vOptions["encoding"] : "UTF-8";
				$mNewValue = \htmlentities($mValue, $nFlag, $sEncoding);
				break;

			case "regex":
				if(isset($vOptions["pattern"])) {
					if(\preg_match("/".$vOptions["pattern"]."/s", $mValue)) {
						$mNewValue = $mValue;
					}
				}
				break;

			case "base64file":
				$sType = "base64";
				$mValue = \substr($mValue, \strpos($mValue, ",")+1);
			case "base64":
			case "color":
			case "date":
			case "datetime":
			case "email":
			case "filename":
			case "imya":
			case "ipv4":
			case "ipv6":
			case "time":
			case "url":
				if(\preg_match("/^".$this->vRegex[$sType]."$/s", $mValue)) {
					$mNewValue = $mValue;
				}
				break;

			case "int":
				$mNewValue = \preg_replace("/^[^0-9]+$/s", "", $mValue);
				$mNewValue = (int)$mNewValue;
				break;

			case "coords":
				$mNewValue = \preg_replace("/^[^0-9\.\-](,|;)[^0-9\.\-]+$/s", "", $mValue);
				break;

			case "number":
				$mNewValue = \preg_replace("/^[^0-9\.\,]+$/s", "", $mValue);
				if(!\is_numeric($mNewValue)) { $mNewValue = 0; }
				$mNewValue *= 1;
				break;

			case "alpha":
				$aParams["types"] = ["ABC"=>true, "ABU"=>true];
				$aParams["groups"] = [];
				$aParams["chars"] = [9=>true,10=>true,13=>true,32=>true];
				$mNewValue = $this->ClearCharacters($mValue, $aParams);
				break;

			case "string":
				$aParams["types"] = ["ABC"=>true, "ABU"=>true, "NUM"=>true];
				$aParams["groups"] = [];
				$aParams["chars"] = [9=>true,10=>true,13=>true,32=>true];
				$mNewValue = $this->ClearCharacters($mValue, $aParams);
				break;

			case "text":
				$aParams["types"] = ["ABC"=>true, "ABU"=>true, "SYL"=>true, "NUM"=>true, "SYM"=>true];
				$aParams["groups"] = ["BASIC_LATIN_SYMBOLS"=>true];
				$aParams["chars"] = [9=>true,10=>true,13=>true,32=>true];
				$mNewValue = $this->ClearCharacters($mValue, $aParams);
				break;

			case "symbols":
				$aParams["types"] = ["SYM"=>true];
				$aParams["groups"] = [];
				$aParams["chars"] = [9=>true,10=>true,13=>true,32=>true];
				$mNewValue = $this->ClearCharacters($mValue, $aParams, true);
				break;
		}

		return $mNewValue;
	}

	private function ClearCharacters($sString, $aParams=null, $bInvert=false) {
		$sNewString = $sInvertString = $sChar = "";
		$nString = \strlen($sString);
		for($x=0; $x<$nString; $x++) {
			if((\ord($sString[$x])&0xC0)!=0x80) {
				if(\strlen($sChar)) {
					$aIs = self::call("unicode")->ischr($sChar);
					if(isset($aParams["types"][$aIs[0]]) || isset($aParams["groups"][$aIs[1]]) || isset($aParams["chars"][$aIs[2]])) {
						$sNewString .= $sChar;
					} else {
						$sInvertString .= $sChar;
					}
					$sChar = "";
				}
				$sChar .= $sString[$x];
			} else {
				$sChar .= $sString[$x];
			}
		}

		$aIs = self::call("unicode")->ischr($sChar);

		if(isset($aParams["types"][$aIs[0]]) || isset($aParams["groups"][$aIs[1]]) || isset($aParams["chars"][$aIs[2]])) {
			$sNewString .= $sChar;
		} else {
			$sInvertString .= $sChar;
		}

		return ($bInvert) ? $sInvertString : $sNewString;
	}
}

?>