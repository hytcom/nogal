<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# alvin
https://hytcom.net/nogal/docs/objects/alvin.md
https://hytcom.net/nogal/docs/objects/alvinuso.md
*/
namespace nogal;
class nglAlvin extends nglFeeder implements inglFeeder {

	private $sKeyStore;
	private $sCryptKey;
	private $sPrivateKey;
	private $sPassphrase;
	private $aToken;
	private $aGrants;
	private $aGrantsEmpty;
	private $aGrantsStructures;
	private $aPoliciesTypes;
	private $crypt;
	private $aes;
	private $sAlvinClaim;
	private $sExpireTime;

	final public function __init__($mArguments=null) {
		$this->__errorMode__("die");
		if(NGL_ALVIN===null) {
			self::errorShowSource(false);
			self::errorMessage($this->object, 1000);
		}

		$this->aGeneratedKeys = [];
		$this->sPrivateKey = null;
		$this->sPassphrase = null;
		$this->crypt = self::call("crypt")->cipher("rsa");
		$this->aes = self::call("crypt")->cipher("aes-128-cbc");
		$this->aToken = null;
		$this->aGrants = $this->aGrantsEmpty = [
			"roles" => [],
			"resources" => [],
			"scopes" => [],
			"policies" => [],
			"permissions" => []
		];

		$this->aGrantsStructures = [
			"roles" => ["label","attribs"],
			"resources" => ["label","path"],
			"scopes" => ["label","path"],
			"policies" => ["label","type","value","positive"],
			"permissions" => ["label","resource","paths","scopes","policies"]
		];

		$this->aPoliciesTypes = ["regex","role","session","time","user"];

		$this->setKeyStore(NGL_PATH_DATA.NGL_DIR_SLASH."alvin");
		if(\file_exists($this->sKeyStore."alvin_pub.pem") || NGL_ALVIN!==null) {
			$this->setkey();
		}

		$this->sAlvinClaim = "alvin";
		$this->sExpireTime = "15 minutes";
	}

	// KEYS --------------------------------------------------------------------
	// path del keystore
	public function setKeyStore($sPath) {
		$sPath = self::call()->sandboxPath($sPath);
		$sPath = self::call()->clearPath($sPath, true);
		if(!\is_writable($sPath)) {
			self::errorMessage($this->object, 1001, $sPath);
		}
		$this->sKeyStore = $sPath;
	}

	// genera un par de claves RSA de 2048 bits
	public function keys($bSave=false, $bReturnKeys=true) {
		$this->aGeneratedKeys = $this->crypt->keygen();
		$this->setkey(true, $this->aGeneratedKeys["private"]);
		$this->setkey(false, $this->aGeneratedKeys["public"]);

		if($bSave) {
			if(!\is_dir($this->sKeyStore)) {
				if(!@\mkdir($this->sKeyStore, 0777, true)) { self::errorMessage($this->object, 1002, $this->sKeyStore); }
			}
			$this->crypt->keypath($this->sKeyStore)->keyname("alvin")->savekeys();
		}
		return ($bReturnKeys) ? $this->aGeneratedKeys : $this;
	}

	// setea las claves publicas y privadas
	public function setkey($bPrivate=false, $sKey=null) {
		if($bPrivate) {
			if($sKey===null) {
				// TODO: agregar caso de Passphrase
				if(\file_exists($this->sKeyStore."alvin.pem") && NGL_ALVIN!==null) {
					$sKey = \file_get_contents($this->sKeyStore."alvin.pem");
				} else if(NGL_ALVIN===null) {
					self::errorShowSource(false);
					self::errorMessage($this->object, 1003);
				}
			}
			$this->sPrivateKey = $this->PrepareKey($sKey);
		} else {
			if($sKey===null) {
				if(\file_exists($this->sKeyStore."alvin_pub.pem") && NGL_ALVIN===true) {
					$sKey = \file_get_contents($this->sKeyStore."alvin_pub.pem");
				} else if(NGL_ALVIN===null) {
					self::errorShowSource(false);
					self::errorMessage($this->object, 1004);
				}
			}
			$this->sCryptKey = $this->PrepareKey($sKey);
		}
		return $this;
	}

