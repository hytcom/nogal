<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___
  
# alvin
## nglAlvin *extends* nglFeeder [2018-10-28]
Alvin es el sistema de seguridad de **nogal**, encargado de gestionar permisos, grupos y perfiles de usuario.  
Mas que un objeto es un concepto que atraviesa transversalmente todo el framework. 

https://github.com/hytcom/wiki/blob/master/nogal/docs/alvin.md
https://github.com/hytcom/wiki/blob/master/nogal/docs/alvinuso.md

#errors
1001 = "Clave de encriptación indefinida"
1002 = "Token inválido o vacío"
1003 = "Clave grants duplicada"
1004 = "Clave grants indefinida"
1005 = "No se pudieron salvar las claves. Permiso denegado"
1006 = "Error en la ruta"
1007 = Clave pública indefinida
1008 = Clave privada indefinida
1009 = Nombre de usuario incorrecto para el TOKEN
1010 = Passhrase indefinida

*/
namespace nogal;

class nglAlvin extends nglFeeder implements inglFeeder {

	private $aToken;
	private $aGeneratedKeys;
	private $sKeysPath;
	private $sCryptKey;
	private $sPrivateKey;
	private $sPasshrase;
	private $sGrantsFile;
	private $aGrants;
	private $crypt;

	final public function __init__($mArguments=null) {
		$this->aToken = null;
		$this->aGeneratedKeys = array();
		$this->sCryptKey = NGL_ALVIN;
		$this->sPrivateKey = null;
		$this->sPasshrase = null;
		$this->aGrants = array();
		$this->sGrantsFile = null;
		$this->crypt = (self::call()->exists("crypt")) ? self::call("crypt") : null;
		$this->sKeysPath = NGL_PATH_DATA.NGL_DIR_SLASH."alvin";
		if($this->crypt!==null) { $this->crypt->type("rsa")->base64(true); }
		self::errorMode("die");
	}

	// KEYS --------------------------------------------------------------------
	public function keys($bSet=false, $bReturnKeys=false) {
		if($this->crypt) { 
			$this->aGeneratedKeys = self::call("crypt")->type("rsa")->keys();
			if($bSet) {
				$this->setkey(true, $this->aGeneratedKeys["private"]);
				$this->setkey(false, $this->aGeneratedKeys["public"]);
			}
			return ($bReturnKeys) ? $this->aGeneratedKeys : $this;
		}
		self::errorMessage($this->object, 1001);
	}

	public function saveKeys() {
		if(!is_dir($this->sKeysPath)) {
			if(!@mkdir($this->sKeysPath, 0775, true)) {
				return self::errorMessage($this->object, 1005, $this->sKeysPath);
			}
		}
		@file_put_contents($this->sKeysPath.NGL_DIR_SLASH."private.key", $this->aGeneratedKeys["private"]);
		@file_put_contents($this->sKeysPath.NGL_DIR_SLASH."public.key", $this->aGeneratedKeys["public"]);

		return $this;
	}

	public function setkey($bPrivate=false, $sKey=null) {
		if(!$this->crypt) { self::errorMessage($this->object, 1001); }
		if($bPrivate) {
			if($sKey===null) {
				if(file_exists($this->sKeysPath.NGL_DIR_SLASH."private.key")) {
					$sKey = file_get_contents($this->sKeysPath.NGL_DIR_SLASH."private.key");
				} else {
					self::errorMessage($this->object, 1008);
				}
			}
			$sKey = preg_replace(array("/-----BEGIN RSA PRIVATE KEY-----/is", "/-----END RSA PRIVATE KEY-----/is", "/[\s]*/is"), array(""), $sKey);
			$this->sPrivateKey = $sKey;
		} else {
			if($sKey===null) {
				if(file_exists($this->sKeysPath.NGL_DIR_SLASH."public.key")) {
					$sKey = file_get_contents($this->sKeysPath.NGL_DIR_SLASH."public.key");
				} else {
					self::errorMessage($this->object, 1007);
				}
			}
			$sKey = preg_replace(array("/-----BEGIN PUBLIC KEY-----/is", "/-----END PUBLIC KEY-----/is", "/[\s]*/is"), array(""), $sKey);
			$this->sCryptKey = $sKey;
		}
		return $this;
	}

