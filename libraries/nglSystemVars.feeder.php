<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___

# system
https://hytcom.net/nogal/docs/objects/system.md
*/
namespace nogal;
class nglSystemVars extends nglTrunk {

	protected $class		= "nglSystemVars";
	protected $me			= "sysvar";
	protected $object		= "sysvar";
	private $VARS;
	private $SETTINGS;

	public function __builder__() {
		$SETTINGS = [];

		// para que una variable sea privada no debe admitir el argumento $mValue
		// por eso se asignan mediate la ejecucion de un metodo privado

		// caracteres con acento
		$SETTINGS["ACCENTED"]		= ['$this->AccentedChars()'];

		// HTTP CODES
		$SETTINGS["HTTP_CODES"]		= ['$this->SetHTTPCodes()'];

		// IP del cliente
		$SETTINGS["IP"]				= ['$this->SetIP()'];

		// variable con contenido null
		$SETTINGS["NULL"]			= ['null'];

		// expresiones regulares de uso comun
		$SETTINGS["REGEX"]			= ['$this->SetRegexs()'];

		// PHP_SELF
		$SETTINGS["SELF"]			= ['$this->SetSelf()'];

		// id del usuario (en caso de existir un login)
		$SETTINGS["UID"]			= ['$this->SetUID()'];

		// version
		$SETTINGS["VERSION"]		= ['$this->SetVersion()'];

		// SETTINGS
		$this->SETTINGS = $SETTINGS;

		// VARIABLES
		$VARS = [];
		foreach($SETTINGS as $sVarname => $mValue) {
			$VARS[$sVarname] = (!\array_key_exists(1, $mValue)) ? eval("return ".$mValue[0].";") : $mValue[1];
		}

		$this->VARS = $VARS;
	}

	public function get($sVarname) { return $this->__get($sVarname); }
	public function getall() { return $this->__get("ALL"); }

	public function __get($sVarname="ALL") {
		if($sVarname!=="ALL") {
			if(isset($this->VARS[$sVarname])) {
				return $this->VARS[$sVarname];
			}
		} else {
			return $this->VARS;
		}
	}

	private function AccentedChars() {
		$vChars = [
			"À"=>"A", "Á"=>"A", "Â"=>"A", "Ã"=>"A", "Ä"=>"A", "Å"=>"A", "Æ"=>"A",
			"È"=>"E", "É"=>"E", "Ê"=>"E", "Ë"=>"E",
			"Ì"=>"I", "Í"=>"I", "Î"=>"I", "Ï"=>"I",
			"Ò"=>"O", "Ó"=>"O", "Ô"=>"O", "Õ"=>"O", "Ö"=>"O", "Ø"=>"O",
			"Ù"=>"U", "Ú"=>"U", "Û"=>"U", "Ü"=>"U",
			"à"=>"a", "á"=>"a", "â"=>"a", "ã"=>"a", "ä"=>"a", "å"=>"a", "æ"=>"a",
			"è"=>"e", "é"=>"e", "ê"=>"e", "ë"=>"e",
			"ì"=>"i", "í"=>"i", "î"=>"i", "ï"=>"i",
			"ð"=>"o", "ò"=>"o", "ó"=>"o", "ô"=>"o", "õ"=>"o", "ö"=>"o", "ø"=>"o",
			"ù"=>"u", "ú"=>"u", "û"=>"u",
			"Š"=>"S", "š"=>"s", "Ž"=>"Z", "ž"=>"z", "Ç"=>"C", "Ñ"=>"N", "Ý"=>"Y", "Þ"=>"B",
			"ß"=>"Ss", "ç"=>"c", "ñ"=>"n", "ý"=>"y", "ý"=>"y", "þ"=>"b", "ÿ"=>"y"
		];

		return $vChars;
	}

	private function SetIP() {
		return (isset($_SERVER["REMOTE_ADDR"])) ? $_SERVER["REMOTE_ADDR"] : "127.0.0.1";
	}

