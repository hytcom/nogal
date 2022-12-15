<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# crypt
https://hytcom.net/nogal/docs/objects/crypt.md
*/
namespace nogal;

class nglCrypt extends nglBranch implements inglBranch {

	private $vAlgorithms	= [];
	private $aRSAKeys		= [];
	private $aCiphers		= [];
	private $aDigests		= [];
	private $aKeysFormats	= [];
	private $openkey		= null;
	private $bIsPrivate		= null;

	final protected function __declareArguments__() {
		$vArguments						= [];
		$vArguments["base64"]			= ['self::call()->isTrue($mValue)', true];
		$vArguments["cipher"]			= ['$this->SetCipher($mValue)', "aes-128-ecb"];
		$vArguments["digest"]			= ['$this->SetDigest($mValue)', "sha256"];
		$vArguments["iv"]				= ['($mValue)', ""];
		$vArguments["key"]				= ['$this->SetKey($mValue)', null];
		$vArguments["keylen"]			= ['(int)$mValue', 2048];
		$vArguments["keyname"]			= ['($mValue)', "rsakey"];
		$vArguments["keypath"]			= ['($mValue)', null];
		$vArguments["padding"]			= ['$mValue', null, ["RAW","ZERO","PKCS1","SSLV23","OAEP"]];
		$vArguments["passphrase"]		= ['($mValue)', null];
		$vArguments["tag"]				= ['($mValue)', ""];
		$vArguments["text"]				= ['$mValue'];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes 				= [];
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		$this->aCiphers = \array_fill_keys(\array_map("strtoupper", \openssl_get_cipher_methods(true)), true);
		$this->aCiphers["DH"] = true;
		$this->aCiphers["DSA"] = true;
		$this->aCiphers["RSA"] = true;
		$this->aDigests = \array_fill_keys(\array_map("strtoupper", \openssl_get_md_methods(true)), true);
	}

	final public function __init__() {
		$this->__errorMode__("die");
		$this->aKeysFormats = [
			"DH" => OPENSSL_KEYTYPE_DH,
			"DSA" => OPENSSL_KEYTYPE_DSA,
			"RSA" => OPENSSL_KEYTYPE_RSA
		];
	}

	public function ciphers() {
		return \array_keys($this->aCiphers);
	}

	public function digests() {
		return \array_keys($this->aDigests);
	}

	public function encrypt() {
		list($sString) = $this->getarguments("text", \func_get_args());
		if($this->cipher===null) { $this->SetCipher(); }
		$sEncrypted = $sString;
		if($sString!="") {
			$nPadding = $this->Padding();
			if($this->IsAsymmetric()) {
				$bCrypt = false;
				if($this->isPrivateKey()===true) {
					$bCrypt = \openssl_private_encrypt($sString, $sEncrypted, $this->openkey, $nPadding);
				} else if($this->isPrivateKey()===false) {
					$bCrypt = \openssl_public_encrypt($sString, $sEncrypted, $this->openkey, $nPadding);
				}
				if(!$bCrypt) { self::errorMessage($this->object, 1005); }
			} else {
				$this->setIV($this->iv);
				$this->CheckIV();
				$sEncrypted = @\openssl_encrypt($sString, $this->cipher, $this->key, $nPadding, $this->iv, $sTag);
				$this->tag = \base64_encode($sTag);
			}
		}

		if($this->argument("base64")) { $sEncrypted = \base64_encode($sEncrypted); }
		return $sEncrypted;
	}

	public function decrypt() {
		list($sString) = $this->getarguments("text", \func_get_args());
		if($this->cipher===null) { $this->SetCipher(); }
		$sDecrypted = false;
		if($this->argument("base64")) { $sString = \base64_decode($sString); }
		if($sString!="") {
			$nPadding = $this->Padding();
			if($this->IsAsymmetric()) {
				if($this->isPrivateKey()===true) {
					\openssl_private_decrypt($sString, $sDecrypted, $this->openkey, $nPadding);
				} else if($this->isPrivateKey()===false) {
					\openssl_public_decrypt($sString, $sDecrypted, $this->openkey, $nPadding);
				} else {
					self::errorMessage($this->object, 1006);
				}
			} else {
				$this->setIV($this->iv);
				$this->CheckIV();
				$sTag = !empty($this->argument("tag")) ? \base64_decode($this->tag) : "";
				$sDecrypted = \openssl_decrypt($sString, $this->cipher, $this->key, $nPadding, $this->iv, $sTag);
			}
		}
		return $sDecrypted;
	}

	public function sign() {
		list($sString) = $this->getarguments("text", \func_get_args());
		if($this->cipher===null) { $this->SetCipher(); }
		if($sString!="") {
			if($this->IsAsymmetric()) {
				if($this->isPrivateKey()) {
					\openssl_sign($sString, $sSignature, $this->openkey, $this->digest);
					if($this->argument("base64")) { $sSignature = \base64_encode($sSignature); }
					return $sSignature;
				}
			} else {
				$sSignature = \openssl_digest($sString, $this->digest, true);
				if($this->argument("base64")) { $sSignature = \base64_encode($sSignature); }
				return $sSignature;
			}
		}
		return false;
	}