	private function PrepareKey($sKey) {
		return \trim($sKey);
	}


	// -- GRANTS ADMIN ---------------------------------------------------------
	// grants structure
	public function grants($bJson=false, $bPretty=false) {
		if($bJson) { return $bPretty ? \json_encode($this->aGrants, JSON_PRETTY_PRINT |  JSON_UNESCAPED_SLASHES) : \json_encode($this->aGrants,  JSON_UNESCAPED_SLASHES); }
		return $this->aGrants;
	}

	// carga el arbol de grants, desde una cadena o desde la ubicación predeterminada
	public function grantsLoad($sGrants=null) {
		if($sGrants===null) {
			$aGrants = self::call()->fileLoad(NGL_PATH_DATA.NGL_DIR_SLASH."alvin".NGL_DIR_SLASH."grants.json", "json");
		} else {
			$aGrants = \json_decode($sGrants, true);
		}

		if(!empty($aGrants)) { $this->aGrants = $aGrants; }
		return $this;
	}

	// guarda el arbol de grants en la ubicación predeterminada
	public function grantsSave() {
		self::call()->fileSave(NGL_PATH_DATA.NGL_DIR_SLASH."alvin".NGL_DIR_SLASH."grants.json", $this->aGrants, "json");
		return $this;
	}

	public function grantsFlush() {
		$this->aGrants = $this->aGrantsEmpty;
		return $this;
	}

	// roles -------------------------------------------------------------------
	public function roles() {
		return !empty($this->aGrants["roles"]) ? $this->aGrants["roles"] : [];
	}

