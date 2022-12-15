<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# ants
https://hytcom.net/nogal/docs/objects/ants.md
*/
namespace nogal;
class nglAnts extends nglBranch implements inglBranch {

	private $aHeaders;
	private $aFormats;
	private $sCurrentKey;
	private $mToken;
	private $aRoutes;

	final protected function __declareArguments__() {
		$vArguments							= [];
		$vArguments["anthill"]				= ['$mValue', NGL_PATH_TMP];
		$vArguments["apikey_name"]			= ['$mValue', "api_key"]; // cuando auth=apikey
		$vArguments["apikey_place"]			= ['$mValue', "header", ["header","body"]];
		$vArguments["auth"]					= ['$mValue', null]; // debe retornar 200 si es ok u otro codigo http
		$vArguments["authtype"]				= ['\strtolower($mValue)', null, ["apikey","basic","bearer","digest","token"]];
		$vArguments["body"]					= ['$mValue'];
		$vArguments["bodytype"]				= ['\strtolower($mValue)', "raw", ["raw","form","encoded"]];
		$vArguments["format"]				= ['\strtolower($mValue)', null, ["html","json","text","xml"]];
		$vArguments["header"]				= ['$mValue', null];
		$vArguments["headers"]				= ['$mValue', null]; // headers adicionales enviados en las respuestas
		$vArguments["key"]					= ['$mValue', null];
		$vArguments["method"]				= ['\strtoupper($mValue)', "GET", ["GET","POST","PUT","PATH","DELETE","OPTIONS"]];
		$vArguments["port"]					= ['$mValue', null];
		$vArguments["route"]				= ['$this->SetRoute($mValue)', null];
		$vArguments["routes"]				= ['$this->SetRoutes($mValue)', null];
		$vArguments["routes_base"]			= ['$mValue', null];
		$vArguments["routes_secure"]		= ['self::call()->isTrue($mValue)', true];
		$vArguments["sslverify"]			= ['self::call()->isTrue($mValue)', false];
		$vArguments["sslversion"]			= ['$mValue', CURL_SSLVERSION_TLSv1_2];
		$vArguments["token"]				= ['$this->SetToken($mValue)', null];
		$vArguments["token_claim"]			= ['$mValue', "access_token"];
		$vArguments["url"]					= ['(string)$mValue', null];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = [];
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
		$this->aRoutes = [];
		$this->aHeaders = [];
		$this->aFormats = [
			"json" => "application/json",
			"text" => "text/plain",
			"html" => "text/html",
			"xml" => "application/xml"
		];
		$this->__errorMode__("die");
	}

	protected function SetRoute() {
		list($aRoute) = $this->getarguments("route", \func_get_args());
		if(!\is_array($aRoute) || count($aRoute)<3) { self::errorMessage($this->object, 1005); }
		$aVerbs = !\is_array($aRoute[0]) ? [$aRoute[0]] : $aRoute[0];
		foreach($aVerbs as $sMethod) {
			$sRoute = $aRoute[1];
			\preg_match_all("/\{(.*?)\}/is", $sRoute, $aParams);
			if(count($aParams[1])) {
				$sRoute = \preg_replace("/\{(.*?)\}/is", "([^\?\/]+)", $aRoute[1]);
			}
			$sRoute = self::call()->clearPath($this->routes_base."/".$sRoute);

			$sMethod = \strtoupper($sMethod);

			$nParams = count($aParams[1]) ? 1 : 0;
			if(empty($this->aRoutes[$nParams][$sRoute])) { $this->aRoutes[$nParams][$sRoute] = []; }
			$this->aRoutes[$nParams][$sRoute][$sMethod] = [
				"method"	=> $sMethod,
				"path"		=> $sRoute,
				"pathvars"	=> ($nParams) ? $aParams[1] : null,
				"response"	=> $aRoute[2],
				"secure"	=> $this->routes_secure
			];
		}

		return $this;
	}

	protected function SetRoutes() {
		list($aRoutes) = $this->getarguments("routes", \func_get_args());
		foreach($aRoutes as $aRoute) {
			$this->route($aRoute);
		}
		return $this;
	}

