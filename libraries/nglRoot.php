<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
*/
namespace nogal;
class nglRoot {

	const NGL_ME									= "nogal";

	private static			$fn						= null;
	private static			$session				= null;
	private static			$shift					= null;
	private static			$nut					= null;
	private static			$tutor					= null;
	private static			$unicode				= null;
	private static			$val					= null;
	private static			$var					= null;

	private static			$bNogalDebug			= false;
	private static			$nStarTime				= null;
	private static			$vErrorCodes			= [];
	private static			$vLastError				= [];
	private static			$bErrorReport			= true;
	private static			$bErrorReportPrevius	= true;
	private static			$aErrorModes			= [];
	private static			$bErrorForceReturn		= false;
	private static			$bErrorShowSource		= true;
	private static			$vCurrentPath			= [];
	private static			$vCoreLibs				= [];
	private static			$vLibraries				= [];
	private static			$vFeederInits			= [];
	protected static		$aObjects				= [];
	protected static		$bLoadAllowed			= false;
	private static 			$aNuts					= [];
	protected static 		$aNutsLoaded			= [];
	private static 			$aTutors				= [];
	protected static 		$aTutorsLoaded			= [];
	protected static 		$sLastEval				= "";
	private static			$aObjectsByClass		= [];
	private static			$vPaths					= [];
	private static			$sLastCalled			= null;
	private static			$vLastOf				= [];


	// METODOS PUBLICOS --------------------------------------------------------
	/** FUNCTION {
		__construct: Constructor
	**/
	public function __construct($vLibraries, $vGraftsLibraries) {
		self::$nStarTime = \microtime(true);

		// paths
		self::setPath("libraries");
		self::setPath("grafts");

		// librerias
		self::$vCoreLibs = [
			"fn" => ["nglFn", true],
			"nut" => ["nglNut", true],
			"sess" => ["nglSession", true],
			"shift" => ["nglShift", true],
			"sysvar" => ["nglSystemVars", true],
			"tutor" => ["nglTutor", true],
			"unicode" => ["nglUnicode", true],
			"validate" => ["nglValidate", true]
		];
		self::$vLibraries = \array_merge(self::$vCoreLibs,  $vLibraries, $vGraftsLibraries);

		// kernel
		self::$bLoadAllowed = true;
		require_once(self::$vPaths["libraries"]."nglTrunk.php");
		require_once(self::$vPaths["libraries"]."nglBranch.php");
		require_once(self::$vPaths["libraries"]."nglFeeder.php");
		require_once(self::$vPaths["libraries"]."nglScion.php");

		require_once(self::$vPaths["libraries"]."nglFn.feeder.php");
		self::$fn = new nglFn();

		require_once(self::$vPaths["libraries"]."nglTutor.feeder.php");
		self::$tutor = new nglTutor();

		require_once(self::$vPaths["libraries"]."nglNut.feeder.php");
		self::$nut = new nglNut();

		self::$bLoadAllowed = false;
	}

	/** FUNCTION {
		__invoke : {
			"description" : "alias de call",
		}
	**/
	public function __invoke() {
		$aArguments = \func_get_args();
		if(!\count($aArguments)) { $aArguments = []; }
		return \call_user_func_array(["self", "call"], $aArguments);
	}

	/** FUNCTION {
		__toString : {
			"description" : "retorna el nombre del objeto",
			"return" : "nombre del objeto"
		}
	**/
	public function __toString() {
		return self::NGL_ME;
	}

	/** FUNCTION {
		call : {
			"description" : "
				invoca al objeto $sObjectName con los argumentos $mArguments.
				$sObjectName puede ser un nombre de objeto o una instancia del mismo.
				cuando la instancia no exista se creará una copia del objeto.
			",
			"params" : {
				"$sObjectName" : "
					nombre del objeto. Formatos:
						nombre_del_objeto,
						nombre_del_objeto.nombre_de_instancia
				",
				"$mArguments" : "argumentos adicionales pasados al método __init__"
				"$bRequireOnly" : "cuando es true, solo incluye la clase"
			},
			"return" : "objeto o false"
		}
	**/
	final public static function call($sObjectName=null, $aArguments=[], $bRequireOnly=false) {
		if($sObjectName===null) { return self::$fn; }
		if(!\is_array($aArguments) || !\count($aArguments)) { $aArguments = null; }

		if(\strpos($sObjectName, "|")!==false) {
			$aObjectConf	= \explode("|", $sObjectName, 2);
			$sObjectName	= $aObjectConf[0];
			$sConfFile		= $aObjectConf[1];
		}

		$sObjectName = self::objectName($sObjectName);
		$aObjectName = \explode(".", $sObjectName, 2);
		self::$sLastCalled = $aObjectName[0];

		$bFeeder = true;
		if(isset(self::$vLibraries[$aObjectName[0]])) {
			$bFeeder = self::$vLibraries[$aObjectName[0]][1];
		}

		if(!$bFeeder && $aObjectName[0]!="nut" && $aObjectName[0]!="tutor") {
			if((\is_array($aObjectName) && \count($aObjectName)==1) || (isset($aObjectName[1]) && $aObjectName[1]==="")) {
				$tmp = self::call()->unique();
				$sObjectName .= ".".\strtolower($tmp);
			}
			$sObjectType = $aObjectName[0];
		} else {
			if($aObjectName[0]=="nut" || $aObjectName[0]=="tutor") { $sObjectName = $aObjectName[0]; }
			switch($sObjectName) {
				case "fn"		: 	return self::returnFeeder(self::$fn);
				case "nut"		: 	return self::$nut->load($aObjectName[1], $aArguments);
				case "tutor"	:	if(NGL_READONLY) { self::errorHTTP(1000); } else { return self::$tutor->load($aObjectName[1], $aArguments); }
				default			:	$sObjectType = $sObjectName;
			}
		}

		if(!isset(self::$aObjects[$sObjectName])) {
			if(isset(self::$vLibraries[$sObjectType])) {
				$sClassName = self::$vLibraries[$sObjectType][0];
				self::loadClass($sClassName, $bFeeder);
				if(!$bRequireOnly) {
					if(!isset($sConfFile)) { $sConfFile = $sObjectType; }
					self::loadObject($sClassName, $bFeeder, $sConfFile, $sObjectName, $aArguments);
				}
			} else {
				self::errorMessage(self::NGL_ME, "1002", $sObjectType, "die");
			}
		}

		if(isset(self::$aObjects[$sObjectName])) {
			self::$vLastOf[$sObjectType] = self::$aObjects[$sObjectName];
			return self::$aObjects[$sObjectName];
		}
		return false;
	}

