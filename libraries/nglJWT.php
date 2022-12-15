<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# jwt
https://hytcom.net/nogal/docs/objects/jwt.md
*/
namespace nogal;
class nglJWT extends nglBranch implements inglBranch {

	private $aAlgorithms;
	private $crypt;

	final protected function __declareArguments__() {
		$vArguments							= [];
		$vArguments["algorithm"]			= ['$this->SetAlgorithm($mValue)', "HS256"];
		$vArguments["expire"]				= ['$mValue', null]; // basetime GMT 0
		$vArguments["header"]				= ['$mValue', null];
		$vArguments["key"]					= ['$this->SetKey($mValue)', "*V3ryStR0n6k3Y@!"];
		$vArguments["payload"]				= ['(array)$mValue', null];
		$vArguments["token"]				= ['$mValue', null];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = [];
		$vAttributes["algorithm_name"]	= null;
		$vAttributes["algorithm_type"]	= null;
		$vAttributes["jwt"] 			= null;
		$vAttributes["encoded"]			= null;
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
		$this->aAlgorithms = [
			# Name	=> [ Algorithm, Asymmetric, Type]
			"HS256"	=> ["HS256", "sha256", "DIGEST"],
			"HS384"	=> ["HS384", "sha384", "DIGEST"],
			"HS512"	=> ["HS512", "sha512", "DIGEST"],
			"RS256"	=> ["RS256", "sha256", "RSA"],
			"RS384"	=> ["RS384", "sha384", "RSA"],
			"RS512"	=> ["RS512", "sha512", "RSA"]
		];

		$this->algorithm($this->algorithm);
		$this->__errorMode__("die");
	}

	protected function SetAlgorithm($sAlgorithm) {
		$sAlgorithm = \strtoupper($sAlgorithm);
		if(empty($this->aAlgorithms[$sAlgorithm])) { self::errorMessage($this->object, 1000); }
		if($this->aAlgorithms[$sAlgorithm][2]=="RSA") {
			$this->crypt = (self::call()->exists("crypt")) ? self::call("crypt") : null;
			if($this->crypt===null) { self::errorMessage($this->object, 1001); }
			$this->crypt->cipher("rsa")->base64(false);
		}
		$this->attribute("algorithm_name", $this->aAlgorithms[$sAlgorithm][0]);
		$this->attribute("algorithm_type", $this->aAlgorithms[$sAlgorithm][2]);
		return $this->aAlgorithms[$sAlgorithm][1];
	}

	protected function SetKey($sKey) {
		if(!empty($sKey)) {
			if($sPath = self::call()->isPath($sKey, "r")) {
				return self::call()->fileLoad($sPath);
			}
			return $sKey;
		} else {
			return "*V3ryStR0n6k3Y@!";
		}
	}

	public function create() {
		$aArguments = func_get_args();
		if(!empty($aArguments[1])) { $this->key($aArguments[1]); }
		list($mPayload, $sKey, $sAlgorithm) = $this->getarguments("payload,key,algorithm", $aArguments);

		if(!\is_array($mPayload)) {
			if(self::call()->isJSON($mPayload)) {
				$mPayload = \json_decode($mPayload, true);
			} else {
				$mPayload = [$mPayload];
			}
		}

		if(empty($mPayload["iss"])) { $mPayload["iss"] = NGL_PROJECT; }
		$mPayload["iat"] = self::call()->gmTime();
		$mPayload["jti"] = "nogal-token-".self::call()->unique(19);

		$nExpire = 0;
		if($this->expire!==null) {
			$mPayload["exp"] = \is_numeric($this->expire) ? $this->expire : self::call()->gmTime($this->expire);
			$mPayload["gmt"] = \date("O");
			$nExpire = $mPayload["exp"] - $mPayload["iat"];
		}

		$sPayload = \json_encode($mPayload);

		$aHeader = ["alg" => $this->algorithm_name, "typ" => "JWT"];
		if($this->header!==null) {
			$aHeaderAdd = \is_array($this->header) ? : \json_decode($this->header,true);
			$aHeader = \array_merge($aHeader, $aHeaderAdd);
		}
		$sHeader = self::call()->base64URLEncode(\json_encode($aHeader));
		$sPayload = self::call()->base64URLEncode($sPayload);
		$sSignature = $this->Signature($sAlgorithm, $sHeader, $sPayload);

		// JWT
		$sJWT = $sHeader . "." . $sPayload . "." . $sSignature;
		$this->attribute("encoded", $sJWT);
		return \json_encode([
			"access-token"=>$sJWT,
			"expires_in" => $nExpire,
			"token_type" => "bearer"
		]);
	}

	public function verify() {
		list($sToken, $sKey) = $this->getarguments("token,key", func_get_args());
		if(!empty($sKey)) { $this->key($sKey); }

		if($aToken = $this->decode($sToken)) {
			$payload = \json_decode($aToken["payload"]);

			if(!empty($payload->exp)) {
				if(empty($payload->gmt)) {
					if($payload->exp < self::call()->gmTime()) { return false; }
				} else {
					$nPlus = $payload->gmt[0]==="-" ? -1 : 1;
					$nHours = \substr($payload->gmt,1,2) * 60 * 60;
					$nMinutes = \substr($payload->gmt,3,2) * 60;
					$nExpire = $payload->exp + (($nHours + $nMinutes) * $nPlus);
					if($nExpire < time()) { return false; }
				}
			}

			$header = \json_decode($aToken["header"]);
			$this->algorithm($header->alg);

			list($sHeader, $sPayload, $sSign) = \explode(".", $sToken);
			if($this->algorithm_type=="RSA") {
				$bVerified = $this->crypt->digest($this->algorithm)->key($sKey)->verify($sHeader. "." . $sPayload, self::call()->base64URLDecode($sSign));
			} else {
				$bVerified = ($sSign === $this->Signature($this->algorithm, $sHeader, $sPayload));
			}

			if($bVerified) {
				$this->attribute("jwt", $aToken);
				return true;
			}
		}
		return false;
	}

	public function algos() {
		return $this->aAlgorithms;
	}

	public function decode() {
		$this->__errorMode__("boolean");
		list($sToken) = $this->getarguments("token", func_get_args());
		if(empty($sToken)) { return self::errorMessage($this->object, 1003); }
		$aToken = \explode(".", $sToken);
		if(count($aToken)<3) { self::errorMessage($this->object, 1004); return false; }
		$this->__errorMode__("die");
		return [
			"header" => self::call()->base64URLDecode($aToken[0]),
			"payload" => self::call()->base64URLDecode($aToken[1]),
			"sign" => $aToken[2]
		];
	}

	protected function Signature($sAlgorithm, $sHeader, $sPayload) {
		$sClaim = $sHeader. "." . $sPayload;
		if($this->algorithm_type=="RSA") {
			$sSignature = $this->crypt->key($this->key)->digest($sAlgorithm)->sign($sClaim);
		} else {
			$sSignature = \hash_hmac($sAlgorithm, $sClaim, $this->key, true);
		}
		return self::call()->base64URLEncode($sSignature);
	}
}

?>