	protected function SetToken($aToken) {
		$this->mToken = ($this->authtype=="basic") ? \base64_encode(\implode(":",$aToken)) : $aToken;
		return $this->mToken;
	}

	public function savekey() {
		list($sToken,$sKey) = $this->getarguments("token,key", \func_get_args());
		$this->sCurrentKey = empty($sKey) ? self::call()->uuid() : $sKey;
		if(!NGL_TERMINAL) {
			if(!\array_key_exists("ANTHILL", $_SESSION[NGL_SESSION_INDEX])) {
				$_SESSION[NGL_SESSION_INDEX]["ANTHILL"] = [];
			}
			$_SESSION[NGL_SESSION_INDEX]["ANTHILL"][$this->sCurrentKey] = $sToken;
			return $this->sCurrentKey;
		} else {
			if(self::call()->fileSave($this->anthill."/".$this->sCurrentKey, $sToken)) {
				return $this->sCurrentKey;
			}
		}

		return false;
	}

	public function key() {
		list($sKey) = $this->getarguments("key", \func_get_args());
		if(empty($sKey)) { $sKey = $this->sCurrentKey; }
		if(!NGL_TERMINAL) {
			if(!empty($_SESSION[NGL_SESSION_INDEX]["ANTHILL"][$sKey])) {
				$this->mToken = $_SESSION[NGL_SESSION_INDEX]["ANTHILL"][$sKey];
				return $this;
			}
		} else {
			if($this->mToken = self::call()->fileLoad($this->anthill."/".$sKey)) {
				return $this;
			}
		}

		return self::errorMessage($this->object, 1001);
	}

	public function getKey() {
		return $this->sCurrentKey;
	}

	public function currentToken() {
		return $this->mToken;
	}

	public function getrequest() {
		$sMethod 		= NGL_GARDENS_PLACE["METHOD"];
		$aHeaders		= \getallheaders();
		$aLowerHeaders	= self::call()->getheaders();
		$sContentType 	= (isset($aLowerHeaders["content-type"])) ? $aLowerHeaders["content-type"] : null;
		if(\strpos($sContentType,";")) { $sContentType = \explode(";", $sContentType)[0]; }

		$sRaw = $mBody = "";
		if($sMethod!="GET") {
			$mBody = !empty($_POST) ? $_POST : \file_get_contents("php://input");
		}

		switch($sContentType) {
			case "text":
			case "text/plain":
			case "text/csv":
				$sFormat = "text";
				break;

			case "multipart/form-data":
			case "application/x-www-form-urlencoded":
				$sFormat = "json";
				break;

			case "json":
			case "application/json":
				$mBody = \json_decode($mBody, true);
				$sFormat = "json";
				break;

			case "xml":
			case "application/xhtml+xml":
			case "application/xml":
			case "text/xml":
				$mBody = self::call("shift")->convert($mBody, "xml-array");
				$sFormat = "xml";
				break;

			case "text/html":
			default:
				$mBody = self::call("shift")->convert($mBody, "xml-array");
				$sFormat = "html";
				break;
		}

		// formato forzado de la respuesta
		if(!empty($aLowerHeaders["content-type-response"]) && !empty($this->aFormats[\strtolower($aLowerHeaders["content-type-response"])])) {
			$sFormat = \strtolower($aLowerHeaders["content-type-response"]);
		} else if($this->format) {
			$sFormat = $this->format;
		}
		$this->args("format", $sFormat);

		if($this->authtype=="apikey") {
			if($this->apikey_place=="header" && isset($aHeaders[$this->apikey_name])) {
				$mAuth = $aHeaders[$this->apikey_name];
			} else if($this->apikey_place=="body" && isset($mBody[$this->apikey_name])) {
				$mAuth = $mBody[$this->apikey_name];
			}
		} else {
			if(isset($aLowerHeaders["authorization"])) {
				$aAuth = \explode(" ", $aLowerHeaders["authorization"], 2);
				$sAuthMethod = \strtolower($aAuth[0]);
				switch($sAuthMethod) {
					case "basic":
						$mAuth = \base64_decode($aAuth[1]);
						$mAuth = (\strpos($mAuth, ":")) ? \explode(":", $mAuth, 2) : $mAuth;
						break;

					case "digest":
						$mAuth = [];
						\preg_match_all("/(\w+)=(?:(?:'([^']+)'|\"([^\"]+)\")|([^\s,]+))/", $aAuth[1], $aGetDigest, PREG_SET_ORDER);
						foreach($aGetDigest as $aPart) {
							$mAuth[$aPart[1]] = $aPart[3];
						}
						break;

					case "bearer":
						$mAuth = $aAuth[1];
						break;

					default:
						$mAuth = $aLowerHeaders["authorization"];
						break;
				}
			}
		}

		return [
			"method" 	=> $sMethod,
			"auth"		=> (!empty($mAuth) ? $mAuth : null),
			"path"		=> NGL_GARDENS_PLACE,
			"headers"	=> $aHeaders,
			"pathvars"	=> null,
			"query"		=> $_GET,
			"raw"		=> $sRaw,
			"body"		=> $mBody
		];
	}