	final public static function requirer() {
		$aBacktrace = \debug_backtrace(false);
		foreach($aBacktrace as $aFile) {
			if(
				$aFile["function"]=="require" ||
				$aFile["function"]=="require_once" ||
				$aFile["function"]=="include" ||
				$aFile["function"]=="include_once"
			) {
				return $aFile["file"];
			}
		}

		return false;
	}

	final public static function EvalCode($sCode) {
		self::$sLastEval = \base64_encode($sCode);
		return $sCode;
	}

	final private static function LastEvalCode() {
		return self::$sLastEval;
	}

	final public static function returnFeeder($object) {
		$sClass = \get_class($object);
		if(\method_exists($object, "__init__") && !isset(self::$vFeederInits[$sClass])) {
			$object->__init__();
			self::$vFeederInits[$sClass] = true;
		}
		return $object;
	}

	final public static function tutor($sTutorName, $sClassName=null, $aMethods=null) {
		if($sClassName!==null) { self::$aTutors[$sTutorName] = [$sClassName,$aMethods]; }
		return (isset(self::$aTutors[$sTutorName])) ? self::$aTutors[$sTutorName] : null;
	}

	final public static function nut($sNutName, $sClassName=null, $aMethods=null) {
		if($sClassName!==null) { self::$aNuts[$sNutName] = [$sClassName,$aMethods]; }
		return (isset(self::$aNuts[$sNutName])) ? self::$aNuts[$sNutName] : null;
	}

	final public static function absolutePath($sPath, $sDirSlash=DIRECTORY_SEPARATOR) {
		$sPath = \str_replace(['/', '\\'], $sDirSlash, $sPath);
		$aPath = \explode($sDirSlash, $sPath);
		$aPath = \array_filter($aPath, "strlen");
		$aAbsolutes = [];
		foreach($aPath as $sPart) {
			if("."==$sPart) { continue; }
			if(".."==$sPart) {
				\array_pop($aAbsolutes);
			} else {
				$aAbsolutes[] = $sPart;
			}
		}

		return DIRECTORY_SEPARATOR.\implode($sDirSlash, $aAbsolutes);
	}

	// verifica si $mPath o alguno de sus indices (si es array) es parte de NGL_PATH_CURRENT
	// $sPath debe terminar en /
	final public static function inCurrentPath($mPath) {
		if(!\is_array($mPath)) { $mPath = [$mPath]; }
		\usort($mPath, function($a, $b) { return \strlen($b) - \strlen($a); });
		$nLength = \strlen(NGL_PATH_CURRENT);
		foreach($mPath as $sPath) {
			if(NGL_PATH_CURRENT===$sPath) {
				return $sPath;
			} else if(\strlen($sPath)>1 && \substr($sPath, -1, 1)=="/") {
				if(\substr(NGL_PATH_CURRENT, 0, \strlen($sPath))===$sPath) {
					return $sPath;
				}
			} else if(\substr($sPath, -1, 1)=="*") {
				if(\substr($sPath, 0, -1)===\substr(NGL_PATH_CURRENT, 0, \strlen($sPath)-1)) {
					return $sPath;
				}
			}
		}

		return false;
	}

	final public static function constants() {
		$aConstants = [];
		$aGetConstants = \get_defined_constants(true);
		foreach($aGetConstants["user"] as $sName => $mConstant) {
			if(\substr($sName,0,4)=="NGL_") {
				if(\is_array($mConstant)) {
					\array_walk_recursive($mConstant, function($i, $v) {
						\addcslashes($v, "\t\r\n");
					});
				}
				$aConstants[$sName] = $mConstant;
			}
		}

		\ksort($aConstants);
		return $aConstants;
	}

	final public static function currentPath($sDirSlash=DIRECTORY_SEPARATOR) {
		if(\is_array(self::$vCurrentPath) && \count(self::$vCurrentPath)) { return self::$vCurrentPath; }

		// document_root
		$sDocumentRoot = \str_replace("\\", "/", NGL_DOCUMENT_ROOT);
		$aDocumentRoot = \explode("/", $sDocumentRoot);
		if(end($aDocumentRoot)=="") { \array_pop($aDocumentRoot); }
		$sDocumentRoot = \implode($sDirSlash, $aDocumentRoot);

		// php_self
		if(\array_key_exists("REDIRECT_SCRIPT_URL", $_SERVER)) {
			$sPHPSelf = $_SERVER["REDIRECT_SCRIPT_URL"];
		} else if(\array_key_exists("REDIRECT_URL", $_SERVER)) {
			$sPHPSelf = $_SERVER["REDIRECT_URL"];
		} else {
			$sPHPSelf = $_SERVER["PHP_SELF"];
		}

		if($sPHPSelf=="/") { $sPHPSelf = "/index"; }
		$sPHPSelf = \str_replace("\\", "/", $sPHPSelf);

		$aPath = \explode("/", $sPHPSelf);
		foreach($aPath as $nIndex => $sPart) {
			if($sPart==="") { unset($aPath[$nIndex]); }
		}
		$sPHPSelf = \implode($sDirSlash, $aPath);
		$vPHPSelf = \pathinfo($sPHPSelf);

		$vCurrent = [];
		$vCurrent["basename"] 	= $vPHPSelf["basename"];
		$vCurrent["path"]		= self::absolutePath($sDocumentRoot.$sDirSlash.$vPHPSelf["dirname"], $sDirSlash);
		$vCurrent["fullpath"]	= $vCurrent["path"].$sDirSlash.$vCurrent["basename"];
		$vCurrent["dirname"]	= ($vPHPSelf["dirname"]!=".") ? $vPHPSelf["dirname"] : "";

		$aBasename = \explode(".", $vCurrent["basename"]);
		if(\is_array($aBasename) && \count($aBasename)>1) {
			$vCurrent["extension"] = \array_pop($aBasename);
			$vCurrent["filename"] = \implode(".", $aBasename);
		} else {
			$vCurrent["extension"] = "";
			$vCurrent["filename"] = $vCurrent["basename"];
		}

		$vCurrent["query_string"] = (\array_key_exists("QUERY_STRING", $_SERVER)) ? $_SERVER["QUERY_STRING"] : "";

		$NGL_URL = \constant("NGL_URL");
		if(!empty($NGL_URL)) {
			$vURL = \parse_url(NGL_URL);
			$vCurrent["scheme"] = $vURL["scheme"];
			$vCurrent["host"] = $vURL["host"];
			$vCurrent["port"] = (isset($vURL["port"])) ? $vURL["port"] : "";
			$vCurrent["urlroot"] = $vURL["scheme"]."://".$vURL["host"];
			if(!empty($vURL["port"])) { $vCurrent["urlroot"] .= ":".$vURL["port"]; }
			$vCurrent["urldirname"] = (!empty($vCurrent["dirname"])) ? \str_replace("\\", "/", $vCurrent["dirname"])."/" : "";
			$vCurrent["url"] = $vCurrent["urldirname"].$vCurrent["basename"].(($vCurrent["query_string"]!="") ? "?".$vCurrent["query_string"] : "");
			$vCurrent["urlpath"] = $vCurrent["urldirname"].$vCurrent["basename"];
			$vCurrent["fullurl"] = $vCurrent["urlroot"]."/".$vCurrent["url"];
			$vCurrent["fullurlpath"] = $vCurrent["urlroot"]."/".$vCurrent["urlpath"];
		}

		return $vCurrent;
	}

