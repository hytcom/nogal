<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
*/
namespace nogal;
class nglTrunk extends nglRoot {

	private $sNogalInfo = "https://raw.githubusercontent.com/hytcom/nogal/master/docs/";
	private $sWikiRepo  = "https://raw.githubusercontent.com/hytcom/wiki/master/nogal/docs/";

	final public function __construct() {
		if(self::$bLoadAllowed===false) {
			\trigger_error("Can't instantiate outside of the «nogal» environment", E_USER_ERROR);
			die();
		}

		if(\method_exists($this, "__builder__")) {
			$this->__builder__(\func_get_args());
		}

		$this->__errorMode__();
	}

	final public function __configFile__() {
		$aExplained = $aData = [];
		if(\file_exists(NGL_PATH_FRAMEWORK.NGL_DIR_SLASH."docs".NGL_DIR_SLASH.$this->object.".info")) {
			if(($sConfig = \file_get_contents(NGL_PATH_FRAMEWORK.NGL_DIR_SLASH."docs".NGL_DIR_SLASH.$this->object.".info"))) {
				$aData = self::parseConfigString($sConfig, true, true);
				if(isset($aData["documentation"])) { $aExplained["documentation"]["url"] = $aData["documentation"]["url"]; }
			}
		} else {
			if(($sConfig = @\file_get_contents($this->sNogalInfo.$this->object.".info"))) {
				$aData = self::parseConfigString($sConfig, true, true);
				if(isset($aData["documentation"])) { $aExplained["documentation"]["url"] = $aData["documentation"]["url"]; }
			}
		}

		if(\method_exists($this, "__defaults__")) { $aDefault = $this->__defaults__(); }
		foreach($aData as $sSection => $aSectionValues) {
			$aExplained[$sSection] = [];
			if($sSection=="arguments" && isset($aDefault)) {
				foreach($aDefault as $sArgument => $mValue) {
					$aExplained[$sSection][$sArgument] = [$mValue];
					if(isset($aData, $aData[$sSection][$sArgument])) { $aExplained[$sSection][$sArgument][] = $aData[$sSection][$sArgument]; }
				}
			} else {
				foreach($aSectionValues as $sArgument => $mValue) {
					$aExplained[$sSection][$sArgument] = $mValue;
				}
			}
		}

		// config file
		$sContent = "";
		foreach($aExplained as $sSection => $aValues) {
			$sContent .= "[".$sSection."]\n";
			foreach($aValues as $sKey => $mValue) {
				$sValue = (\is_array($mValue)) ? $mValue[0] : $mValue;
				if($sKey!="_help") {
					switch(true) {
						case $sValue===null: $sArgument = $sKey." = null"; break;
						case $sValue===false: $sArgument = $sKey." = false"; break;
						case $sValue===true: $sArgument = $sKey." = true"; break;
						case \is_numeric($sValue): $sArgument = $sKey." = ".$sValue; break;
						default:
							if(\strstr($sValue, '"')!==false) {
								$sArgument = $sKey." = '".$sValue."'";
							} else {
								$sArgument = $sKey." = \"".$sValue."\"";
							}
						break;
					}

					if(\is_array($mValue) && isset($mValue[1])) { $sContent .= ";".\implode(\chr(10).";", \explode(\chr(10), $mValue[1]))."\n"; }
					$sContent .= $sArgument."\n";
					if($sSection=="arguments") { $sContent .= "\n"; }
				} else {
					$sContent .= ";".\implode(\chr(10).";", \explode(\chr(10), $sValue))."\n";
				}
			}

			$sContent .= "\n";
		}

		if(empty($sContent)) {
			$this->__errorMode__("die");
			$this->__errorShowSource__(false);
			\trigger_error("Can't get config file data: ".$this->sNogalInfo.$this->object.".info", E_USER_ERROR);
		}

		if(\is_writeable(NGL_PATH_CONF) && !\file_exists(NGL_PATH_CONF.NGL_DIR_SLASH.$this->object.".conf")) {
			\file_put_contents(NGL_PATH_CONF.NGL_DIR_SLASH.$this->object.".conf", $sContent);
			return true;
		} else if(\is_writeable(NGL_PATH_TMP) && !\file_exists(NGL_PATH_TMP.NGL_DIR_SLASH.$this->object.".conf")) {
			\file_put_contents(NGL_PATH_TMP.NGL_DIR_SLASH.$this->object.".conf", $sContent);
			return "The config file has been created in: ".NGL_PATH_TMP."\nMove the file to: ".NGL_PATH_CONF."\n";
		} else {
			return $sContent;
		}
	}