	private function SetHTTPCodes() {
		return [
			100 => "Continue",
			101 => "Switching Protocols",
			102 => "Processing",
			200 => "OK",
			201 => "Created",
			202 => "Accepted",
			203 => "Non-Authoritative Information",
			204 => "No Content",
			205 => "Reset Content",
			206 => "Partial Content",
			207 => "Multi-Status",
			300 => "Multiple Choices",
			301 => "Moved Permanently",
			302 => "Found",
			303 => "See Other",
			304 => "Not Modified",
			305 => "Use Proxy",
			306 => "(Unused)",
			307 => "Temporary Redirect",
			308 => "Permanent Redirect",
			400 => "Bad Request",
			401 => "Unauthorized",
			402 => "Payment Required",
			403 => "Forbidden",
			404 => "Not Found",
			405 => "Method Not Allowed",
			406 => "Not Acceptable",
			407 => "Proxy Authentication Required",
			408 => "Request Timeout",
			409 => "Conflict",
			410 => "Gone",
			411 => "Length Required",
			412 => "Precondition Failed",
			413 => "Request Entity Too Large",
			414 => "Request-URI Too Long",
			415 => "Unsupported Media Type",
			416 => "Requested Range Not Satisfiable",
			417 => "Expectation Failed",
			418 => "I'm a teapot",
			419 => "Authentication Timeout",
			420 => "Enhance Your Calm",
			422 => "Unprocessable Entity",
			423 => "Locked",
			424 => "Failed Dependency",
			424 => "Method Failure",
			425 => "Unordered Collection",
			426 => "Upgrade Required",
			428 => "Precondition Required",
			429 => "Too Many Requests",
			431 => "Request Header Fields Too Large",
			444 => "No Response",
			449 => "Retry With",
			450 => "Blocked by Windows Parental Controls",
			451 => "Unavailable For Legal Reasons",
			494 => "Request Header Too Large",
			495 => "Cert Error",
			496 => "No Cert",
			497 => "HTTP to HTTPS",
			499 => "Client Closed Request",
			500 => "Internal Server Error",
			501 => "Not Implemented",
			502 => "Bad Gateway",
			503 => "Service Unavailable",
			504 => "Gateway Timeout",
			505 => "HTTP Version Not Supported",
			506 => "Variant Also Negotiates",
			507 => "Insufficient Storage",
			508 => "Loop Detected",
			509 => "Bandwidth Limit Exceeded",
			510 => "Not Extended",
			511 => "Network Authentication Required",
			598 => "Network read timeout error",
			599 => "Network connect timeout error"
		];
	}

	private function SetSelf() {
		return self::currentPath();
	}

	private function SetRegexs() {
		$vRegexs = [];
		$vRegexs["base64"] 		= "[a-zA-Z0-9\+\/\=]*";
		$vRegexs["color"] 		= "#([0-9A-F]{6,8}|[0-9A-F]{3})";
		$vRegexs["date"] 		= "[0-9]{4}\-([012][0-9]|3[01])\-([012][0-9]|3[01])";
		$vRegexs["datetime"] 	= "[0-9]{4}\-([012][0-9]|3[01])\-([012][0-9]|3[01])\ ([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])";
		$vRegexs["email"] 		= "[a-zA-Z0-9\_\-]+(\.[a-zA-Z0-9\-\_]+)*@[a-zA-Z0-9\-]+(\.[a-zA-Z0-9]+)(\.[a-zA-Z]{2,})*";
		$vRegexs["filename"] 	= "(?(?=^([a-z]:|\\\\))(^([a-z]:|\\\\)[^\/\?\<\>\:\*\|]+)|([^\\0]+))";
		$vRegexs["fulltag"]		= "<([a-zA-Z]+)(\"[^\"]*\"|\'[^\']*\'|[^\'\">])*>(.*?)<\/\\1>";
		$vRegexs["imya"] 		= "[a-zA-Z][a-zA-Z0-9]{31}";
		$vRegexs["ipv4"]		= "((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)";
		$vRegexs["ipv6"]		= "(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]).){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]).){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))";
		$vRegexs["phpvar"] 		= "\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*";
		$vRegexs["tag"]			= "<([a-zA-Z]+)(\"[^\"]*\"|\'[^\']*\'|[^\'\">])*>";
		$vRegexs["time"] 		= "([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?";
		$vRegexs["url"] 		= "((http|ftp|HTTP|FTP)(s|S)?:\/\/)?([0-9a-zA-Z\.-]+)\.([a-zA-Z\.]{2,6})([\/a-zA-Z0-9 \.-]*)*\/?";

		return $vRegexs;
	}

	private function SetUID() {
		if(isset($_SESSION[NGL_SESSION_INDEX]["ALVIN"], $_SESSION[NGL_SESSION_INDEX]["ALVIN"]["id"])) {
			return $_SESSION[NGL_SESSION_INDEX]["ALVIN"]["id"];
		} else {
			return null;
		}
	}

	private function SetVersion() {
		$vVersion					= [];
		$vVersion["name"]			= "nogal";
		$vVersion["description"]	= "the most simple PHP Framework";
		$vVersion["version"]		= \file_get_contents(NGL_PATH_FRAMEWORK.NGL_DIR_SLASH."version");
		$vVersion["author"]			= "hytcom";
		$vVersion["site"]			= "https://hytcom.net";
		$vVersion["documentation"]	= "https://hytcom.net/nogal/docs";
		$vVersion["github"]			= "https://github.com/hytcom/nogal";
		$vVersion["docker"]			= "docker pull hytcom/nogal:latest";

		return $vVersion;
	}

	public function sessionVars() {
		$this->VARS["UID"] = $this->SetUID();
	}
}

?>