	final public static function defineConstant($sConstantName, $sConstantValue=null) {
		if(!\defined($sConstantName)) { \define($sConstantName, $sConstantValue); }
		return \constant($sConstantName);
	}

	final public static function gardensplace($sURL=null, $sGround=null) {
		if($sURL===null) { $sURL = NGL_URL; }
		$sURL = \parse_url($sURL, PHP_URL_PATH);
		$sFile = \str_replace(\parse_url(NGL_URL, PHP_URL_PATH), "", $sURL);
		$sFile = \str_replace("\\", "/", $sFile);

		if(!empty($sFile) && $sFile[0]!="/") { $sFile = "/".$sFile; }
		$sFile = self::call()->clearPath($sFile, false, "/");
		$aParts = \explode("/", $sFile, 3);

		// casos especiales
		if(isset($aParts[1])) {
			if($aParts[1]=="tutor" && isset($aParts[2])) {
				if(\file_exists(NGL_PATH_GARDEN."/tutor.php")) {
					return [NGL_PATH_GARDEN."/tutor.php", $aParts[2], false];
				} else {
					return [NGL_PATH_FRAMEWORK."/tutor.php", $aParts[2], false];
				}
			} else if($aParts[1]=="nut" && isset($aParts[2])) {
				if(\file_exists(NGL_PATH_GARDEN."/nut.php")) {
					return [NGL_PATH_GARDEN."/nut.php", $aParts[2], false];
				} else {
					return [NGL_PATH_FRAMEWORK."/nut.php", $aParts[2], false];
				}
			}
		}

		// caso normal
		if($sGround===null) { $sGround = NGL_PATH_CROWN; }
		$sFilePath = $sGround.NGL_DIR_SLASH.$sFile;
		$sFile = \realpath($sFilePath);

		$bAutoIndex = false;
		if(\file_exists($sFile) && !\is_dir($sFile)) {
			return [$sFile, null, $bAutoIndex];
		} else if(is_dir($sFile)) {
			if($sURL[\strlen($sURL)-1]!="/") {
				\header("location: ".$sURL."/");
				exit();
			}
			$bAutoIndex = true;
			$sFile .= "/index.php";
			$sFile = \realpath($sFile);
			if(\file_exists($sFile)) { return [$sFile, null, $bAutoIndex]; }
			$sFilePath .= "/";
		} else {
			$sFile = $sFilePath.".php";
			$sFile = \realpath($sFile);
			if(\file_exists($sFile)) {
				return [$sFile, null, $bAutoIndex];
			}
		}

		// error
		$sFilePath = self::call()->clearPath($sFilePath, ($sURL[\strlen($sURL)-1]=="/"), NGL_DIR_SLASH);
		return [false, $sFilePath, $bAutoIndex];
	}