	final public function __configFileValue__($sKey, $sValue=null) {
		$bUpdated = false;
		if(\file_exists(NGL_PATH_CONF.NGL_DIR_SLASH.$this->object.".conf")) {
			if(\is_numeric($sValue) || \in_array($sValue,["false","true","null",false,true,null],true)) {
				$sValue = \str_replace(["'", '"'], "", \trim($sValue));
			} else if(\is_string($sValue)) {
				$sValue = '"'.$sValue.'"';
			}

			$aSections = self::parseConfigFileSections(NGL_PATH_CONF.NGL_DIR_SLASH.$this->object.".conf");
			$aConfig = self::parseConfigFile(NGL_PATH_CONF.NGL_DIR_SLASH.$this->object.".conf", true);

			$aKey = !\strpos($sKey, ".") ? ["arguments", $sKey] : \explode(".", $sKey, 2);
			if(\array_key_exists($aKey[0], $aConfig)) {
				$bUpdated = true;
				if(\array_key_exists($aKey[1], $aConfig[$aKey[0]])) {
					$aSections[$aKey[0]] = \preg_replace("/".\preg_quote($aKey[1], "/")." *=(.*?)\n/is", $aKey[1]." = ".$sValue."\n", $aSections[$aKey[0]]);
				} else {
					$aSections[$aKey[0]] .= $aKey[1]." = ".$sValue."\n";
				}
			}

			if($bUpdated) {
				$sContent = "";
				foreach($aSections as $sSection => $sCode) {
					if($sContent!="") { $sContent .= "\n"; }
					$sContent .= "[".$sSection."]\n".$sCode;
				}

				if(\is_writeable(NGL_PATH_CONF)) {
					\file_put_contents(NGL_PATH_CONF.NGL_DIR_SLASH.$this->object.".conf", $sContent);
					return true;
				} else if(\is_writeable(NGL_PATH_TMP)) {
					\file_put_contents(NGL_PATH_TMP.NGL_DIR_SLASH.$this->object.".conf", $sContent);
					return "The config file has been created in: ".NGL_PATH_TMP."\nMove the file to: ".NGL_PATH_CONF."\n";
				} else {
					return $sContent;
				}
			}

			return "Invalid key '".$sKey."'\n";
		} else {
			return "The config file doesn't exists\n";
		}
	}

	final public function __errorMode__($sMode=NGL_HANDLING_ERRORS_MODE) {
		return self::errorMode($this->object, $sMode);
	}

	final public function __errorShowSource__($bShow=NGL_HANDLING_ERRORS_BACKTRACE) {
		return self::errorShowSource($bShow);
	}

	final public function __me__($bArray=false) {
		if(!$bArray) {
			$me = new \stdClass();
			$me->name = $this->me;
			$me->class = $this->class;
			$me->object = $this->object;
			return $me;
		} else {
			$vMe = [];
			$vMe[0] 		= $this->me;
			$vMe[1] 		= $this->class;
			$vMe[2] 		= $this->object;
			$vMe["name"]	= $this->me;
			$vMe["class"]	= $this->class;
			$vMe["object"]	= $this->object;
			return $vMe;
		}
	}

	final public function __help__() {
		$sFileName = $this->sWikiRepo.$this->object.".md";
		if(!$sHelp = @\file_get_contents($sFileName)) {
			$this->__errorMode__("die");
			$this->__errorShowSource__(false);
			\trigger_error("Can't get help file data: ".$sFileName, E_USER_ERROR);
		}
		return $sHelp;
	}
}

?>