	// ADMIN GRANTS ------------------------------------------------------------
	// carga o crea los permisos
	public function loadGrants($sFilePath, $sPasshrase=null) {
		$grants = self::call("file")->load($sFilePath);
		if($grants->size) {
			$this->sGrantsFile = $sFilePath;
			if(!$sPasshrase) { self::errorMessage($this->object, 1010); }
			$sGrants = $grants->read();
			$grants->close();
			$sGrants = $sGrants = self::call("crypt")->type("aes")->key($sPasshrase)->base64(true)->decrypt($sGrants);
		} else {
			$sGrants = '{"GRANTS":[],"RAW":[]}';
		}
		return $this->jsonGrants($sGrants);
	}

	// escribe el archivo con los permisos
	public function save($sFilePath=null, $sPasshrase=null) {
		if(!$sPasshrase) { self::errorMessage($this->object, 1010); }
			if($sFilePath===null) {
			if($this->sGrantsFile!==null) {
				$sFilePath = $this->sGrantsFile;
			} else {
				self::errorMessage($this->object, 1006);
			}
		}

		$sGrants = json_encode(array("GRANTS"=>$this->aGrants, "RAW"=>$this->aRAW));
		$sGrants = self::call("crypt")->type("aes")->key($sPasshrase)->base64(true)->encrypt($sGrants);

		$save = self::call("file")->load($sFilePath);
		if($save->write($sGrants)!==false) {
			$save->close();
			return true;
		}

		return false;
	}

	// importa los permisos desde una cadena json
	public function import($sGrants) {
		return $this->jsonGrants($sGrants);
	}
	
	// retorna todos los permisos del tipo raw
	public function getraw($sProfile=null) {
		if($sProfile!==null) {
			if(isset($this->aRAW[$sProfile])) {
				return $this->aRAW[$sProfile];
			} else {
				return false;
			}
		}

		return $this->aRAW;
	}

	// agrega o sobreescribe los permisos raw de un perfil
	public function setraw($sProfile, $aValue, $bAppend=false) {
		if($bAppend) { $aValue = self::call()->arrayMerge($this->aRAW[$sProfile], $aValue); }
		$this->aRAW[$sProfile] = $aValue;
		return $this;
	}

	// elimina un perfil de los permisos raw
	public function unsetraw($sProfile) {
		unset($this->aRAW[$sProfile]);
		return $this;
	}

	// retorna todos los permisos del tipo grant
	public function getall() {
		return $this->aGrants;
	}

	// listado de permisos segun el tipo (grants|groups|profiles)
	public function get($sType=null) {
		if($sType!==null) {
			return $this->aGrants[$sType];
		}
		return $this->getall();
	}

	// retorna un permiso con su composicion
	public function grant($sName=null, $sType="grants") {
		$this->chkType($sType);
		if($sType!==false && $sName!==null && isset($this->aGrants[$sType][$sName])) {
			if($sType=="grants") {
				return $this->aGrants[$sType][$sName];
			} else {
				return $this->GetGrant($sType, $sName);
			}
		}
		return false;
	}

	// agrega un permiso a la estructura
	public function setGrant($sType, $sName, $mGrant, $nMode=0) {
		$this->chkType($sType);
		$nIndex = $this->FindGrant($sName, false, $sType);
		if($nIndex!==false && !$nMode) { $this->aGrants[$sType][$nIndex] = array(); }
		
		if(is_array($mGrant)) {
			$mGrant = array_unique($mGrant);
		} else {
			if($sType!="grants") { $mGrant = array($mGrant); }
		}

		if($nIndex===false) {
			if($sType=="grants") {
				$this->aGrants[$sType][$sName] = $sName;
			} else {
				$this->aGrants[$sType][$sName] = $mGrant;
			}
		} else {
			if($sType=="grants") {
				$this->aGrants[$sType][$nIndex] = array($sName, $sName);
			} else {
				if($nMode===2) {
					foreach($mGrant as $sGrant) {
						$nKey = array_search($sGrant, $this->aGrants[$sType][$nIndex]);
						if($nKey!==false) { unset($this->aGrants[$sType][$nIndex][$nKey]); }
					}
				} else {
					$this->aGrants[$sType][$nIndex] = array_merge($this->aGrants[$sType][$nIndex], $mGrant);
				}

				$this->aGrants[$sType][$nIndex] = array_unique($this->aGrants[$sType][$nIndex]);
			}
		}
		return $this;
	}

	// elimina un permiso, grupo o perfil
	public function unsetGrant($sType, $sName) {
		$this->chkType($sType);
		if($sName!==false) { unset($this->aGrants[$sType][$sName]); }
		return $this;
	}