	final public static function exceptionsHandler($exception) {
		self::errorsHandler($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
	}

	final public static function errorsHandler($nError, $sMessage, $sFile, $nLine) {
		$aErrors = [];
		if(\defined("E_ERROR")) { 				$aErrors[E_ERROR]		 		= "Error"; }
		if(\defined("E_WARNING")) { 			$aErrors[E_WARNING]				= "Warning"; }
		if(\defined("E_PARSE")) { 				$aErrors[E_PARSE]				= "Parsing Error"; }
		if(\defined("E_NOTICE")) { 				$aErrors[E_NOTICE]				= "Notice"; }
		if(\defined("E_CORE_ERROR")) { 			$aErrors[E_CORE_ERROR]			= "Core Error"; }
		if(\defined("E_CORE_WARNING")) { 		$aErrors[E_CORE_WARNING]		= "Core Warning"; }
		if(\defined("E_COMPILE_ERROR")) { 		$aErrors[E_COMPILE_ERROR]		= "Compile Error"; }
		if(\defined("E_COMPILE_WARNING")) { 	$aErrors[E_COMPILE_WARNING]		= "Compile Warning"; }
		if(\defined("E_USER_ERROR")) { 			$aErrors[E_USER_ERROR]			= "User Error"; }
		if(\defined("E_USER_WARNING")) {		$aErrors[E_USER_WARNING]		= "User Warning"; }
		if(\defined("E_USER_NOTICE")) { 		$aErrors[E_USER_NOTICE]			= "User Notice"; }
		if(\defined("E_STRICT")) { 				$aErrors[E_STRICT]				= "Runtime Notice"; }
		if(\defined("E_RECOVERABLE_ERROR")) {	$aErrors[E_RECOVERABLE_ERROR]	= "Catchable Fatal Error"; }
		if(\defined("E_DEPRECATED")) { 			$aErrors[E_DEPRECATED]			= "Runtime Notice, this code not work in future versions"; }

		$sType = \substr($sMessage, 0,\strpos($sMessage, ","));
		$sMessage = \str_replace("[<a href='function", "[<a target='_blank' href='http://php.net/function", $sMessage);

		$bIgnoreError = false;
		switch($nError) {
			case E_NOTICE:
				if(\strpos($sMessage, "undefined constant")) {
					$bIgnoreError = true;
				}
				break;

			case E_WARNING:
				if(\strpos($sMessage, "headers already sent")) {
					$bIgnoreError = true;
				}

			case E_DEPRECATED:
				// $bIgnoreError = true;
				break;

			case E_STRICT:
				if($sMessage=="Creating default object from empty value") {
				}
				break;

			case E_PARSE:
			case E_USER_ERROR:
				break;

			default:
				if(isset($aErrors[$nError])) {
					$sMessage = "Internal Framework Error (".$aErrors[$nError]."), Please report to admin";
				}
		}

		self::$vLastError = [
			"object" => "PHP",
			"type" => !empty($aErrors[$nError]) ? $aErrors[$nError] : $sType,
			"code" => "",
			"file" => $sFile,
			"line" => $nLine,
			"error" => $sMessage,
			"details" => ""
		];

		if(self::$bErrorReport) {
			if(\strpos($sFile, "eval()'d")) {
				self::$vLastError["details"] = "\nEVAL-CODE:base64[".self::LastEvalCode()."]\n";
			}

			if(\error_reporting()) {
				if(!$bIgnoreError) {
					try {
						self::errorMessage();
					} catch(Exception $e){
						throw new \Exception(self::errorMessage());
					}
				}
			}
		}
	}

	final public static function errorGetLast() {
		return self::$vLastError;
	}

	final public static function errorClearLast() {
		self::$vLastError = [];
	}

	final public static function errorReporting($bReport) {
		self::$bErrorReportPrevius = self::$bErrorReport;
		if($bReport!==null) { self::$bErrorReport = $bReport; }
		return self::$bErrorReport;
	}

	final public static function errorReportingRestore() {
		self::$bErrorReport = self::$bErrorReportPrevius;
		return self::$bErrorReport;
	}

	// retorna el modo previo
	final public static function errorMode($sObject, $sMode=null) {
		$sCurrent = (isset(self::$aErrorModes[$sObject])) ? self::$aErrorModes[$sObject] : NGL_HANDLING_ERRORS_MODE;
		if($sMode!==null) {
			$sMode = \strtolower($sMode);
			if(!\in_array($sMode, ["boolean","code","die","log","print","return"])) { $sMode = NGL_HANDLING_ERRORS_MODE; }
			self::$aErrorModes[$sObject] = $sMode;
		}
		return $sCurrent;
	}

	final public static function errorShowSource($bShow) {
		self::$bErrorShowSource = ($bShow===true) ? true : false;
	}

	final public static function errorForceReturn($bForce) {
		self::$bErrorForceReturn = ($bForce===true) ? true : false;
	}

	final public static function errorHTTP($nCode, $sDetails="") {
		$sError = !empty(self::call("sysvar")->HTTP_CODES[$nCode]) ? self::call("sysvar")->HTTP_CODES[$nCode] : "Undefined Error Code";
		if($nCode===1000) {
			$sError = "NOGAL Read only Mode / Tutors Off";
			\header("HTTP/1.0 503 Not Service Unavailable", true, 503);
		} else {
			\header("HTTP/1.0 ".$nCode." ".$sError, true, $nCode);
		}

		die(self::errorPage(["error"=>$sError, "code"=>$nCode, "details"=>$sDetails]));
	}

	final public static function errorPage($aArguments) {
		if(\is_readable(NGL_PATH_CONF.NGL_DIR_SLASH."errorpage.html")) {
			$sMessage = @\file_get_contents(NGL_PATH_CONF.NGL_DIR_SLASH."errorpage.html");
		} else {
			$sMessage = @\file_get_contents(NGL_PATH_FRAMEWORK.NGL_DIR_SLASH."assets".NGL_DIR_SLASH."errorpage.html");
		}

		if(NGL_TERMINAL || $sMessage===false || \strtolower(NGL_HANDLING_ERRORS_FORMAT)=="text") {
			$sMessage = NGL_PROJECT." ERROR ";
			if(!empty($aArguments["object"])) { $sMessage .= $aArguments["object"]; }
			if(!empty($aArguments["code"])) { $sMessage .= "#".$aArguments["code"]; }
			if(!empty($aArguments["error"])) { $sMessage .= " - ".$aArguments["error"]; }
			if(!empty($aArguments["details"])) { $sMessage .= " ".$aArguments["details"]; }
			if((empty($aArguments["type"]) || $aArguments["type"]!="NOGAL" || self::$bNogalDebug) && self::$bErrorShowSource) {
				if(!empty($aArguments["file"])) { $sMessage .= "\nOn ".$aArguments["file"]; }
				if(!empty($aArguments["line"])) { $sMessage .= " line ".$aArguments["line"]; }
				if(!empty($aArguments["backtrace"])) { $sMessage .= "\n".$aArguments["backtrace"]; }
			}
		} else {
			// file source
			header("Content-Type: text/html", true);
			$aArguments["source"] = "";
			if(!self::$bErrorShowSource) { $aArguments["file"] = null; }
			if((empty($aArguments["type"]) || $aArguments["type"]!="NOGAL" || self::$bNogalDebug) && self::$bErrorShowSource) {
				if(!empty($aArguments["file"]) && \is_readable($aArguments["file"])) {
					$aSource = \file($aArguments["file"]);
					$nPadding = 5;
					$nIni = ($aArguments["line"] >= $nPadding) ? ($aArguments["line"] - $nPadding - 1) : 0;
					$nEnd = ($nIni+$nPadding < count($aSource)) ? $aArguments["line"] + $nPadding : 0;
					$aSource = \array_slice($aSource, $nIni, ($nEnd-$nIni), true);
					$sSource = "";
					foreach($aSource as $nLine => $sLineCode) {
						$sSource .= ($nLine+1)."\t".\htmlentities($sLineCode);
					}
					$aArguments["source"] = $sSource;
				}
			}

			$sMessage = \str_replace("{%TITLE%}", NGL_PROJECT, $sMessage);
			foreach($aArguments as $sIndex => $sValue) {
				$sMessage = \str_replace("{%".\strtoupper($sIndex)."%}", $sValue, $sMessage);
			}
			$sMessage = \preg_replace("/\{%[a-z0-9_]+%\}/is", "", $sMessage);
		}

		return $sMessage;
	}

	final public static function errorSetCodes($sObject, $aCodes) {
		self::$vErrorCodes[$sObject] = $aCodes;
	}

	final public static function errorCodes($sObject, $nCode) {
		if(!isset(self::$vErrorCodes[$sObject])) {
			$sErrorFile = null;
			if(\file_exists(NGL_PATH_CONF.NGL_DIR_SLASH.$sObject.".conf")) {
				$sErrorFile = NGL_PATH_CONF.NGL_DIR_SLASH.$sObject.".conf";
			} else if(\file_exists(NGL_PATH_FRAMEWORK.NGL_DIR_SLASH."docs".NGL_DIR_SLASH.$sObject.".info")) {
				$sErrorFile = NGL_PATH_FRAMEWORK.NGL_DIR_SLASH."docs".NGL_DIR_SLASH.$sObject.".info";
			}

			if($sErrorFile!==null) {
				$aConfig = self::parseConfigString(\file_get_contents($sErrorFile), true);
				if(isset($aConfig["errors"])) { self::errorSetCodes($sObject, $aConfig["errors"]); }
			}
		}

		return (isset(self::$vErrorCodes[$sObject], self::$vErrorCodes[$sObject][$nCode])) ? self::$vErrorCodes[$sObject][$nCode] : $nCode;
	}

	final public static function errorMessage($sObject=null, $sCode=null, $sDetails=null, $sMode=null) {
		$aBacktrace = \debug_backtrace();
		$sCurrentFile = $aBacktrace[0]["file"];
		$nCurrentLine = $aBacktrace[0]["line"];

		$sError = "";
		$sType = "NOGAL";
		$bLast = false;
		if($sObject===null) {
			$bLast = true;
			$aLast = self::errorGetLast();
			if(\is_array($aLast) && \count($aLast)) {
				$sObject = $aLast["object"];
				$sType = $aLast["type"];
				$sCode = $aLast["code"];
				$sError = $aLast["error"];
				$sCurrentFile = $aLast["file"];
				$nCurrentLine = $aLast["line"];
				if(isset($aLast["details"])) { $sDetails = $aLast["details"]; }
			}
		}

		$sTitle = ($sCode!==null) ? $sObject."#".$sCode : $sObject;
		$sLowerObject = \str_replace("@","",\strtolower($sObject));
		if(!$bLast) { $sError = self::errorCodes($sLowerObject, $sCode); }

		$sMsg = (!empty($sError) && $sError!==$sCode) ? $sTitle." - ".$sError : $sTitle;

		// log
		$vCurrentPath = self::currentPath();
		$sErrRow  = \date("Y-m-d H:i:s");
		$sErrRow .= "\t".(isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "localhost");
		$sErrRow .= "\t".$vCurrentPath["fullpath"];
		if(\defined("NGL_GARDENS_PLACE") && !empty(NGL_GARDENS_PLACE["uproot"])) { $sErrRow .= "->".NGL_GARDENS_PLACE["uproot"]; }
		$sErrRow .= "\t".$sCurrentFile." (".$nCurrentLine.")";
		$sErrRow .= "\t[ ".\strip_tags($sMsg)." ]";
		self::log("errors.log", $sErrRow."\n");

		if($sMode===null) { $sMode = self::errorMode($sLowerObject); }
		$sMode = \strtolower($sMode);
		if($sMode=="log") { return false; }

		// datos del error
		$sBacktrace = "";
		if(($sType!="NOGAL" || self::$bNogalDebug) && self::$bErrorShowSource) {
			ob_start();
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$sBacktrace = ob_get_contents();
			ob_end_clean();
			$sBacktrace = preg_replace("/^#.*?[errorMessage[^\n]*\n/is", "", $sBacktrace);
			$sBacktrace = preg_replace("/^#.*?errorsHandler[^\n]*\n/is", "", $sBacktrace);
			$sBacktrace = preg_replace("/^#.*?exceptionsHandler[^\n]*\n/is", "", $sBacktrace);
			$sBacktrace = preg_replace("/^(#[\d]+)([\s]*)(.*)/im", "$1 $3", $sBacktrace);

			self::$vLastError = [
				"object" => $sObject,
				"type" => $sType,
				"code" => $sCode,
				"trigger" => $vCurrentPath["fullpath"],
				"file" => $sCurrentFile,
				"line" => $nCurrentLine,
				"error" => ($sError!==$sCode ? $sError : ""),
				"details" => $sDetails,
				"backtrace" => $sBacktrace,
				"log" => $sErrRow
			];
		} else if($sObject=="@rind") {
			$sObject = "rind";
			ob_start();
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$sBacktrace = ob_get_contents();
			ob_end_clean();
			$sBacktrace = preg_replace("/^#.*?[errorMessage[^\n]*\n/is", "", $sBacktrace);
			$sBacktrace = preg_replace("/^#.*?called at \[([^\n]*)\]\n.*/is", "$1", $sBacktrace);
			$aFileLine = \explode(":", $sBacktrace);

			self::$vLastError = [
				"object" => $sObject,
				"type" => $sType,
				"code" => $sCode,
				"trigger" => $vCurrentPath["fullpath"],
				"file" => $aFileLine[0],
				"line" => $aFileLine[1],
				"error" => ($sError!==$sCode ? $sError : ""),
				"details" => $sDetails,
				"log" => $sErrRow
			];
		} else {
			self::$vLastError = [
				"object" => $sObject,
				"type" => $sType,
				"code" => $sCode,
				"error" => ($sError!==$sCode ? $sError : ""),
				"details" => $sDetails,
				"log" => $sErrRow
			];
		}

		// impresion
		if($sMode=="boolean") { return false; }
		if($sMode=="code") { return $sCode; }
		if(self::$bErrorForceReturn) { return $sMsg; }

		$sMsg = self::errorPage(self::$vLastError);
		if(PHP_SAPI=="cli") { $sMsg = self::out("\n ".$sMsg." ", "error"); }
		if($sMode=="die") { die($sMsg); }
		return $sMsg;
	}

	final public static function exists($sObjectType) {
		$sObjectType = \strtok($sObjectType, ".");
		return isset(self::$vLibraries[$sObjectType]);
	}

	final public static function kill($sObjectName) {
		if(isset(self::$aObjects[$sObjectName])) {
			$sClassName = self::$aObjects[$sObjectName]->class;
			if(isset(self::$aObjectsByClass[$sClassName])) {
				foreach(self::$aObjectsByClass[$sClassName] as $nIndex => $sName) {
					if($sName==$sObjectName) {
						self::$aObjectsByClass[$sClassName][$nIndex] = null;
						unset(self::$aObjectsByClass[$sClassName][$nIndex]);
						break;
					}
				}
			}

			self::$aObjects[$sObjectName] = null;
			unset(self::$aObjects[$sObjectName]);
			\gc_collect_cycles();
			return true;
		}
		return false;
	}

	final public static function lastOf($sObject=null) {
		if($sObject===null) {
			return $vLastOf;
		} else if(isset(self::$vLastOf[$sObject])) {
			return self::$vLastOf[$sObject];
		}
		return null;
	}

	final public static function loadClass($sClassName, $bFeeder) {
		if($bFeeder) {
			$sClassFile = $sClassName.".feeder.php";
		} else {
			$sClassFile = $sClassName.".php";
		}

		if(!isset(self::$aObjectsByClass[$sClassName])) {
			if(\file_exists(self::$vPaths["libraries"].$sClassFile)) {
				require_once(self::$vPaths["libraries"].$sClassFile);
				self::$aObjectsByClass[$sClassName] = [];
			} else if(\file_exists(self::$vPaths["grafts"].$sClassFile)) {
				require_once(self::$vPaths["grafts"].$sClassFile);
				self::$aObjectsByClass[$sClassName] = [];
			} else {
				self::errorMessage(self::NGL_ME, "1001", self::$vPaths["libraries"].$sClassFile." (".$sClassName.")", "die");
			}
		}
	}

	/** FUNCTION {
		load : {
			"description" : "agrega un nuevo objeto al objeto principal y lo retorna",
			"params" : {
				"$sClassName" : "nombre de la clase del nuevo objeto",
				"$sObjectName" : "nombre del nuevo objeto",
				"$aArguments" : "argumentos para el nuevo objeto"
			},
			"return" : "instancia $sObjectName de la clase $sClassName"
		}
	**/
	final public static function loadObject($sClassName, $bFeeder=true, $sConfFile=null, $sObjectName=null, $aArguments=null) {
		if($sObjectName!==null) {
			$sObjectName = self::objectName($sObjectName);
			if(!\in_array($sObjectName, self::$aObjectsByClass[$sClassName])) {
				$sCallClass = __NAMESPACE__."\\".$sClassName;

				self::$bLoadAllowed = true;
				self::$aObjects[$sObjectName] = new $sCallClass ($sClassName, $sObjectName);
				self::$bLoadAllowed = false;
				if(\method_exists(self::call($sObjectName), "__vendor__")) { self::call($sObjectName)->__vendor__(); }
				if(\method_exists(self::call($sObjectName), "__declareAttributes__")) { self::call($sObjectName)->__SetupAttributes__(self::call($sObjectName)->__declareAttributes__()); }
				if(\method_exists(self::call($sObjectName), "__declareArguments__")) { self::call($sObjectName)->__SetupArguments__(self::call($sObjectName)->__declareArguments__()); }
				if(\method_exists(self::call($sObjectName), "__declareVariables__")) { self::call($sObjectName)->__declareVariables__(); }
				if(!$bFeeder && \file_exists($sConfigFile = NGL_PATH_CONF.NGL_DIR_SLASH.$sConfFile.".conf")) { self::call($sObjectName)->__config__($sConfigFile); }
				if(\method_exists(self::call($sObjectName), "__arguments__") && $aArguments!==null) { self::call($sObjectName)->args($aArguments); }
				if(\method_exists(self::call($sObjectName), "__init__")) {
					if($bFeeder) {
						self::call($sObjectName)->__init__($aArguments);
					} else {
						self::call($sObjectName)->__init__();
					}
				}

				self::$aObjectsByClass[$sClassName][] = $sObjectName;
			}

			if(isset(self::$aObjects[$sObjectName])) {
				return self::$aObjects[$sObjectName];
			}
		}

		return false;
	}

	final public static function loadedClass($sClassName=null) {
		if($sClassName) {
			return (
				isset(self::$aObjectsByClass[$sClassName]) || (
					isset(self::$vLibraries[$sClassName]) &&
					isset(self::$aObjectsByClass[self::$vLibraries[$sClassName][0]])
				)
			);
		} else {
			return self::$aObjectsByClass;
		}
	}

	final public static function availables() {
		$aComponents = \array_merge(self::$vCoreLibs, self::$vLibraries);
		\ksort($aComponents);
		$aAvailables = [];
		foreach($aComponents as $sComponent => $aComponent) {
			$aAvailables[$sComponent] = ["object"=>$sComponent, "class"=>$aComponent[0], "documentation"=>"https://hytcom.net/nogal/docs/objects/".$sComponent.".md"];
		}
		return $aAvailables;
	}

	final public static function is($obj, $sType=null) {
		if(\method_exists($obj, "__me__")) {
			$aAvailables = \array_merge(self::$vCoreLibs, self::$vLibraries);
			$object = $obj->__me__();
			if(isset($aAvailables[$object->name]) && $aAvailables[$object->name][0]==$object->class) {
				if($sType!==null) {
					return ($aAvailables[$object->name][0]==$aAvailables[\strtolower($sType)][0]) ? true : false;
				}
				return true;
			}
		}
		return false;
	}

	final public static function isFeeder($obj) {
		if(\method_exists($obj, "__me__")) {
			$aAvailables = \array_merge(self::$vCoreLibs, self::$vLibraries);
			$object = $obj->__me__();
			if(isset($aAvailables[$object->name]) && $aAvailables[$object->name][1]) {
				return true;
			}
		}
		return false;
	}

	protected static function Libraries() {
		$aAvailables = [];
		foreach(self::$vLibraries as $sLib => $aLib) {
			$aAvailables[$sLib] = $aLib[1];
		}
		return $aAvailables;
	}

	final public static function log($sFileName, $sContent) {
		$bError = true;
		if(self::call()) {
			if(\defined("NGL_PATH_LOGS")) {
				$sFilePath = self::call()->sandboxPath(NGL_PATH_LOGS);
				$sFilePath = self::call()->clearPath($sFilePath);
				if(\is_writable($sFilePath)) {
					$sFileName = self::call()->clearPath($sFilePath.NGL_DIR_SLASH.$sFileName);
					if(!\file_exists($sFileName) || \is_writable($sFileName)) {
						$bError = false;
					}
				}
			}

			if($bError) {
				$sTmpDir = self::tempDir();
				$sTmpDir = self::call()->clearPath($sTmpDir.NGL_DIR_SLASH."nogal");
				if(!\file_exists($sTmpDir)) { @\mkdir($sTmpDir, 0777, true); }
				if(\is_writeable($sTmpDir)) {
					$sFileName = $sTmpDir.NGL_DIR_SLASH.\pathinfo($sFileName, PATHINFO_BASENAME);
					if(!\file_exists($sFileName) || \is_writable($sFileName)) {
						$bError = false;
					}
				}
			}

			if(!$bError) { \file_put_contents($sFileName, $sContent, FILE_APPEND); }
		}
		return false;
	}

	final public static function chkreferer($bReturnMode=false) {
		if(!isset($_SERVER["HTTP_REFERER"])) {
			if($bReturnMode) { return false; }
			self::call()->errorHTTP(403);
		} else {
			if(\strpos($_SERVER["HTTP_REFERER"], NGL_URL)===false) {
				if($bReturnMode) { return false; }
				self::call()->errorHTTP(403);
			}
		}

		if($bReturnMode) { return true; }
	}

	final public static function passwd($sPassword, $bDecrypt=false) {
		if(NGL_PASSWORD_KEY!==null && self::call()->exists("crypt")) {
			if($bDecrypt) {
				$sPassword = \base64_decode($sPassword);
				$sPassword = self::call("crypt")->type("aes")->key(NGL_PASSWORD_KEY)->decrypt($sPassword);
			} else {
				$sPassword = self::call("crypt")->type("aes")->key(NGL_PASSWORD_KEY)->encrypt($sPassword);
				$sPassword = \base64_encode($sPassword);
			}
		}

		return $sPassword;
	}

	final public static function out($sMessage, $sStyle=null, $bNewLine=true) {
		$aStyles = [
			"success" => "\033[0;92m%s\033[0m",
			"danger" => "\033[1;31m%s\033[0m",
			"warning" => "\033[1;33m%s\033[0m",
			"info" => "\033[1;36m%s\033[0m",
			"bold" => "\033[1m%s\033[0m",
			"error" => "\033[1;33;45m%s\033[0m"
		];

		$sFormat = '%s';

		if(isset($aStyles[$sStyle])) { $sFormat = $aStyles[$sStyle]; }
		if($bNewLine) { $sFormat .= PHP_EOL; }

		\printf($sFormat, $sMessage);
	}

	final public static function objects() {
		return self::$aObjects;
	}

	final public static function objectName($sObjectName) {
		$sObjectName = \preg_replace("/[^a-zA-Z0-9_\.]/is", "", $sObjectName);
		return \strtolower($sObjectName);
	}

	final public static function parseConfigFileSections($sFilePath) {
		$sFilePath = \preg_replace("/[\\\\\/]{1,}/", NGL_DIR_SLASH, $sFilePath);
		$sFilePath = \rtrim($sFilePath, NGL_DIR_SLASH);
		if(\file_exists($sFilePath)) {
			$aContent = \file($sFilePath);
			$aSections = [];
			$sSection = null;
			$sSectionContent = "";
			$sPrevius = "-";
			foreach($aContent as $sLine) {
				if(\trim($sPrevius)=="" && \trim($sLine)=="") { continue; }
				$sPrevius = $sLine;
				if(\preg_match("/^\[([a-z-A-Z0-9\-\_]+)\]\s+$/is", $sLine, $aMatch)) {
					if($sSection!==null) {
						$aSections[$sSection] = $sSectionContent;
						$sSectionContent = "";
					}
					$sSection = $aMatch[1];
				} else {
					$sSectionContent .= $sLine;
				}
			}
			$aSections[$sSection] = $sSectionContent;
			return $aSections;
		}
	}

	/** FUNCTION {
		"name" : "parseConfigFile",
		"type" : "public",
		"description" : "Parsea un archivo de configuración basado en una estructura enriquecida de los archivos .ini",
		"parameters" : {
			"$sFilePath" : ["string", "Ruta del archivo de configuración"],
			"$bUseSections" : ["boolean", "Indica si el archivo está divido en secciones", "false"]
		},
		"return" : "array o null"
	} **/
	final public static function parseConfigFile($sFilePath, $bUseSections=false) {
		$sFilePath = \preg_replace("/[\\\\\/]{1,}/", NGL_DIR_SLASH, $sFilePath);
		$sFilePath = \rtrim($sFilePath, NGL_DIR_SLASH);
		if(\file_exists($sFilePath)) {
			$sContent = \file_get_contents($sFilePath);
			return self::parseConfigString($sContent, $bUseSections);
		}
		return null;
	}

	/** FUNCTION {
		"name" : "parseConfigString",
		"type" : "public",
		"description" : "Parsea una cadena de configuración basada en una estructura enriquecida de los archivos .ini",
		"parameters" : {
			"$sString" : ["string", "Origen de datos"],
			"$bUseSections" : ["boolean", "Indica si el archivo está divido en secciones", "false"]
		},
		"return" : "array"
	} **/
	final public static function parseConfigString($sString, $bUseSections=false, $bPreserveNL=false) {
		if($bPreserveNL) {
			$NL = self::call()->unique(6);
			$sString = \preg_replace("/(\\\(\\r\\n|\\n))/", $NL, $sString);
		}
		$sString = \preg_replace("/\\\(\\r\\n|\\n)/s", "", $sString);

		$aData = [];
		$aLines = \explode(chr(10), $sString);

		$sHashDecode = self::call()->unique(8);
		$sSection = null;
		foreach($aLines as $sLine) {
			if($bUseSections) {
				$bSection = \preg_match("/^(\[)([a-zA-Z0-9\_\.\-]+)(\])/is", $sLine, $aMatchs);
				if($bSection) {
					$sSection = $aMatchs[2];
					$aData[$sSection] = [];
					continue;
				}
			}

			$bStatement = \preg_match("/^(?!;)([\w+\.\-\/]+)(\[[\w+\.\-\/]*\])?\s*=?\s*(.*)\s*$/s", $sLine, $aMatchs);
			if($bStatement) {
				$sKey	= $aMatchs[1];
				$sIndex	= (!empty($aMatchs[2])) ? \substr($aMatchs[2], 1, -1) : null;
				$mValue	= $aMatchs[3];
				if(\preg_match("/^(((\"|\')(.*)(\"|\'))?.*)(;.*)?$/is", $mValue, $aValue)) {
					if(!\array_key_exists(4, $aValue)) {
						$aValue = \explode(";", $aValue[1]);
						$mValue	= $aValue[0];
					} else {
						$mValue	= \strlen($aValue[4]) ? $aValue[4] : "";
					}
				}
				$mValue = \trim($mValue);

				// variables
				$mValue = \preg_replace_callback(
					"/\{\\\$([a-z0-9_\.]+)\}/i",
					function($aMatches) use($aData,$sHashDecode) {
						$mValue = "{:".$aMatches[1].":}";
						$aVariable = \explode(".", $aMatches[1]);

						if(\strtolower($aVariable[0])=="ngl" && !empty($aVariable[2])) {
							$obj = self::call($aVariable[1]);
							$callback = $aVariable[2];
							$aVarValue = \is_callable([$obj, $callback]) ? $obj->$callback() : $obj->$callback;
							$aVariable = array_slice($aVariable,3);
							$mValue = count($aVariable) ? self::call()->strToVars("{:".\implode(".", $aVariable).":}", $aVarValue) : $sHashDecode.\json_encode($aVarValue);
						} else if(\strtolower($aVariable[0])=="env") {
							array_shift($aVariable);
							$mValue = count($aVariable) ? self::call()->strToVars("{:".\implode(".", $aVariable).":}", $aData) : $sHashDecode.\json_encode($aData);
						}

						if($mValue==="{:".$aMatches[1].":}") { $mValue = self::call()->strToVars("{:".$aMatches[1].":}", $GLOBALS); }
						return $mValue;
					},
					$mValue
				);

				// constantes
				$mValue = \preg_replace_callback(
					"/\{@([a-z_][a-z0-9_\.]*)\}/i",
					function($aMatches) {
						if(\strpos($aMatches[1], ".")) {
							$aConstant = \explode(".", $aMatches[1]);
							$aMatches[1] = \array_shift($aConstant);
						}

						$mValue = (\defined($aMatches[1])) ? \constant($aMatches[1]) : $aMatches[1];
						if(isset($aConstant)) {
							foreach($aConstant as $sIndex) {
								$mValue = $mValue[$sIndex];
							}
						}
						return $mValue;
					},
					$mValue
				);

				// booleans
				switch(\strtolower($mValue)) {
					case "null": $mValue = null; break;
					case "false": $mValue = false; break;
					case "true": $mValue = true; break;
				}

				// multilinea
				if($bPreserveNL) { $mValue = \str_replace($NL, chr(10), $mValue); }

				// decode json
				if(\substr($mValue,0,8)==$sHashDecode) { $mValue = json_decode(\substr($mValue,8), true); }

				if($sSection!==null) {
					if($sIndex!==null) {
						if($sIndex!="") {
							$aData[$sSection][$sKey][$sIndex] = $mValue;
						} else {
							$aData[$sSection][$sKey][] = $mValue;
						}
					} else {
						$aData[$sSection][$sKey] = $mValue;
					}
				} else {
					if($sIndex!==null) {
						if($sIndex!="") {
							$aData[$sKey][] = $mValue;
						} else {
							$aData[$sKey][$sIndex] = $mValue;
						}
					} else {
						$aData[$sKey] = $mValue;
					}
				}
			}
		}

		return $aData;
	}

	final public static function path($sPath) {
		$sPath = \strtolower($sPath);
		if(isset(self::$vPaths[$sPath])) { return self::$vPaths[$sPath]; }
		return null;
	}

	final public static function setPath($sPath) {
		$sBaseDir = \preg_replace("/[\\\\\/]{1,}/", NGL_DIR_SLASH, NGL_PATH_FRAMEWORK);
		self::$vPaths[$sPath] = \realpath($sBaseDir.NGL_DIR_SLASH.$sPath).NGL_DIR_SLASH;
	}

	final public static function starTime() {
		return self::$nStarTime;
	}

	final public static function tempDir() {
		if(!function_exists("sys_get_temp_dir") ) {
			if(!empty(NGL_PATH_TMP) && \is_writable(NGL_PATH_TMP)) { return NGL_PATH_TMP; }
			if(!empty($_ENV["TMP"]) && \is_writable($_ENV["TMP"])) { return $_ENV["TMP"]; }
			if(!empty($_ENV["TMPDIR"]) && \is_writable($_ENV["TMPDIR"])) { return $_ENV["TMPDIR"]; }
			if(!empty($_ENV["TEMP"]) && \is_writable($_ENV["TEMP"])) { return $_ENV["TEMP"]; }

			$sTempFile = \tempnam(self::call()->unique(16), "");
			if($sTempFile) {
				$sTempDir = \realpath(\dirname($sTempFile));
				\unlink($sTempFile);
				return $sTempDir;
			} else {
				return false;
			}
		}

		return \sys_get_temp_dir();
	}

	final public static function whois($sElement=null) {
		if($sElement===null) {
			$aLibraries = self::$vLibraries;
			$aMethods = \get_class_methods(__CLASS__);
			foreach($aMethods as $nKey => $sMethod) {
				if($sMethod[0]=="_") { unset($aMethods[$nKey]); }
			}

			$vInfo = [];
			$vInfo["objects"] = \array_keys($aLibraries);
			$vInfo["methods"] = $aMethods;

		} else {
			$sElement = \strtolower($sElement);
			$vInfo = self::call($sElement)->Whoami();
		}
		return $vInfo;
	}
}

?>