	public function setheader() {
		list($mHeader) = $this->getarguments("header", func_get_args());
		$aHeader = \is_array($mHeader) ? $mHeader : sefl::call()->explodeTrim(":", $mHeader, 2);
		$this->aHeaders[$aHeader[0]] = (\count($aHeader)>1) ? $aHeader[1] : "";
		return $this;
	}

	private function GetRoute($sMethod, $sPath) {
		if(NGL_GARDENS_PLACE["AUTOINDEX"]) { $sPath = \rtrim($sPath, "index"); }
		if(!empty($this->aRoutes[0][$sPath]) && !empty($this->aRoutes[0][$sPath][$sMethod])) {
			return $this->aRoutes[0][$sPath][$sMethod];
		}

		if(!empty($this->aRoutes[1])) {
			foreach($this->aRoutes[1] as $sRoute => $aRoute) {
				if(\preg_match("#^".$sRoute."$#", $sPath)) {
					if(!empty($aRoute[$sMethod])) {
						\preg_match("#".$sRoute."#", $sPath, $aParams);
						array_shift($aParams);
						$aRoute[$sMethod]["pathvars"] = \array_combine($aRoute[$sMethod]["pathvars"], $aParams);
						return $aRoute[$sMethod];
					}
				}
			}
		}

		return false;
	}

	public function response() {
		$aRequest = $this->getrequest();
		$response			= new \stdClass();
		$response->method	= $aRequest["method"];
		$response->code		= 405;
		$response->headers	= ($this->headers!==null) ? $this->headers : [];
		$response->body		= null;

		if($aRoute = $this->GetRoute($aRequest["method"], $aRequest["path"]["URLPATH"])) {
			$response->code = 200;

			// autenticacion
			if($aRoute["secure"]===true) {
				if(\is_callable($this->auth)) {
					$response->code = 401;
					$fAuth = $this->auth;
					$response->code = $fAuth($aRequest);
				}
				if(!\is_int($response->code)) { $response->code = 401; }
			}

			// path vars
			if($aRoute["pathvars"]!==null) { $aRequest["pathvars"] = $aRoute["pathvars"]; }

			// response from backend
			if(\is_array($aRoute["response"]) && \is_string($aRoute["response"][0]) && \substr(\strtolower($aRoute["response"][0]),0,4)=="nut.") {
				if(!empty($aRoute["response"][1])) {
					$response = self::call($aRoute["response"][0])->run($aRoute["response"][1], [$aRequest, $response]);
				} else {
					$response->code = 404;
				}
			} else {
				$response = $aRoute["response"]($aRequest, $response);
			}

		} else {
			$response->code = 404;
		}

		// headers
		array_unshift($response->headers, "HTTP/1.0 ".$response->code." ".self::call("sysvar")->HTTP_CODES[$response->code]);
		$response->headers["Content-Type"] = $this->aFormats[$this->format];
		foreach($response->headers as $sHeader => $sHeaderValue) {
			if($sHeader===0) {
				\header($sHeaderValue);
			} else {
				\header($sHeader.":".$sHeaderValue, true);
			}
		}

		// body
		if(!empty($response->body) && \is_array($response->body)) {
			if($this->format=="json" || $this->format=="xml") {
				$sData = self::call("shift")->convert($response->body, "array-".$this->format);
			} else if($this->format=="text") {
				$sData = self::call()->imploder(["\t", "\n"], $response->body);
			}

			$response->body = $sData;
		}

		return $response;
	}

