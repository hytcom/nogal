<?php

$NGL_ALVIN_LOGIN		= NGL_URL."/login";

$NGL_ALVIN_SAFEPATHS 	= array();
$NGL_ALVIN_SAFEPATHS[]	= "/gardener";
$NGL_ALVIN_SAFEPATHS[]	= "/login";
$NGL_ALVIN_SAFEPATHS[]	= "/logout";
$NGL_ALVIN_SAFEPATHS[]	= "/index";
$NGL_ALVIN_SAFEPATHS[]	= "/";
$NGL_ALVIN_SAFEPATHS[]	= "";

/** $NGL_ALVIN_IGNORES ---------------------------------------------------------
Archivos ignorados por ALVIN al momento de la carga
La URL de los archivos son relativas al valor de la constante NGL_URL y deben comenzar con "/"
Ej:
	NGL_URL = http://www.dominio.com/project
	URL COMPLETA = http://www.dominio.com/project/folder/filename.php
	$NGL_ALVIN_IGNORES[] = "/folder/filename.php";

La carga de estas excepciones debe hacerse en settings.php
**/
if(!isset($NGL_ALVIN_IGNORES) || !count($NGL_ALVIN_IGNORES)) {
	$NGL_ALVIN_IGNORES = $NGL_ALVIN_SAFEPATHS;
}


/** $NGL_ALVIN_STRICTS ---------------------------------------------------------
Archivos para los que se requiere algun tipo de acceso ALVIN al momento de la carga
La URL de los archivos son relativas al valor de la constante NGL_URL y deben comenzar con "/"
Ej:
	NGL_URL = http://www.dominio.com/project
	URL COMPLETA = http://www.dominio.com/project/folder/filename.php
	$NGL_ALVIN_STRICTS["/mods/foobar/file1.php"] = true; <- se requiere unicamente login activo
	$NGL_ALVIN_STRICTS["/mods/foobar/file2.php"] = "FOO,BAR"; <- se requieren los permisos FOO y BAR
	$NGL_ALVIN_STRICTS["/mods/foobar/file3.php"] = "?|FOO,BAR"; <- se requieren los permisos FOO ó BAR
	$NGL_ALVIN_STRICTS["/mods/foobar2/"] = "BAR"; <- bloquea toda la carpeta, se requiere permiso BAR

La carga de estas limitantes debe hacerse en settings.php
**/
if(!isset($NGL_ALVIN_STRICTS)) { $NGL_ALVIN_STRICTS = array(); }


/** REDIRECCION DESPUES DEL LOGIN --------------------------------------------*/
if(isset($_SESSION, $_SESSION[NGL_SESSION_INDEX])) {
	if(!isset($_SESSION[NGL_SESSION_INDEX]["ALVIN"])) {
		if(!isset($_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"])) {
			if(!in_array(NGL_PATH_CURRENT, $NGL_ALVIN_SAFEPATHS)) {
				$_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"] = NGL_URL.NGL_PATH_CURRENT;
				if(defined("NGL_PATH_CURRENT_QUERY") && NGL_PATH_CURRENT_QUERY!="") { $_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"] .= "?".NGL_PATH_CURRENT_QUERY; }
			} else {
				$_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"] = true;
			}
		}
	} else {
		if(isset($_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]) && $_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]!==true) {
			header("location:".$_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]);
			unset($_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]);
			exit();
		}
		unset($_SESSION[NGL_SESSION_INDEX]["GOAFTERLOGIN"]);
	}
}

/** CHEQUEOS -----------------------------------------------------------------*/
if(NGL_AUTHORIZED_IPS!==null && is_array(NGL_AUTHORIZED_IPS) && !in_array(NGL_PATH_CURRENT, $NGL_ALVIN_SAFEPATHS)) {
	if(array_key_exists("REMOTE_ADDR", $_SERVER) && !in_array($_SERVER["REMOTE_ADDR"], NGL_AUTHORIZED_IPS)) {
		$ngl()->errorPages(403);
	}
}

if(NGL_ALVIN!==null) {
	// si no de definen exepciones, todo esta bloquedo salvo index y login
	if(!isset($_SESSION, $_SESSION[NGL_SESSION_INDEX], $_SESSION[NGL_SESSION_INDEX]["ALVIN"]) && !NGL_TERMINAL) {
		if(NGL_PATH_CURRENT!="/" && !in_array(NGL_PATH_CURRENT, $NGL_ALVIN_IGNORES)) {
			if($ngl()->inCurrentPath($NGL_ALVIN_IGNORES)===false) {
				header("location:".$NGL_ALVIN_LOGIN);
				exit();
			}
		}
	}

	$NGL_ALVIN_STRICT = $ngl()->inCurrentPath(array_keys($NGL_ALVIN_STRICTS));
	if($NGL_ALVIN_STRICT!==false) {
		if($NGL_ALVIN_STRICTS[$NGL_ALVIN_STRICT]===true && !isset($_SESSION[NGL_SESSION_INDEX]["ALVIN"])) {
			header("location:".$NGL_ALVIN_LOGIN);
			exit();
		}

		if(array_key_exists("alvin", $_SESSION[NGL_SESSION_INDEX]["ALVIN"])) {
			$sToken		= isset($_SESSION[NGL_SESSION_INDEX]["ALVIN"]["alvin"]) ? $_SESSION[NGL_SESSION_INDEX]["ALVIN"]["alvin"] : null;
			$sUsername	= isset($_SESSION[NGL_SESSION_INDEX]["ALVIN"]["username"]) ? $_SESSION[NGL_SESSION_INDEX]["ALVIN"]["username"] : null;
			$sProfile	= isset($_SESSION[NGL_SESSION_INDEX]["ALVIN"]["profile"]) ? $_SESSION[NGL_SESSION_INDEX]["ALVIN"]["profile"] : null;

			if($NGL_ALVIN = $ngl("alvin")->load($sToken, $sUsername, $sProfile)) {
				if(!$NGL_ALVIN->check($NGL_ALVIN_STRICTS[$NGL_ALVIN_STRICT])) {
					header("location:".$NGL_ALVIN_LOGIN);
					exit();
				}
			} else {
				header("location:".$NGL_ALVIN_LOGIN);
				exit();
			}
		}
	}
}

unset($NGL_ALVIN, $NGL_ALVIN_SAFEPATHS, $NGL_ALVIN_IGNORES, $NGL_ALVIN_STRICT, $NGL_ALVIN_STRICTS, $NGL_ALVIN_LOGIN);

?>