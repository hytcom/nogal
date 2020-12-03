<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___
  
# crypt
## nglCrypt *extends* nglBranch [instanciable] [2018-08-12]
Implementa la clase 'phpseclib', de algoritmos de encriptación

https://github.com/hytcom/wiki/blob/master/nogal/docs/crypt.md

# error codes
1001 = Invalid key format
1002 = Decrypt Faild! Invalid key or String

*/
namespace nogal;

class nglGraftCrypt extends nglScion {

	private $crypter	= null;
	private $sCrypter	= null;
	private $vAlgorithms	= [];

	final protected function __declareArguments__() {
		$vArguments					= [];
		$vArguments["key"]			= ['$this->SetKey($mValue)', null];
		$vArguments["keyslen"]		= ['(int)$mValue', 512];
		$vArguments["text"]			= ['$mValue'];
		$vArguments["type"]			= ['$this->SetType($mValue)', "aes"];
		$vArguments["base64"]		= ['self::call()->isTrue($mValue)', false];
	
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes 				= [];
		$vAttributes["keyword"] 	= null;
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		$vAlgorithms				= [];
		$vAlgorithms["aes"]			= "\phpseclib\Crypt\AES";
		$vAlgorithms["blowfish"]	= "\phpseclib\Crypt\Blowfish";
		$vAlgorithms["des"]			= "\phpseclib\Crypt\DES";
		$vAlgorithms["tripledes"]	= "\phpseclib\Crypt\TripleDES";
		$vAlgorithms["rc2"]			= "\phpseclib\Crypt\RC2";
		$vAlgorithms["rc4"]			= "\phpseclib\Crypt\RC4";
		$vAlgorithms["rijndael"]	= "\phpseclib\Crypt\Rijndael";
		$vAlgorithms["rsa"]			= "\phpseclib\Crypt\RSA";
		$vAlgorithms["twofish"]		= "\phpseclib\Crypt\Twofish";
		$this->vAlgorithms			= $vAlgorithms;
	}

	public function decrypt() {
		list($sString) = $this->getarguments("text", \func_get_args());
		if($this->sCrypter===null) { $this->SetType(); }
		if($this->sCrypter=="\phpseclib\Crypt\RSA") { $this->RSAMode(); }
		if($this->argument("base64")) { $sString = \base64_decode($sString); }
		
		self::errorMode("print");
		\ob_start();
		$sDecrypted = $this->crypter->decrypt($sString);
		$sError = \ob_get_contents();
		\ob_end_clean();
		if(\strlen($sError)) { self::errorMode("die"); self::errorMessage($this->object, 1002, $sError); }
		self::errorModeRestore();
		return ($sString!="") ? $sDecrypted : "";
	}

	public function encrypt() {
		list($sString) = $this->getarguments("text", \func_get_args());
		if($this->sCrypter===null) { $this->SetType(); }
		if($this->sCrypter=="\phpseclib\Crypt\RSA") { $this->RSAMode(); }
		$sString = ($sString!="") ? $this->crypter->encrypt($sString) : "";
		if($this->argument("base64")) { $sString = \base64_encode($sString); }
		return $sString;
	}

	public function keys() {
		list($nLength) = $this->getarguments("keyslen", \func_get_args());

		if($nLength<256) { $nLength = 512; }
		if($this->sCrypter=="\phpseclib\Crypt\RSA") {
			$vKeys = $this->crypter->createKey($nLength);
			return ["private"=>$vKeys["privatekey"], "public"=>$vKeys["publickey"]];
		}

		return false;
	}

	public function chKeys($sKey1, $sKey2) {
		if($this->sCrypter===null) { $this->SetType(); }
		if($this->sCrypter=="\phpseclib\Crypt\RSA") { $this->RSAMode(); }
		$sTest = \md5(\microtime());
		\ob_start();
		$this->SetKey($sKey1);
		$sEncrypted = $this->crypter->encrypt($sTest);
		$this->SetKey($sKey2);
		$sDecrypted = $this->crypter->decrypt($sEncrypted);
		\ob_end_clean();
		return ($sTest===$sDecrypted) ? true : false;
	}
	
	private function RSAMode() {
		if($this->attribute("keyword")===null) { return false; }
		$sKey = $this->attribute("keyword");
		$this->crypter->setMGFHash("sha512");

		for($x=0; $x<8; $x++) {
			$key = $this->crypter->_parseKey($sKey, $x);
			if($key!==false) { break; }
		}

		if($key===null) { self::errorMode("die"); self::errorMessage($this->object, 1001); }
		$nModulus = \strlen($key["modulus"]->toBytes());
		if(($nModulus - 2 * $this->crypter->hLen - 2) > 0) {
			$this->crypter->setEncryptionMode(1);
		} else if(($nModulus - 11) > 0) {
			$this->crypter->setEncryptionMode(2);
		} else {
			$this->crypter->setEncryptionMode(3);
		}
	}

	protected function SetKey($sKey=null) {
		if($this->sCrypter===null) { $this->SetType(); }
		if($sKey!==null) {
			$this->attribute("keyword", $sKey);
			if(\method_exists($this->crypter, "setKey")) {
				$this->crypter->setKey($sKey);
			} else if(\method_exists($this->crypter, "loadKey")) {
				$this->crypter->loadKey($sKey);
			}
		}

		return $this;
	}
	
	protected function SetType($sCrypter="aes") {
		$sCrypter = \strtolower($sCrypter);
		$sAlgorithm = (isset($this->vAlgorithms[$sCrypter])) ? $this->vAlgorithms[$sCrypter] : $this->vAlgorithms["aes"];
		$this->sCrypter = $sAlgorithm;
		$this->crypter = new $this->sCrypter();
		
		// reasignacion de key
		$sKey = $this->attribute("keyword");
		if($sKey!==null) {
			if(\method_exists($this->crypter, "setKey")) {
				$this->crypter->setKey($sKey);
			} else if(\method_exists($this->crypter, "loadKey")) {
				$this->crypter->loadKey($sKey);
			}
		}

		return $this;
	}
}

?>