	public function toArray($mMessage, $sFrom="json") {
		if(\is_array($mMessage)) { return $mMessage; }
		switch($sFrom) {
			case "json":
				return \json_decode($mMessage, true);

			case "xml":
				return self::call("shift")->convert($mMessage, "xml-array");

			case "text":
				\parse_str($mMessage, $aData);
				return $aData;

			case "html":
			default:
				return [$mMessage];
		}
	}

	public function getToken() {
		list($sMethod,$sURL,$sBody,$sClaim) = $this->getarguments("method,url,body,token_claim", \func_get_args());
		$aResponse = $this->request($sMethod,$sURL,$sBody);

		if($aResponse["code"]!=200) { self::errorMessage($this->object, 1004); }
		$aBody = \json_decode($aResponse["body"], true);
		$sToken = self::call()->strToVars($sClaim, $aBody);
		return $this->savekey($sToken);
	}

	public function request() {
		list($sMethod,$sURL,$sBody) = $this->getarguments("method,url,body", \func_get_args());
		$sMethod = \strtoupper($sMethod);
		$sFormat = empty($this->format) ? "html" : $this->format;
		$mContent = null;
		switch($this->bodytype) {
			case "raw":
				$sContentType = $this->aFormats[$sFormat];
				$mContent = $sBody;
				break;

			case "form":
				$sContentType = "";
				$mContent = $this->toArray($sBody, $sFormat);
				break;

			case "encoded":
				$sContentType = "application/x-www-form-urlencoded";
				$mContent = \http_build_query($this->toArray($sBody, $sFormat));
				break;
		}

		$aResponse = false;

		if(self::call()->isURL($sURL) && \function_exists("curl_init")) {
			$aHeaders = ["Content-Type: ".$sContentType];

			// autorizacion
			switch($this->authtype) {
				case "apikey":
					if($this->apikey_place=="header") {
						$aHeaders[$this->apikey_name] = $this->mToken;
					} else if($this->apikey_place=="body") {
						if($this->bodytype) {
							$mContent[$this->apikey_name] = $this->mToken;
						}
					}
					break;

				case "basic":
				case "bearer":
					$aHeaders[] = "Authorization: ".\ucfirst($this->authtype)." ".$this->sCurrentKey;
					break;

				case "digest":
					$sDigest = $this->Digest($sContentType);
					if(!empty($sDigest)) {
						$aHeaders[] = "Authorization: Digest ".$sDigest;
					}
					break;

				case "token":
					$aHeaders[] = "Authorization: ".$this->mToken;
					break;
			}

			// parametros
			if($sMethod=="GET" && $mContent!==null) {
				$url = self::call("url")->load($sURL);
				$sURL = $url->update("params", $mContent)->get();
			}

			$curl = \curl_init($sURL);
			if($this->port!==null) { \curl_setopt($curl, CURLOPT_PORT, $this->port); }
			\curl_setopt($curl, CURLOPT_HEADER, true);
			\curl_setopt($curl, CURLOPT_HTTPHEADER, $aHeaders);
			\curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			\curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			\curl_setopt($curl, CURLOPT_SSLVERSION, $this->sslversion);
			\curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->sslverify);
			\curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->sslverify);

