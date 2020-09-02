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
1001 = Clave de encriptación indefinida
1002 = Token inválido o vacío
1003 = Clave grants duplicada
1004 = Clave grants indefinida
1005 = No se pudieron salvar las claves. Permiso denegado
1006 = Error en la ruta
1007 = Clave pública indefinida
1008 = Clave privada indefinida
1009 = Nombre de usuario incorrecto para el TOKEN
1010 = Passphrase indefinida
1011 = Nombre de permiso inválido
1012 = No se pudieron cargar los permisos

*/
namespace nogal;

class nglAlvin extends nglFeeder implements inglFeeder {

	private $aToken;
	private $aGeneratedKeys;
	private $sKeysPath;
	private $sCryptKey;
	private $sPrivateKey;
	private $sPassphrase;
	private $sGrantsFile;
	private $aGrants;
	private $aRAW;
	private $sDefaultGrants;
	private $crypt;

	final public function __init__($mArguments=null) {
		$this->aToken = null;
		$this->aGeneratedKeys = array();
		$this->sCryptKey = NGL_ALVIN;
		$this->sPrivateKey = null;
		$this->sPassphrase = null;
		$this->aGrants = array();
		$this->aRAW = array();
		$this->sGrantsFile = null;
		$this->sDefaultGrants = '{"GRANTS":{"profiles":{"ADMIN":[]}},"RAW":[]}';
		$this->crypt = (self::call()->exists("crypt")) ? self::call("crypt") : null;
		$this->sKeysPath = NGL_PATH_DATA.NGL_DIR_SLASH."alvin";
		if($this->crypt!==null) { $this->crypt->type("rsa")->base64(true); }
		$this->__errorMode__("die");
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
		return self::errorMessage($this->object, 1001);
	}