	public function verify() {
		list($sString,$sSignature) = $this->getarguments("text,signature", \func_get_args());
		if($this->cipher===null) { $this->SetCipher(); }
		if($sString!="") {
			$aVerify = [-1=>null, 0=>false, 1=>true];
			if($this->IsAsymmetric()) {
				if($this->isPublicKey()) {
					if($this->argument("base64")) { $sSignature = \base64_decode($sSignature); }
					$nVerify = \openssl_verify($sString, $sSignature, $this->openkey, $this->digest);
					return $aVerify[$nVerify];
				}
			} else {
				if($this->argument("base64")) { $sSignature = \base64_decode($sSignature); }
				$sSignature = \openssl_digest($sString, $this->digest, true);
				return ($sString==$sSignature);
			}
		}
		return false;
	}

	public function keygen() {
		list($nLength, $sDigest) = $this->getarguments("keylen,digest", \func_get_args());
		if($this->IsAsymmetric()) {
			if($nLength<256) { $nLength = 256; }
			$this->openkey = \openssl_pkey_new([
				"digest_alg" => $sDigest,
				"private_key_bits" => $nLength,
				"private_key_type" => $this->aKeysFormats[$this->cipher]
			]);
			\openssl_pkey_export($this->openkey, $sPrivate, $this->passphrase);
			$this->aRSAKeys = [
				"private"	=> $sPrivate,
				"public"	=> \openssl_pkey_get_details($this->openkey)["key"]
			];
			return $this->aRSAKeys;
		}
		return false;
	}

	protected function SetCipher($sCipher="aes128") {
		$sCipher = \strtoupper($sCipher);
		if(empty($this->aCiphers[$sCipher])) { self::errorMessage($this->object, 1000, $sCipher); }
		return $sCipher;
	}

	protected function SetDigest($sDigest="sha512") {
		$sDigest = \strtoupper($sDigest);
		if(empty($this->aDigests[$sDigest])) { self::errorMessage($this->object, 1007, $sDigest); }
		return $sDigest;
	}

	private function usesIV() {
		return \openssl_cipher_iv_length($this->cipher);
	}

	protected function CheckIV() {
		$sVector = $this->iv;
		if(!empty($sVector)) {
			if(!$nLength = $this->usesIV()) { self::errorMessage($this->object, 1004); }
			if(\strlen($this->iv)!=$nLength) { self::errorMessage($this->object, 1003, $nLength); }
			return $nLength;
		}
		return false;
	}

	protected function SetKey($sKey=null) {
		if($this->cipher===null) { $this->SetCipher(); }
		if($sPath = self::call()->isPath($sKey, "r")) {
			$sKey = self::call()->fileLoad($sPath);
		}

		if(!empty($sKey) && $this->IsAsymmetric()) {
			if($this->IsAsymmetric()) {
				if(\is_resource($this->openkey = \openssl_pkey_get_private($sKey, $this->passphrase))) {
					$this->bIsPrivate = true;
				} else {
					if(\is_resource($this->openkey = \openssl_pkey_get_public($sKey))) { $this->bIsPrivate = false; }
				}
			}
		}
		return $sKey;
	}

	public function isPrivateKey() {
		if($this->IsAsymmetric()) { return $this->bIsPrivate; }
		return false;
	}

	public function isPublicKey() {
		if($this->IsAsymmetric()) { return $this->bIsPrivate ? false : true; }
		return false;
	}

	private function IsAsymmetric() {
		return \in_array($this->cipher, ["DH","DSA","RSA"]);
	}

	protected function Padding() {
		$sPadding = $this->padding;
		if(empty($sPadding)) {
			return $this->IsAsymmetric() ? OPENSSL_PKCS1_PADDING : OPENSSL_RAW_DATA;
		} else {
			$sPadding = \strtoupper($sPadding);
			if($this->IsAsymmetric()) {
				switch($sPadding) {
					case "RAW": return OPENSSL_NO_PADDING;
					case "SSLV23": return OPENSSL_SSLV23_PADDING;
					case "OAEP": return OPENSSL_PKCS1_OAEP_PADDING;
					default: case "PKCS1": return OPENSSL_PKCS1_PADDING;
				}
			} else {
				switch($sPadding) {
					case "ZERO": return OPENSSL_ZERO_PADDING;
					default: case "RAW": OPENSSL_RAW_DATA;
				}
			}
		}
	}

	public function saveKeys() {
		list($sKeyPath,$sFileName) = $this->getarguments("keypath,keyname", \func_get_args());
		if(!\is_writable($sKeyPath)) { self::errorMessage($this->object, 1008, $sKeyPath); }
		if(self::call()->fileSave($sKeyPath.NGL_DIR_SLASH.$sFileName.".pem", $this->aRSAKeys["private"])) {
			if(!self::call()->fileSave($sKeyPath.NGL_DIR_SLASH.$sFileName."_pub.pem", $this->aRSAKeys["public"])) {
				return false;
			}
			return true;
		} else {

		}
		return false;
	}
}

?>