			if($sMethod!="GET") {
				\curl_setopt($curl, CURLOPT_POST, 1);
				if($mContent!==null) {
					\curl_setopt($curl, CURLOPT_POSTFIELDS, $mContent);
				}
			}
			$sBuffer = \curl_exec($curl);
			if(\curl_errno($curl)) { $sBuffer = "REQUEST ERROR curl: ".\curl_error($curl); }

			$nHeaderSize = \curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$sHeaders = \substr($sBuffer, 0, $nHeaderSize);
			$sBody = \substr($sBuffer, $nHeaderSize);

			$aResponse = [
				"code" => \curl_getinfo($curl, CURLINFO_RESPONSE_CODE),
				"time" => \curl_getinfo($curl, CURLINFO_TOTAL_TIME),
				"size" => \curl_getinfo($curl, CURLINFO_SIZE_DOWNLOAD),
				"header" => $this->parseHeaders($sHeaders),
				"body" => $sBody
			];

			\curl_close($curl);
		}

		return $aResponse;
	}

	private function ParseHeaders($sHeaders) {
		$aHeaders = [];
		foreach(\explode("\r\n\r\n", $sHeaders) as $x => $sHeader) {
			if(\strlen($sHeader)) {
				foreach(explode("\r\n", $sHeader) as $y => $sLine) {
					if($y===0) {
						$aHeaders[$x]["http_code"] = \trim($sLine);
					} else {
						list($sKey, $sValue) = explode(": ", $sLine);
						$aHeaders[$x][$sKey] = $sValue;
					}
				}
			}
		}

		return \count($aHeaders)==1 ? $aHeaders[0] : $aHeaders;
	}

	/*
	TODO: qop = auth-int
	*/
	private function Digest($sEntityBody) {
		$mToken = $this->mToken;
		if(!\is_array($mToken)) { return $mToken; }
		if(!\array_key_exists("response", $mToken)) {
			if(!\array_key_exists("algorithm", $mToken)) { self::errorMessage($this->object, 1002); }
			$sAlgorithm = \strtolower($mToken["algorithm"]);

			$sURI = \parse_url($this->url, PHP_URL_PATH);
			$sQuery = \parse_url($this->url, PHP_URL_QUERY);
			if(!empty($sQuery)) { $sURI.="?".$sQuery; }

			if(\substr($sAlgorithm,-5)!="-sess") {
				if(!self::call()->arrayKeysExists(["username","realm","password"], $mToken, true)) { self::errorMessage($this->object, 1003); }
				$sA1 = \hash($sAlgorithm, $mToken["username"].":".$mToken["realm"].":".$mToken["password"]);
			} else {
				$sAlgorithm = \substr($sAlgorithm,0,-5);
				if(!self::call()->arrayKeysExists(["username","realm","password","nonce","cnonce"], $mToken, true)) { self::errorMessage($this->object, 1003); }
				$sA1 = \hash($sAlgorithm, $mToken["username"].":".$mToken["realm"].":".$mToken["password"]);
				$sA1 = \hash($sAlgorithm, $sA1.":".$mToken["nonce"].":".$mToken["cnonce"]);
			}

			if(\array_key_exists("qop", $mToken)) {
				$sNC = !empty($mToken["nc"]) ? $mToken["nc"] : "";
				$sAuthInt = \strtolower($mToken["qop"])=="auth-int" ? ":".\hash($sAlgorithm, $sEntityBody) : "";
				$sA2 = \hash($sAlgorithm, $this->method.":".$sURI.$sAuthInt);
				$mToken["response"] = \hash($sAlgorithm, $sA1.":".$mToken["nonce"].":".$sNC.":".$mToken["cnonce"].":".$mToken["qop"].":".$sA2);
			} else {
				$sA2 = \hash($sAlgorithm, $this->method.":".$sURI);
				$mToken["response"] = \hash($sAlgorithm, $sA1.":".$mToken["nonce"].":".$sA2);
			}
		}

		$aDigest = [];
		foreach($mToken as $sKey => $sValue) {
			$aDigest[] = $sKey.'="'.$sValue.'"';
		}

		return \implode(", ", $aDigest);
	}
}

?>