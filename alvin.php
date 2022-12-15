<?php

/*-- DEFAULT CONFIG ----------------------------------------------------------*/
$NGL_ALVIN_CONFIG = [
	"login" => "/login",
	"firewall" => true
];

$NGL_ALVIN_IGNORES = [
	"/gardener" => 1,
	"/login" => 1,
	"/logout" => 1,
	"/index" => 1,
	"/" => 1
];

/*-- CONFIG ------------------------------------------------------------------*/
$ALVINCFG = NGL_PATH_CONF.NGL_DIR_SLASH."alvin.conf";
if(\file_exists($ALVINCFG)) {
	$ALVINCFG = $ngl()->parseConfigString(\file_get_contents($ALVINCFG), true);

	if(isset($ALVINCFG["config"])) {
		$NGL_ALVIN_CONFIG = \array_merge($NGL_ALVIN_CONFIG, $ALVINCFG["config"]);
	}

	if(isset($ALVINCFG["ignore"]) && \count($ALVINCFG["ignore"])) {
		$NGL_ALVIN_IGNORES = \array_merge($NGL_ALVIN_IGNORES, \array_fill_keys(\array_keys($ALVINCFG["ignore"]), true));
	}
}

/*-- REDIRECCION DESPUES DEL LOGIN -------------------------------------------*/
if(isset($_SESSION, $_SESSION[NGL_SESSION_INDEX])) {
	if(!isset($_SESSION[NGL_SESSION_INDEX]["ALVIN"])) {
		if(!isset($_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"])) {
			if(empty($NGL_ALVIN_IGNORES[NGL_PATH_CURRENT])) {
				$_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"] = NGL_URL.NGL_PATH_CURRENT;
				if(\defined("NGL_PATH_CURRENT_QUERY") && NGL_PATH_CURRENT_QUERY!="") { $_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"] .= "?".NGL_PATH_CURRENT_QUERY; }
			} else {
				$_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"] = true;
			}
		}
	} else {
		if(isset($_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]) && $_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]!==true) {
			\header("location:".$_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]);
			unset($_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]);
			exit();
		}
		unset($_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]);
	}
}

/*-- CHEQUEOS ----------------------------------------------------------------*/
if(NGL_AUTHORIZED_IPS!==null && \is_array(NGL_AUTHORIZED_IPS) && empty($NGL_ALVIN_IGNORES[NGL_PATH_CURRENT])) {
	if(\array_key_exists("REMOTE_ADDR", $_SERVER) && !\in_array($_SERVER["REMOTE_ADDR"], NGL_AUTHORIZED_IPS)) {
		$ngl()->errorHTTP(403);
	}
}

if(NGL_ALVIN!==null && !NGL_TERMINAL) {
	if($NGL_ALVIN_CONFIG["firewall"]) {
		$NGL_ALVIN_IGNORE = $ngl()->inCurrentPath(\array_keys($NGL_ALVIN_IGNORES));

		if($NGL_ALVIN_IGNORE===false) {
			// si no de definen exepciones, todo esta bloquedo salvo index y login
			if(isset($_SESSION[NGL_SESSION_INDEX]["ALVIN"]["token"])!==true) {
				if(NGL_PATH_CURRENT!="/" && !\in_array(NGL_PATH_CURRENT, $NGL_ALVIN_IGNORES)) {
					\header("location:".$NGL_ALVIN_CONFIG["login"]);
					exit();
				}
			}

			$NGL_ALVIN = $ngl("alvin")->setkey(false, (NGL_ALVIN===true ? null : NGL_ALVIN))->autoload();
			if(!$NGL_ALVIN || !$NGL_ALVIN->firewall(NGL_PATH_CURRENT)) {
				\header("location:".$NGL_ALVIN_CONFIG["login"]);
				exit();
			}
		}
	}
}

unset($ALVINCFG, $NGL_ALVIN, $NGL_ALVIN_IGNORE, $NGL_ALVIN_IGNORES);

?>