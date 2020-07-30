<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___
  
# mysql
## nglCoon *extends* nglBranch *implements* inglBranch
Cliente/Servidor de peticiones REST

https://github.com/hytcom/wiki/blob/master/nogal/docs/coon.md

*/
namespace nogal;

class nglCoon extends nglBranch implements inglBranch {

	final protected function __declareArguments__() {
		$vArguments							= array();
		$vArguments["apiname"]				= array('$mValue', "nogalcoon");
		$vArguments["auth"]					= array('$mValue', null); // basic | bearer | alvin
		$vArguments["ctype"]				= array('(string)$mValue', "json"); // csv | json | text | xml
		$vArguments["data"]					= array('$mValue');
		$vArguments["key"]					= array('$mValue', "SOME_STRONG_KEY");
		$vArguments["method"]				= array('(string)$mValue', "POST");
		$vArguments["token"]				= array('$mValue', null);
		$vArguments["url"]					= array('(string)$mValue', null);
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = array();
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
	}


	public function request() {
		list($mData,$sURL,$sToken,$sCType) = $this->getarguments("data,url,token,ctype", func_get_args());
		$sMethod = strtoupper($this->argument("method"));
		$sAuth = $this->argument("auth");

		$sCType = strtolower($sCType);
		$mContent = "";
		
		switch($sCType) {
			case "json":
				$sContentType = "application/json";
				if($sMethod=="POST") {
					$mContent = (is_array($mData)) ? json_encode($mData) : $mData;
				} else {
					$mContent = (!is_array($mData)) ? json_decode($mData, true) : $mData;
				}
				break;

			case "xml":
				$sContentType = "application/xml";
				if($sMethod=="POST") {
					$mContent = (is_array($mData)) ? self::call("shift")->convert($mData, "array-xml") : $mData;
				} else {
					$mContent = (!is_array($mData)) ? self::call("shift")->convert($mData, "xml-array") : $mData;
				}
				$sCType = "xml";
				break;

			case "csv":
				$sContentType = "text/csv";
				if($sMethod=="POST") {
					$mContent = (is_array($mData)) ? self::call("shift")->convert($mData, "array-csv") : $mData;
				} else {
					$mContent = (!is_array($mData)) ? self::call("shift")->convert($mData, "csv-array") : $mData;
				}
				break;

			case "text":
				$sContentType = "text/plain";
				if($sMethod=="POST") {
					$mContent = (is_array($mData)) ? http_build_query($mData) : $mData;
				} else {
					$mContent = $mData;
					if(!is_array($mData)) { parse_str($mData, $mContent); }
				}
				break;
		}

		$sBuffer = "REQUEST ERROR: Bad Request";
		if(self::call()->isURL($sURL) && function_exists("curl_init")) {
			$aHeaders = array("Content-Type: ".$sContentType);
			if($sAuth!==null) { $aHeaders[] = "Authorization: ".$sAuth; }
			if($sMethod=="GET" && !empty($mContent)) {
				$url = self::call("url")->load($sURL);
				$sURL = $url->update("params", $mContent)->get();
			}

			$curl = curl_init($sURL);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $aHeaders); 
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			if($sMethod=="POST") {
				curl_setopt($curl, CURLOPT_POSTFIELDS, $mContent);
				curl_setopt($curl, CURLOPT_POST, 1);
			}
			
			$sBuffer = curl_exec($curl); 
			if(curl_errno($curl)) { $sBuffer = "REQUEST ERROR curl: ".curl_error($curl); }
			curl_close($curl);
		}

		return $sBuffer;
	}

	public function getrequest() {
		$aHeaders		= self::call()->getheaders();
		$aRequest		= $_REQUEST["source"];
		$mBody			= $sInput = file_get_contents("php://input");
		$aSelf			= self::call("sysvar")->SELF;
		
		if(isset($aHeaders["authorization"])) {
			$aAuth = explode(" ", $aHeaders["authorization"], 2);
			$sAuthMethod = strtolower($aAuth[0]);
			if($sAuthMethod=="basic") {
				$sAuth = base64_decode($aAuth[1]);
				$mAuth = (strpos($sAuth, ":")) ? explode(":", $sAuth, 2) : $sAuth;
			} else if($sAuthMethod=="bearer") {
				$mAuth = $aAuth[1];
			} else if($sAuthMethod=="alvin") {
				$sToken = self::call()->tokenDecode($aAuth[1], $this->argument("key"));
				if($sToken!==false) {
					$mAuth = $sToken;
					$this->args("token", $sToken);
				} else {
					$mAuth = false;
				}
			}
		}
		
		$sCType = $this->argument("ctype");
		$sContentType = (isset($aHeaders["content-type"])) ? $aHeaders["content-type"] : null;
		switch($sContentType) {
			case "application/json":
				$mBody = json_decode($sInput, true);
				$sCType = "json";
				break;

			case "application/xhtml+xml":
			case "application/xml":
			case "text/xml":
				$mBody = self::call("shift")->convert($sInput, "xml-array");
				$sCType = "xml";
				break;

			case "text/csv":
				$mBody = self::call("shift")->convert($sInput, "csv-array");
				$sCType = "csv";
				break;

			case "text/plain":
			case "text/html":
				$mBody = $sInput;
				$sCType = "text";
				break;
		}

		if($this->argument("ctype")===null) { $this->args("ctype", $sCType); }

		$aReturn = array();
		if(isset($mAuth)) { $aReturn["auth"] = $mAuth; }
		$aReturn["path"]	= $aSelf;
		$aReturn["headers"]	= $aHeaders;
		$aReturn["request"]	= $aRequest;
		$aReturn["body"]	= $mBody;

		return $aReturn;
	}

	public function response() {
		list($aData,$sToken,$sCType) = $this->getarguments("data,token,ctype", func_get_args());
		$sApiName = $this->argument("apiname");

		if(!in_array($sCType, array("csv","json","text","xml"))) { $sCType = "json"; }

		if($sCType=="json" || $sCType=="xml") {
			$aResponse = array();
			$aResponse["api"]			= $sApiName;
			$aResponse["timestamp"]		= time();
			$aResponse["datetime"]		= date("Y-m-d H:i:s", $aResponse["timestamp"]);
			if($sToken!==null) {
				$aResponse["token"] 	= self::call()->tokenEncode($sToken, $this->argument("key"), false);
			}
			$aResponse["count"]			= count($aData);
			$aResponse["data"]			= $aData;

			header("Content-Type: application/".$sCType, true);
			return self::call("shift")->convert($aResponse, "array-".$sCType);
		} else if($sCType=="csv") {
			header("Content-Type: text/csv", true);
			return self::call("shift")->convert($aData, "array-csv");
		} else {
			header("Content-Type: text/plain", true);
			return (is_array($aData)) ? self::call()->imploder(array("\t", "\n"), $aData) : $aData;
		}
	}
}

?>