	// genera el token del usuario
	public function token($sProfileName, $aGrants=array(), $aRaw=array(), $sUsername=null) {
		if($sUsername!==null) { $sUsername = $this->username($sUsername); }
		if(!$this->crypt) { self::errorMessage($this->object, 1001); }

		$sProfileName = trim($sProfileName);
		$sProfileName = strtoupper($sProfileName);
		$aToken = array("profile"=>$sProfileName, "grants"=>null,"raw"=>null);

		// permisos
		if(is_array($aGrants) && count($aGrants)) {
			$aGrants = $this->PrepareGrants($aGrants); 
			$aToken["grants"] = self::call()->truelize($aGrants);
		}

		// permisos crudos
		if(is_array($aRaw) && count($aRaw)) { $aToken["raw"] = $aRaw; }

		if($this->crypt) {
			if(!$this->sPrivateKey) { self::errorMessage($this->object, 1008); }
			$sTokenContent = $this->crypt->type("rsa")->key($this->sPrivateKey)->encrypt(serialize($aToken));
		} else {
			$sTokenContent = serialize($aToken);
		}
		$sTokenContent = base64_encode($sTokenContent);
		$sTokenContent = base64_encode($this->password($sUsername))."@".$sTokenContent;

		$sToken	 = "/-- NGL ALVIN TOKEN -------------------------------------------------------/\n";
		$sToken	.= chunk_split($sTokenContent);
		$sToken	.= "/------------------------------------------------------- NGL ALVIN TOKEN --/";

		return $sToken;
	}

	//
	private function PrepareGrants($aGrants) {
		return array_map(function($v) {
			return strtoupper(preg_replace("/[^a-zA-Z0-9\_\-\.]+/", "", $v));
		}, $aGrants);
	}
	
	// decodifica los permisos json
	private function jsonGrants($sGrants) {
		$aGrants = json_decode($sGrants, true);
		if($aGrants!==null) {
			if(array_key_exists("GRANTS", $aGrants)) { $this->aGrants = $aGrants["GRANTS"]; }
			if(array_key_exists("RAW", $aGrants)) { $this->aRAW = $aGrants["RAW"]; }
			if(!array_key_exists("GRANTS", $aGrants) && !array_key_exists("RAW", $aGrants)) { $this->aGrants = $aGrants; }
		} else {
			self::errorMessage($this->object, 1005);
		}

		return $this;
	}

	// busca permisos
	private function FindGrant($sName, $bRecursive=false, $sType="grants") {
		$sType = strtolower($sType);
		if(!$bRecursive) {
			if(isset($this->aGrants[$sType], $this->aGrants[$sType][$sName])) {
				return $sName;
			}
		} else {
			$aFound = array();
			foreach($this->aGrants[$sType] as $sChkName) {
				if($sChkName==$sGrant || strpos($sChkName, $sGrant.".")===0) {
					$aFound[$sChkName] = $sChkName;
				}
			}

			uasort($aFound, function ($a, $b) {
				return (strlen($a) > strlen($b));
			});

			return $aFound;
		}

		return false;
	}

	// retorna la estructura de un permiso y su composicion
	private function GetGrant($sType, $sName) {
		$aRemove = $aReturn = array();
		$aGrants = $this->aGrants[$sType][$sName];

		// grupos
		foreach($aGrants as $sGrant) {
			$sSign = "";
			if($sGrant[0]=="-") { $sGrant = substr($sGrant, 1); $sSign = "-"; }
			if($sType=="profiles" && isset($this->aGrants["groups"][$sGrant])) {
				foreach($this->aGrants["groups"][$sGrant] as $sGrantOfGroup) {
					$sGroupSign = "";
					if($sGrantOfGroup[0]=="-") { $sGrantOfGroup = substr($sGrantOfGroup, 1); $sGroupSign = "-"; }
					if($sSign=="-") { $aRemove[$sGrantOfGroup] = true; }
					if($sGroupSign=="-") { $aRemove[$sGrantOfGroup] = true; }
					$aReturn[$sGrantOfGroup] = true;
				}
			}

			if(isset($this->aGrants["grants"][$sGrant])) {
				$aReturn[$sGrant] = true;
			}
		}

		$aGrants = array_keys($aReturn);
		$aReturn = array();

		// permisos individuales
		foreach($aGrants as $sGrant) {
			$sSign = "";
			if($sGrant[0]=="-") { $sGrant = substr($sGrant, 1); $sSign = "-"; }
			if(isset($this->aGrants["grants"][$sGrant])) {
				if(strpos($sGrant, ".")===false) {
					foreach($this->aGrants["grants"] as $sGrantChk) {
						if($sGrantChk==$sGrant || strpos($sGrantChk, $sGrant.".")===0) {
							if($sSign=="-") { $aRemove[$sGrantChk] = true; }
							$aReturn[$sGrantChk] = true;
						}
					}
				} else {
					if($sSign=="-") { $aRemove[$sGrant] = true; }
					$aReturn[$sGrant] = true;
				}
			} else if(substr($sGrant, -1)==".") {
				foreach($this->aGrants["grants"] as $sGrantChk) {
					if(strpos($sGrantChk, $sGrant)===0) {
						if($sSign=="-") { $aRemove[$sGrantChk] = true; }
						$aReturn[$sGrantChk] = true;
					}
				}
			}
		}

		foreach($aRemove as $sKey => $bVal) {
			if(strpos($sKey, ".")!==false) {
				$aKey = explode(".", $sKey);
				unset($aReturn[$aKey[0]]);
			}
			unset($aReturn[$sKey]);
		}

		return array_keys($aReturn);
	}