	public function saveKeys() {
		if(!is_dir($this->sKeysPath)) {
			if(!@mkdir($this->sKeysPath, 0775, true)) {
				self::errorMessage($this->object, 1005, $this->sKeysPath);
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
					return self::errorMessage($this->object, 1008);
				}
			}
			$sKey = preg_replace(array("/-----BEGIN RSA PRIVATE KEY-----/is", "/-----END RSA PRIVATE KEY-----/is", "/[\s]*/is"), array(""), $sKey);
			$this->sPrivateKey = $sKey;
		} else {
			if($sKey===null) {
				if(file_exists($this->sKeysPath.NGL_DIR_SLASH."public.key")) {
					$sKey = file_get_contents($this->sKeysPath.NGL_DIR_SLASH."public.key");
				} else {
					return self::errorMessage($this->object, 1007);
				}
			}
			$sKey = preg_replace(array("/-----BEGIN PUBLIC KEY-----/is", "/-----END PUBLIC KEY-----/is", "/[\s]*/is"), array(""), $sKey);
			$this->sCryptKey = $sKey;
		}
		return $this;
	}

	// ADMIN GRANTS ------------------------------------------------------------
	// carga o crea los permisos
	public function loadGrants($sFilePath, $sPassphrase=null) {
		if($sPassphrase===null) { return self::errorMessage($this->object, 1010); }
		$grants = self::call("file")->load($sFilePath);
		if($grants->size) {
			$this->sGrantsFile = $sFilePath;
			$sGrants = $grants->read();
			$sGrants = preg_replace("/(\n|\r)/is", "", $sGrants);
			$grants->close();
			$sGrants = $sGrants = self::call("crypt")->type("aes")->key($sPassphrase)->base64(true)->decrypt($sGrants);
		} else {
			$sGrants = $this->sDefaultGrants;
		}
		return $this->jsonGrants($sGrants);
	}

	// escribe el archivo con los permisos
	public function save($sFilePath=null, $sPassphrase=null) {
		if($sPassphrase===null) { return self::errorMessage($this->object, 1010); }
		if($sFilePath===null) {
			if($this->sGrantsFile!==null) {
				$sFilePath = $this->sGrantsFile;
			} else {
				return self::errorMessage($this->object, 1006);
			}
		}

		$sGrants = json_encode(array("GRANTS"=>$this->aGrants, "RAW"=>$this->aRAW));
		$sGrants = self::call("crypt")->type("aes")->key($sPassphrase)->base64(true)->encrypt($sGrants);

		$save = self::call("file")->load($sFilePath);
		if($save->write(chunk_split($sGrants, 80))!==false) {
			$save->close();
			return true;
		}

		return false;
	}

	// importa los permisos desde una cadena plana o un json
	public function import($sGrants) {
		if($sGrants[0]=="{") {
			return $this->jsonGrants($sGrants);
		} else {
			$aGrants = self::call()->strToArray($sGrants);
			$this->jsonGrants($this->sDefaultGrants);
			foreach($aGrants as $sRow) {
				$aRow = preg_split("/(\t|;|,)/is", $sRow);
				$sGroup = trim(array_shift($aRow));
				$this->setGrant("groups", $sGroup, array());
				foreach($aRow as $sGrant) {
					$sGrant = trim($sGrant);
					if(empty($sGrant)) { break; }
					$this->setGrant("grants", $sGrant, $sGrant);
					$this->setGrant("groups", $sGroup, array($sGrant=>$sGrant), 1);
				}
			}
		}

		return $this;
	}

	public function export($bPretty=false) {
		$sGrants = json_encode(array("GRANTS"=>$this->aGrants, "RAW"=>$this->aRAW));
		return ($bPretty) ? self::call("shift")->jsonFormat($sGrants) : $sGrants;
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
		if($sType!==null && isset($this->aGrants[$sType])) { return $this->aGrants[$sType]; }
		return array();
	}

	// retorna un permiso con su composicion
	public function grant($sName=null, $sType="grants") {
		$this->chkType($sType);
		$sName = $this->GrantName($sName);
		$sType = strtolower($sType);
		if($sType!==false && $sName!==null && isset($this->aGrants[$sType][$sName])) {
			if($sType=="profiles" && $sName=="ADMIN") {
				return array("ADMIN"=>array());
			} else {
				return $this->aGrants[$sType][$sName];
			}
		}
		return false;
	}

	// agrega un permiso a la estructura
	public function setGrant($sType, $sName, $mGrant) {
		$this->chkType($sType);
		$sName = $this->GrantName($sName);
		if($sName===false) { self::errorMessage($this->object, 1011, null, "die"); }
		
		$sIndex = $this->FindGrant($sName, false, $sType);
		
		// valor a array
		if(is_array($mGrant)) {
			$mGrant = array_unique($mGrant);
		} else {
			if($sType!="grants") { $mGrant = array($mGrant); }
		}

		// nuevo registro
		if($sIndex===false) {
			if($sType=="grants") {
				$this->aGrants["grants"][$sName] = array();
			} else if($sType=="groups") {
				$this->MakeGroup($sName, $mGrant, true);
				ksort($this->aGrants["groups"][$sName]);
			} else {
				if($sName=="ADMIN") { return $this; }
				$this->MakeProfile($sName, $mGrant, true);
			}
		} else { // edicion
			if($sType=="groups") {
				$this->MakeGroup($sIndex, $mGrant);
				ksort($this->aGrants["groups"][$sIndex]);
			} else if($sType=="profiles") {
				if($sIndex=="ADMIN") { return $this; }
				$this->MakeProfile($sIndex, $mGrant);
			}
		}

		return $this;
	}

	private function MakeGroup($sName, $aGrants, $bNew=false) {
		if($bNew) { $this->aGrants["groups"][$sName] = array(); }
		foreach($aGrants as $sGrant) {
			$sGrant = $this->GrantName($sGrant);
			if(array_key_exists($sGrant, $this->aGrants["grants"])) {
				$this->aGrants["groups"][$sName][$sGrant] = array();
				$this->aGrants["grants"][$sGrant][$sName] = true;
			}
		}
		
		ksort($this->aGrants["groups"]);
		ksort($this->aGrants["groups"][$sName]);
	}

	private function MakeProfile($sName, $aGrants, $bNew=false) {
		if($bNew) { $this->aGrants["profiles"][$sName] = array(); }
		foreach($aGrants as $sGrant) {
			if($sGrant[0]=="-") { $sGrant = substr($sGrant, 1); $bRemove = true; }
			$sGrant = $this->GrantName($sGrant, true);
			$aGrant = explode(".", $sGrant);
			if(isset($this->aGrants["groups"][$aGrant[0]])) {
				if(isset($aGrant[1])) {
					if(!isset($this->aGrants["groups"][$aGrant[0]][$aGrant[1]])) { continue; }
					if(isset($bRemove)) {
						unset($this->aGrants["groups"][$aGrant[0]][$aGrant[1]][$sName]);
						unset($this->aGrants["profiles"][$sName][$aGrant[0]][$aGrant[1]]);
					} else {
						$this->aGrants["groups"][$aGrant[0]][$aGrant[1]][$sName] = true;
						$this->aGrants["profiles"][$sName][$aGrant[0]][$aGrant[1]] = true;
					}
				} else {
					if(!isset($bRemove)) {
						foreach($this->aGrants["groups"][$aGrant[0]] as $sGrant => $sTrue) {
							$this->aGrants["groups"][$aGrant[0]][$sGrant][$sName] = true;
						}
						$this->aGrants["profiles"][$sName][$aGrant[0]] = self::call()->truelize(array_keys($this->aGrants["groups"][$aGrant[0]]));
					} else {
						foreach($this->aGrants["profiles"][$sName][$aGrant[0]] as $sGrant => $sTrue) {
							unset($this->aGrants["groups"][$aGrant[0]][$sGrant][$sName]);
							unset($this->aGrants["profiles"][$sName][$aGrant[0]][$sGrant]);
						}
						unset($this->aGrants["profiles"][$sName][$aGrant[0]]);
					}
				}
			}
		}

		ksort($this->aGrants["profiles"]);
		ksort($this->aGrants["profiles"][$sName]);
	}

	// elimina un permiso, grupo o perfil
	public function unsetGrant($sType, $sName) {
		$this->chkType($sType);
		$sName = $this->GrantName($sName);

		if($sName!==false) { 
			if($sType=="grants") {
				foreach($this->aGrants["grants"][$sName] as $sGroup => $bVal) {
					foreach($this->aGrants["groups"][$sGroup][$sName] as $sProfile => $bVal) {
						unset($this->aGrants["profiles"][$sProfile][$sGroup][$sName]);
					}					
					unset($this->aGrants["groups"][$sGroup][$sName]);
				}
			} else if($sType=="groups") {
				foreach($this->aGrants["groups"][$sName] as $sGrant => $aProfiles) {
					if(count($aProfiles)) {
						foreach($aProfiles as $sProfile => $bVal) {
							unset($this->aGrants["profiles"][$sProfile][$sName]);
						}
					}
				}
			} else {
				if($sName=="ADMIN") { return $this; }
				foreach($this->aGrants["profiles"][$sName] as $sGroup => $aGrants) {
					if(count($aGrants)) {
						foreach($aGrants as $sGrant => $bVal) {
							unset($this->aGrants["groups"][$sGroup][$sGrant][$sName]);
						}
					}
				}
			}

			unset($this->aGrants[$sType][$sName]);
			ksort($this->aGrants[$sType]);
		}
		return $this;
	}

	// genera el token del usuario
	public function token($sProfileName, $aGrants=array(), $aRaw=array(), $sUsername=null) {
		if($sUsername!==null) { $sUsername = $this->username($sUsername); }
		if(!$this->crypt) { return self::errorMessage($this->object, 1001); }

		$sProfileName = trim($sProfileName);
		$sProfileName = strtoupper($sProfileName);
		$aToken = array("profile"=>$sProfileName, "grants"=>null,"raw"=>null);

		// permisos
		if(is_array($aGrants) && count($aGrants)) {
			$aToken["grants"] = $this->PrepareGrants($aGrants); 
		}

		// permisos crudos
		if(is_array($aRaw) && count($aRaw)) { $aToken["raw"] = $aRaw; }

		$sTokenContent = serialize($aToken);
		if($this->crypt) {
			if(!$this->sPrivateKey) { return self::errorMessage($this->object, 1008); }
			$sTokenContent = $this->crypt->type("rsa")->key($this->sPrivateKey)->encrypt($sTokenContent);
		}
		$sTokenContent = base64_encode($sTokenContent);
		$sTokenContent = base64_encode($this->password($sUsername))."@".$sTokenContent;

		$sToken	 = "/-- NGL ALVIN TOKEN -------------------------------------------------------/\n";
		$sToken	.= chunk_split($sTokenContent);
		$sToken	.= "/------------------------------------------------------- NGL ALVIN TOKEN --/";

		return $sToken;
	}

	//
	private function GrantName($sGrant, $bDot=false) {
		$sGrant = self::call()->unaccented($sGrant);
		$sRegex = (!$bDot) ? "/[^a-zA-Z0-9]+/" : "/[^a-zA-Z0-9\-\_\.]+/";
		return strtoupper(preg_replace($sRegex, "", $sGrant));
	}

	private function PrepareGrants($aProfile) {
		if(array_key_exists("ADMIN", $aProfile)) { $aProfile["grants"] = $this->aGrants["groups"]; }
		$aToken = array();
		foreach($aProfile as $sGroup => $aGrants) {
			$aToken[$sGroup] = true;
			if(!is_array($aGrants) || !count($aGrants)) { continue; }
			foreach($aGrants as $sGrant => $bVal) {
				$aToken[$sGroup.".".$sGrant] = true;
			}
		}
		return $aToken;
	}
	
	// decodifica los permisos json
	private function jsonGrants($sGrants) {
		$aGrants = json_decode($sGrants, true);
		if($aGrants!==null) {
			if(array_key_exists("GRANTS", $aGrants)) { $this->aGrants = $aGrants["GRANTS"]; }
			if(array_key_exists("RAW", $aGrants)) { $this->aRAW = $aGrants["RAW"]; }
			if(!array_key_exists("GRANTS", $aGrants) && !array_key_exists("RAW", $aGrants)) { $this->aGrants = $aGrants; }
		} else {
			return self::errorMessage($this->object, 1012);
		}

		return $this;
	}

	// busca permisos
	private function FindGrant($sName, $bRecursive=false, $sType="grants") {
		$sType = strtolower($sType);
		if(isset($this->aGrants[$sType], $this->aGrants[$sType][$sName])) { return $sName; }
		return false;
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

	// valida un nombre de perfil
	public function profile($sProfile) {
		return $this->GrantName($sProfile, false);
	}

	// valida un nombre de usuario
	public function username($sUsername) {
		$sUsername = self::call()->unaccented($sUsername);
		return preg_replace("/[^a-zA-Z0-9\_\-\.\@]+/", "", $sUsername);
	}

	// encripta un password
	public function password($sPassword) {
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
			}
			return false;				
		} else {
			$aReturn = array();
			$bNone = true;
			foreach($aToCheck as $sGrant) {
				if(isset($this->aToken["grants"][$sGrant])) {
					if($sMode=="any") { return true; }
					$aReturn[$sGrant] = true;
					$bNone = false;
				} else {
					if($sMode=="all") { return false; }
					$aReturn[$sGrant] = false;
				}

			}

			if($sMode=="none") { return $bNone; }
			if($sMode=="all") { return true; }
			if($sMode=="any") { return false; }

			return $aReturn;
		}
	}
}

?>