	public function rolesCreate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("roles", $sName, true);
		if(empty($aProperties["label"])) { $aProperties["label"] = $sName; }
		$aProperties["attribs"] = (empty($aProperties["attribs"]) || !\is_array($aProperties["attribs"])) ? [] : $aProperties["attribs"];
		$this->GrantClaimCreate("roles", $sClaim, $aProperties);
		return $this;
	}

	public function rolesUpdate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("roles", $sName);
		$this->GrantClaimUpdate($this->aGrants["roles"][$sClaim], $aProperties);
		return $this;
	}

	public function rolesDelete($sName) {
		$sClaim = $this->ClaimExists("roles", $sName);
		$this->ChkRoleInPolicies($sClaim);
		$this->GrantClaimDelete("roles", $sClaim);
		return $this;
	}

	// resources ---------------------------------------------------------------
	public function resources() {
		return !empty($this->aGrants["resources"]) ? $this->aGrants["resources"] : [];
	}

	public function resourcesCreate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("resources", $sName, true);
		if(empty($aProperties["label"])) { $aProperties["label"] = $sName; }
		$aProperties["path"] = !empty($aProperties["path"]) ? $this->PathToClaim($aProperties["path"]) : null;
		$this->GrantClaimCreate("resources", $sClaim, $aProperties);
		return $this;
	}

	public function resourcesUpdate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("resources", $sName);
		if(\array_key_exists("path", $aProperties)) { $aProperties["path"] = $this->PathToClaim($aProperties["path"]); }
		$this->GrantClaimUpdate($this->aGrants["resources"][$sClaim], $aProperties);
		return $this;
	}

	public function resourcesDelete($sName) {
		$sClaim = $this->ClaimExists("resources", $sName);
		$this->ChkResourceInPermissions($sClaim);
		$this->GrantClaimDelete("resources", $sClaim);
		return $this;
	}


	// scopes ------------------------------------------------------------------
	public function scopes() {
		return !empty($this->aGrants["scopes"]) ? $this->aGrants["scopes"] : [];
	}

	public function scopesCreate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("scopes", $sName, true);
		if(empty($aProperties["label"])) { $aProperties["label"] = $sName; }
		$aProperties["path"] = !empty($aProperties["path"]) ? $this->PathToClaim($aProperties["path"]) : null;
		$this->GrantClaimCreate("scopes", $sClaim, $aProperties);
		return $this;
	}

	public function scopesUpdate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("scopes", $sName);
		if(\array_key_exists("path", $aProperties)) { $aProperties["path"] = $this->PathToClaim($aProperties["path"]); }
		$this->GrantClaimUpdate($this->aGrants["scopes"][$sClaim], $aProperties);
		return $this;
	}

	public function scopesDelete($sName) {
		$sClaim = $this->ClaimExists("scopes", $sName);
		$this->ChkScopeInPermissions($sClaim);
		$this->GrantClaimDelete("scopes", $sClaim);
		return $this;
	}


	// policies ----------------------------------------------------------------
	public function policies() {
		return !empty($this->aGrants["policies"]) ? $this->aGrants["policies"] : [];
	}

	public function policiesCreate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("policies", $sName, true);
		if(empty($aProperties["label"])) { $aProperties["label"] = $sName; }
		if(!\array_key_exists("positive", $aProperties)) { $aProperties["positive"] = true; }
		if(!empty($aProperties["type"])) {
			$aProperties["type"] = \strtolower($aProperties["type"]);
			if(!\in_array($aProperties["type"], $this->aPoliciesTypes)) { self::errorMessage($this->object, 1008, $aProperties["type"]); }
		}
		if(empty($aProperties["type"])) { self::errorMessage($this->object, 1008); }
		if(empty($aProperties["value"])) { self::errorMessage($this->object, 1009); }

		// chequeo de roles
		if($aProperties["type"]=="role") {
			$this->ChkPoliciesRoles($aProperties["value"]);
		}

		$this->GrantClaimCreate("policies", $sClaim, $aProperties);
		return $this;
	}

	public function policiesUpdate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("policies", $sName);
		if(!\array_key_exists("positive", $aProperties)) { $aProperties["positive"] = true; }
		if(!empty($aProperties["type"])) { self::errorMessage($this->object, 1012, "policiesUpdate => type"); }
		if(empty($aProperties["value"])) { self::errorMessage($this->object, 1009); }

		// chequeo de roles
		if($this->aGrants["policies"][$sClaim]["type"]=="role") {
			$this->ChkPoliciesRoles($aProperties["value"]);
		}

		$this->GrantClaimUpdate($this->aGrants["policies"][$sClaim], $aProperties);
		return $this;
	}

	public function policiesDelete($sName) {
		$sClaim = $this->ClaimExists("policies", $sName);
		$this->ChkPolicyInPermissions($sClaim);
		$this->GrantClaimDelete("policies", $sClaim);
		return $this;
	}


	// permissions -------------------------------------------------------------
	public function permissions() {
		return !empty($this->aGrants["permissions"]) ? $this->aGrants["permissions"] : [];
	}

	public function permissionsCreate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("permissions", $sName, true);
		if(empty($aProperties["label"])) { $aProperties["label"] = $sName; }

		// resource
		if(empty($aProperties["resource"])) { self::errorMessage($this->object, 1010); }
		$sResource = $this->ClaimExists("resources", $aProperties["resource"]);

		// scopes
		if(!empty($aProperties["scopes"])) {
			if(!\is_array($aProperties["scopes"])) { self::errorMessage($this->object, 1011); }
			foreach($aProperties["scopes"] as $k => $sScope) {
				$this->ClaimExists("scopes", $sScope);
			}
		}

		// paths
		if(!empty($aProperties["paths"]) && \is_array($aProperties["paths"])) {
			foreach($aProperties["paths"] as $k => $sPath) {
				$aProperties["paths"][$k] = $this->PathToClaim($sPath);
			}
		}

		// policies
		if(!empty($aProperties["policies"]) && \is_array($aProperties["policies"])) {
			foreach($aProperties["policies"] as $k => $sPolicy) {
				$this->ClaimExists("policies", $sPolicy);
			}
		}

		$this->GrantClaimCreate("permissions", $sClaim, $aProperties);
		return $this;
	}

	public function permissionsUpdate($sName, $aProperties=[]) {
		$sClaim = $this->ClaimExists("permissions", $sName);

		// resource
		if(!empty($aProperties["resource"])) {
			$sResource = $this->ClaimExists("resources", $aProperties["resource"]);
		}

		// scopes
		if(!empty($aProperties["scopes"])) {
			if(!\is_array($aProperties["scopes"])) { self::errorMessage($this->object, 1011); }
			foreach($aProperties["scopes"] as $k => $sScope) {
				$this->ClaimExists("scopes", $sScope);
			}
		}

		// paths
		if(!empty($aProperties["paths"]) && \is_array($aProperties["paths"])) {
			foreach($aProperties["paths"] as $k => $sPath) {
				$aProperties["paths"][$k] = $this->PathToClaim($sPath);
			}
		}

		// policies
		if(!empty($aProperties["policies"]) && \is_array($aProperties["policies"])) {
			foreach($aProperties["policies"] as $k => $sPolicy) {
				$this->ClaimExists("policies", $sPolicy);
			}
		}

		$this->GrantClaimUpdate($this->aGrants["permissions"][$sClaim], $aProperties);

		return $this;
	}

	public function permissionsDelete($sName) {
		$sClaim = $this->ClaimExists("permissions", $sName);
		$this->GrantClaimDelete("permissions", $sClaim);
		return $this;
	}

	// -- TOKEN ----------------------------------------------------------------
	// duracion del token
	public function tokenExpireTime($sExpireTime=false) {
		if($sExpireTime===false) { return $this->sExpireTime; }
		$this->sExpireTime = $sExpireTime;
		return $this;
	}

	// genera un token
	public function tokenCreate($sUsername, $aRoles=[], $aToken=[]) {
		$aAlvin = ["roles"=>$aRoles, "resources"=>[], "paths"=>[], "policies"=>[]];
		$aPermissions = $this->evaluate($sUsername, $aRoles, $aToken);
		$this->AddToToken($aAlvin, $aPermissions["approve"]);
		$aToken["username"] = $sUsername;
		$aToken["alvin"] = $aAlvin;

		// se genera el token
		if(!$this->sPrivateKey) { self::errorMessage($this->object, 1003); }
		$jwt = self::call("jwt")->algorithm("rs512")->key($this->sPrivateKey);
		if(!empty($this->sExpireTime)) { $jwt->expire($this->sExpireTime); }
		return ($sToken = $jwt->create($aToken)) ? $jwt->encoded : "";
	}

	// evalua un token
	public function evaluate($sUsername, $aRoles=[], $aToken=[]) {
		$sUsername = $this->username($sUsername);
		// status
		// 0 - fail
		// 1 - approve
		// 2 - eval later
		foreach($this->aGrants["permissions"] as $sPermission => $aPermission) {
			// permisos sin policies
			if($aPermission["policies"]===null) {
				$aEvaluated["approve"][$sPermission] = [];
			} else { // permisos por usuario y rol
				$aRoleUserPass = [];
				foreach($aPermission["policies"] as $sPolicy) {
					$aPolicy = $this->aGrants["policies"][$sPolicy];
					if($aPolicy["type"]=="role") {
						$aRoleUserPass[$sPolicy] = 0;
						if(count(\array_intersect($aRoles, $aPolicy["value"]))) {
							if($aPolicy["positive"]) { $aRoleUserPass[$sPolicy] = 1; }
						} else {
							if(!$aPolicy["positive"]) { $aRoleUserPass[$sPolicy] = 1; }
						}
					} else if($aPolicy["type"]=="user") {
						$aRoleUserPass[$sPolicy] = 0;
						if(in_array($sUsername, $aPolicy["value"])) {
							if($aPolicy["positive"]) { $aRoleUserPass[$sPolicy] = 1; }
						} else {
							if(!$aPolicy["positive"]) { $aRoleUserPass[$sPolicy] = 1; }
						}
					}
				}

				if(!\count($aRoleUserPass) || count($aRoleUserPass)==\array_sum($aRoleUserPass)) {
					$aPolicies = $aPoliciesStatus = [];
					foreach($aPermission["policies"] as $sPolicy) {
						$aPolicies[$sPolicy] = 0;
						$aPoliciesStatus[$sPolicy] = 0;
						$aPolicy = $this->aGrants["policies"][$sPolicy];

						if($aPolicy["type"]=="role" || $aPolicy["type"]=="user") {
							$aPolicies[$sPolicy] = 1;
							$aPoliciesStatus[$sPolicy] = 1;
						} else if($aPolicy["type"]=="time" || $aPolicy["type"]=="session") {
							$aPolicies[$sPolicy] = 1;
							$aPoliciesStatus[$sPolicy] = 2;
						} else if($aPolicy["type"]=="regex") {
							$mClaim = self::call()->strToVars($aPolicy["value"][0], $aToken);
							if($aPolicy["value"][0]!=$mClaim) {
								if($aPolicy["value"][1]==="") { $aPolicy["value"][1] = '^$'; }
								if(preg_match("/".$aPolicy["value"][1]."/", $mClaim)) {
									if($aPolicy["positive"]) {
										$aPolicies[$sPolicy] = 1;
										$aPoliciesStatus[$sPolicy] = 1;
									}
								} else {
									if(!$aPolicy["positive"]) {
										$aPolicies[$sPolicy] = 1;
										$aPoliciesStatus[$sPolicy] = 1;
									}
								}
							}
						}
					}
					if(count($aPolicies)==\array_sum($aPolicies)) {
						$aEvaluated["approve"][$sPermission] = $aPoliciesStatus;
					} else {
						$aEvaluated["fail"][$sPermission] = $aPoliciesStatus;
					}

				} else {
					$aEvaluated["fail"][$sPermission] = $aRoleUserPass;
				}
			}
		}

		return $aEvaluated;
	}

	private function AddToToken(&$aAlvin, $aPermissions) {
		foreach($aPermissions as $sPermission => $aPolicies) {
			$aPermission	= $this->aGrants["permissions"][$sPermission];
			$sResource		= $aPermission["resource"];
			$sResourcePath	= !empty($this->aGrants["resources"][$sResource]["path"]) ? $this->aGrants["resources"][$sResource]["path"] : false;
			$aScopePaths	= [];

			$aPoliciesNames = [];
			foreach($aPolicies as $sPolicy => $nStatus) {
				if($nStatus===2) { $aPoliciesNames[] = $sPolicy; }
			}
			if(!\count($aPoliciesNames)) { $aPoliciesNames = true; }

			$aAlvin["resources"][$sResource] = $aPoliciesNames;
			if(!empty($aPermission["scopes"]) && \is_array($aPermission["scopes"])) {
				foreach($aPermission["scopes"] as $sScope) {
					$aAlvin["resources"][$sResource.".".$sScope] = $aPoliciesNames;
					if(!empty($this->aGrants["scopes"][$sScope]["path"])) {
						$aScopePaths[] = $this->aGrants["scopes"][$sScope]["path"];
					}
				}
			}

			if($sResourcePath!=false) {
				$aAlvin["paths"][$sResourcePath.NGL_DIR_SLASH] = $aPoliciesNames;
				$aAlvin["paths"][$sResourcePath.NGL_DIR_SLASH."index"] = $aPoliciesNames;
				if(count($aScopePaths)) {
					foreach($aScopePaths as $sPath) {
						$aAlvin["paths"][$sResourcePath.NGL_DIR_SLASH.$sPath] = $aPoliciesNames;
					}
				}
			}

			if($aPermission["paths"]) {
				$aAlvin["paths"] = \array_merge($aAlvin["paths"], \array_fill_keys($aPermission["paths"], $aPoliciesNames));
			}

			if($aPoliciesNames!==true) {
				foreach($aPoliciesNames as $sPolicy) {
					$aAlvin["policies"][$sPolicy] = $this->aGrants["policies"][$sPolicy];
				}
			}
		}
	}

	private function RealTimePolicies($aPoliciesList) {
		$aPolicies = $aPoliciesStatus = [];
		foreach($aPoliciesList as $sPolicy) {
			$aPolicies[$sPolicy] = 0;
			$aPolicy = $this->aToken[$this->sAlvinClaim]["policies"][$sPolicy];

			if($aPolicy["type"]=="time") {
				$bDate = true;
				if($aPolicy["value"][2]) {
					if(!\in_array(\date("w"), $aPolicy["value"][2])) { $bDate = false; }
				}

				if($bDate) {
					$nFrom = !empty($aPolicy["value"][0]) ? \strtotime($aPolicy["value"][0]) : 0;
					$nTo = !empty($aPolicy["value"][0]) ? \strtotime($aPolicy["value"][1]) : \strtotime("2050-08-15");
					$nNow = \time();
					if($nNow<=$nFrom || $nNow>=$nTo) {
						$bDate = false;
					}
				}

				if($bDate) {
					if(self::call()->isTrue($aPolicy["positive"])) { $aPolicies[$sPolicy] = 1; }
				} else {
					if(!self::call()->isTrue($aPolicy["positive"])) { $aPolicies[$sPolicy] = 1; }
				}
			} else if($aPolicy["type"]=="session") {
				if(empty($_SESSION[NGL_SESSION_INDEX]["ALVIN"])) {
					$this->__errorMode__("log");
					return self::errorMessage($this->object, 1016);
				}

				$mClaim = self::call()->strToVars($aPolicy["value"][0], $_SESSION[NGL_SESSION_INDEX]["ALVIN"]);
				if($aPolicy["value"][0]!=$mClaim) {
					if($aPolicy["value"][1]==="") { $aPolicy["value"][1] = '^$'; }
					if(preg_match("/".$aPolicy["value"][1]."/", $mClaim)) {
						if(self::call()->isTrue($aPolicy["positive"])) { $aPolicies[$sPolicy] = 1; }
					} else {
						if(!self::call()->isTrue($aPolicy["positive"])) { $aPolicies[$sPolicy] = 1; }
					}
				}
			}
		}

		return (count($aPolicies)==\array_sum($aPolicies)) ? true : false;
	}

	// IMPLEMENTACION ----------------------------------------------------------
	// carga un token
	public function load($sToken) {
		if(!$this->sCryptKey) { self::errorMessage($this->object, 1004); }
		$jwt = self::call("jwt");
		if(!$bCheck = $jwt->verify($sToken, $this->sCryptKey)) {
			$this->__errorMode__("boolean");
			return self::errorMessage($this->object, 1015);
		}
		$this->aToken = \json_decode($jwt->jwt["payload"], true);
		return $this;
	}

	// verifica que haya un token cargado
	public function loaded() {
		return ($this->aToken!==null);
	}

	// intenta cargar el token desde la session
	public function autoload() {
		if(empty($_SESSION[NGL_SESSION_INDEX]["ALVIN"])) {
			$this->__errorMode__("log");
			return self::errorMessage($this->object, 1016);
		}
		if(!empty($_SESSION[NGL_SESSION_INDEX]["ALVIN"]["token"])) {
			$this->load($_SESSION[NGL_SESSION_INDEX]["ALVIN"]["token"]);
		}
		return $this;
	}

	public function token() {
		return $this->aToken;
	}

	public function check($sGrant) {
		if(!$this->loaded()) { $this->expiredToken(); }
		$sGrant = \trim($sGrant);
		$sGrant = \strtolower($sGrant);
		if(empty($sGrant)) { return false; }
		if($sGrant[0].$sGrant[1]=="!|") {
			$sGrant = \substr($sGrant, 2);
			return $this->CheckPermissions($sGrant, "none");
		} else if($sGrant[0].$sGrant[1]=="?|") {
			$sGrant = \substr($sGrant, 2);
			return $this->CheckPermissions($sGrant, "any");
		} else {
			return $this->CheckPermissions($sGrant, "all");
		}
	}

	public function firewall($sPath) {
		if(!$this->loaded()) { $this->expiredToken(); }
		if(!isset($this->aToken[$this->sAlvinClaim]["paths"][$sPath])) { return false; }
		if($this->aToken[$this->sAlvinClaim]["paths"][$sPath]===true) { return true; }
		return $this->RealTimePolicies($this->aToken[$this->sAlvinClaim]["paths"][$sPath]);
	}

	public function expiredToken() {
		$this->__errorMode__("die");
		self::errorShowSource(false);
		self::errorMessage($this->object, 1015);
	}

	private function CheckPermissions($sResource, $sMode="analize") {
		if(!$this->loaded()) {
			$this->autoload();
			if($this->aToken===null) {
				$this->__errorMode__("log");
				self::errorMessage($this->object, 1014);
				return null;
			}
		}

		$aToCheck = (\strpos($sResource, ",")===false) ? [$sResource] : self::call()->explodeTrim(",", $sResource);

		if(\is_array($aToCheck) && \count($aToCheck)==1) {
			$sResource = $aToCheck[0];
			if(isset($this->aToken[$this->sAlvinClaim]["resources"][$sResource])) {
				if($this->aToken[$this->sAlvinClaim]["resources"][$sResource]===true) {
					return ($sMode=="none") ? false : true;
				} else {
					if($this->RealTimePolicies($this->aToken[$this->sAlvinClaim]["resources"][$sResource])) {
						return ($sMode=="none") ? false : true;
					}
				}
			}
			return ($sMode=="none") ? true : false;
		} else {
			$aReturn = [];
			$bNone = true;
			foreach($aToCheck as $sResource) {
				$bPass = false;
				if(isset($this->aToken[$this->sAlvinClaim]["resources"][$sResource])) {
					$bPass = true;
					if(\is_array($this->aToken[$this->sAlvinClaim]["resources"][$sResource])) {
						$bPass = $this->RealTimePolicies($this->aToken[$this->sAlvinClaim]["resources"][$sResource]);
					}
				}

				if($bPass) {
					if($sMode=="any") { return true; }
					if($sMode=="none") { return false; }
					$aReturn[$sResource] = true;
					$bNone = false;
				} else {
					if($sMode=="all") { return false; }
					$aReturn[$sResource] = false;
				}

			}

			if($sMode=="none") { return $bNone; }
			if($sMode=="all") { return true; }
			if($sMode=="any") { return false; }

			return $aReturn;
		}
	}

	// -- VALIDACIONES----------------------------------------------------------
	// sanitiza un nombre de usuario
	// si el nombre es nulo y hay un token cargado, intenta retornar el nombre del mismo
	public function username($sUsername=null) {
		if($sUsername===null && $this->aToken!==null) { return $this->aToken["username"]; }
		$sUsername = self::call()->unaccented($sUsername);
		return \preg_replace("/[^a-zA-Z0-9\_\-\.\@]+/", "", $sUsername);
	}

	// calcula un password
	public function password($sPassword) {
		$sCryptPassword = \crypt($sPassword, '$6$rounds=5000$'.\md5($this->sCryptKey).'$');
		$aCryptPassword = \explode('$', $sCryptPassword, 5);
		return $aCryptPassword[4];
	}

	private function ClaimExists($sGrant, $sClaim, $bPositive=false, $bThrowError=true) {
		$sClaim = $this->ClaimName($sClaim);
		$bExists = \array_key_exists($sClaim, $this->aGrants[$sGrant]);

		if($bExists && $bPositive) {
			if($bThrowError) { self::errorMessage($this->object, 1006, $sGrant."[".$sClaim."]"); }
			return true;
		} else if(!$bExists && !$bPositive) {
			if($bThrowError) { self::errorMessage($this->object, 1007, $sGrant."[".$sClaim."]"); }
			return true;
		}

		return $bThrowError ? $sClaim : false;
	}

	private function ClaimName($sClaimSrc) {
		$sClaim = self::call()->unaccented($sClaimSrc);
		$sClaim = \strtolower(\preg_replace("/[^a-zA-Z0-9\_]+/", "", $sClaim));
		if(empty($sClaim) || preg_match("/[a-z\_]/", $sClaim[0])===0) { self::errorMessage($this->object, 1005, $sClaimSrc." => ".$sClaim); }
		return $sClaim;
	}

	private function PathToClaim($sPath) {
		return ($sPath==NGL_DIR_SLASH || $sPath==".") ? $sPath : self::call()->clearPath($sPath);
	}

	private function GrantClaimCreate($sGrant, $sClaim, $aCreate) {
		$aClaim = \array_fill_keys($this->aGrantsStructures[$sGrant], null);
		foreach($this->aGrantsStructures[$sGrant] as $sKey) {
			if(\array_key_exists($sKey, $aCreate)) {
				$aClaim[$sKey] = $aCreate[$sKey];
			}
		}
		$this->aGrants[$sGrant][$sClaim] = $aClaim;
	}

	private function GrantClaimUpdate(&$aClaim, $aUpdate) {
		foreach($aClaim as $sKey => $mValue) {
			if(\array_key_exists($sKey, $aUpdate)) {
				$aClaim[$sKey] = $aUpdate[$sKey];
			}
		}
	}

	private function GrantClaimDelete($sGrant, $sClaim) {
		unset($this->aGrants[$sGrant][$sClaim]);
	}

	private function ChkPoliciesRoles($aRoles) {
		$aCheck = \array_diff($aRoles, \array_keys($this->aGrants["roles"]));
		if(count($aCheck)) { self::errorMessage($this->object, 1007, "roles[".\implode(", ", $aCheck)."]"); }
	}

	private function ChkRoleInPolicies($sRole) {
		foreach($this->aGrants["policies"] as $sPolicy => $aPolicy) {
			if($aPolicy["type"]=="role") {
				if(\in_array($sRole, $aPolicy["value"])) {
					self::errorMessage($this->object, 1013, "policy[".$sPolicy."] => role[".$sRole."]");
				}
			}
		}
	}

	private function ChkResourceInPermissions($sResource) {
		foreach($this->aGrants["permissions"] as $sPermission => $aPermission) {
			if($aPermission["resource"]==$sResource) {
				self::errorMessage($this->object, 1013, "permission[".$sPermission."] => resource[".$sResource."]");
			}
		}
	}

	private function ChkScopeInPermissions($sScope) {
		foreach($this->aGrants["permissions"] as $sPermission => $aPermission) {
			if(\in_array($sScope, $aPermission["scopes"])) {
				self::errorMessage($this->object, 1013, "permission[".$sPermission."] => scope[".$sScope."]");
			}
		}
	}

	private function ChkPolicyInPermissions($sPolicy) {
		foreach($this->aGrants["permissions"] as $sPermission => $aPermission) {
			if(\in_array($sPolicy, $aPermission["policies"])) {
				self::errorMessage($this->object, 1013, "permission[".$sPermission."] => policy[".$sPolicy."]");
			}
		}
	}
}

?>