	private function chkType(&$sType) {
		$sType = strtolower($sType);
		return (in_array($sType, array("grants", "groups", "profiles"))) ? $sType : false;
	}

	// USE GRANTS --------------------------------------------------------------
	// carga un token
	// tipo de carga de ALVIN-TOKEN (TOKEN|TOKENUSER|PROFILE)
	public function load($sToken=null, $sUsername=null, $sProfile=null) {
		if(!$this->crypt) { self::errorMessage($this->object, 1001); }

		// datos insuficientes
		if($sToken===null && $sProfile===null) { return false; }

		// modo de carga
		$sMode = strtoupper(NGL_ALVIN_MODE);

		if($sToken!==null && $sMode!=="PROFILE") {
			$sToken = preg_replace("/\s/is", "", $sToken);
			$sToken = str_replace(array("NGLALVINTOKEN","/---------------------------------------------------------/"), "", $sToken);

			$aToken = explode("@", $sToken);
			if(isset($aToken[1])) {
				if($sUsername!==null) {
					$sUsername = $this->username($sUsername);
					if(base64_decode($aToken[0])!==$this->password($sUsername)) {
						self::errorMessage($this->object, 1009);
						return false;				
					}
				}
				$sToken = $aToken[1];
			} else {
				if($sMode==="TOKENUSER") { return false; }
				$sToken = $aToken[0];
			}
		
			if($this->crypt) {
				$sDecrypt = $this->crypt->type("rsa")->key($this->sCryptKey)->decrypt(base64_decode($sToken));
			} else {
				$sDecrypt = base64_decode($sToken);
			}

			$this->aToken = unserialize($sDecrypt);
		} else if($sProfile!==null) {
			$sProfileName = trim($sProfile);
			$sProfileName = strtoupper($sProfileName);
			$this->aToken = array("profile"=>$sProfileName);
		}

		if(!is_array($this->aToken)) {
			self::errorMessage($this->object, 1002);
			return false;
		}
		
		return $this;
	}

	// verifica que haya un token cargado
	public function loaded() {
		return ($this->aToken!==null);
	}
	
	// valida un nombre de usuario
	public function username($sUsername) {
		return preg_replace("/[^a-zA-Z0-9\_\-\.\@]+/", "", $sUsername);
	}

	// encripta un password
	public function password($sPassword) {
		if(!$this->crypt) { self::errorMessage($this->object, 1001); }
		$sCryptPassword = crypt($sPassword, '$6$rounds=5000$'.md5($this->sCryptKey).'$');
		$aCryptPassword = explode('$', $sCryptPassword, 5);
		return $aCryptPassword[4];
	}

	// ver token
	public function viewtoken() {
		return ($this->aToken!==null) ? $this->aToken : false;
	}

	public function analize($sGrant, $sToken=null) {
		$sGrant = trim($sGrant);
		if(empty($sGrant)) { return false; }
		if($sGrant[0].$sGrant[1]=="?|") {
			$sGrant = substr($sGrant, 2);
		}
		return $this->CheckGrant($sGrant, $sToken, "analize");
	}

	/// chequear si es parte de otro proesupestos.add tiene que matchear con presupuestos(algo)
	public function check($sGrant, $sToken=null) {
		$sGrant = trim($sGrant);
		$sGrant = strtoupper($sGrant);
		if(empty($sGrant)) { return false; }
		if($sGrant[0].$sGrant[1]=="!|") {
			$sGrant = substr($sGrant, 2);
			return $this->CheckGrant($sGrant, $sToken, "none");
		} else if($sGrant[0].$sGrant[1]=="?|") {
			$sGrant = substr($sGrant, 2);
			return $this->CheckGrant($sGrant, $sToken, "any");
		} else {
			return $this->CheckGrant($sGrant, $sToken, "all");
		}
	}

	public function raw($sIndex=null, $aKeyVals=false) {
		$mRaw = null;
		if(!is_array($this->aToken)) { self::errorMessage($this->object, 1002); return false; }
		if(array_key_exists("raw", $this->aToken)) {
			$mRaw = self::call()->arrayFlatIndex($this->aToken["raw"], $sIndex, true);
		}

		if(is_array($aKeyVals)) {
			$mRaw = $this->RawKeywords($mRaw, $aKeyVals);
		}

		return $mRaw;
	}

	private function RawKeywords($mRaw, $aKeyVals) {
		if(is_array($mRaw)) {
			foreach($mRaw as $mKey => $mValue) {
				$mRaw[$mKey] = $this->RawKeywords($mValue, $aKeyVals);
			}
		} else {
			preg_match_all("/\{:([a-z0-9_\.]+):\}/is", $mRaw, $aMatchs);
			if(count($aMatchs[0])) {
				foreach($aMatchs[0] as $x => $sFind) {
					$sReplace = self::call()->arrayFlatIndex($aKeyVals, $aMatchs[1][$x], true);
					$mRaw = str_replace($sFind, $sReplace, $mRaw);
				}
			}
		}

		return $mRaw;
	}

	private function FlatGrants($sName, $aGrants) {
		$aFlat = array($sName=>$sName);
		foreach($aGrants as $sName => $aGrant) {
			if(isset($aGrant["type"]) && $aGrant["type"]=="grant") {
				$aFlat[$sName] = $aGrant["grant"];
			} else {
				$aFlat = array_merge($aFlat, $this->FlatGrants($sName, $aGrant["grant"]));
			}
		}
		return $aFlat;
	}

	public function unload($aUser) {
		$this->aToken = null;
		return $this;
	}

	// primero intenta matchear el nombre del perfil
	// luego busca pertenencias de grupos xxx.
	// finalmente, permisos
	private function CheckGrant($sGrant, $sToken=null, $sMode="analize") {
		if($sToken!=null) { $this->load($sToken); }
		$aToCheck = (strpos($sGrant, ",")===false) ? array($sGrant) : self::call()->explodeTrim(",", $sGrant);

		// nombre del perfil
		if(in_array($this->aToken["profile"], $aToCheck)) { return true; }

		if(count($aToCheck)==1) {
			$sGrant = $aToCheck[0];
			if(isset($this->aToken["grants"][$sGrant])) {
				return ($sMode=="none") ? false : true;
			} else {
				if(substr($sGrant, -1)==".") {
					$aCheck = array_filter($this->aToken["grants"], 
						function($sGrantChk) use ($sGrant) { 
							if(strpos($sGrantChk, $sGrant)===0) {
								return true;
							}
							return false;
						}, 
						ARRAY_FILTER_USE_KEY
					);
					
					if(count($aCheck)) { return true; }
				}
				return ($sMode!="none") ? false : true;
			}
		} else {
			$aReturn = array();
			$bNone = true;
			foreach($aToCheck as $sGrant) {
				if(isset($this->aToken["grants"][$sGrant])) {
					$aReturn[$sGrant] = true;
					$bNone = false;
				} else {
					if(substr($sGrant, -1)==".") {
						$aCheck = array_filter($this->aToken["grants"], 
							function($sGrantChk) use ($sGrant) { 
								if(strpos($sGrantChk, $sGrant)===0) {
									return true;
								}
								return false;
							}, 
							ARRAY_FILTER_USE_KEY
						);
						
						if(count($aCheck)) { return true; }
					}
					$aReturn[$sGrant] = false;
				}

				if($sMode=="any" && $aReturn[$sGrant]) { return true; }
				if($sMode=="all" && !$aReturn[$sGrant]) { return false; }
			}

			if($sMode=="none") { return $bNone; }
			if($sMode=="all") { return true; }
			if($sMode=="any") { return false; }

			return $aReturn;
		}
	}
}

?>