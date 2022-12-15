<?php

namespace nogal;

#===============================================================================
# INICIO
#===============================================================================
// deteccion de windows
$NGL_WINDOWS = (\strtoupper(\substr(PHP_OS, 0, 3))==="WIN");
\define("NGL_WINDOWS", $NGL_WINDOWS);
unset($NGL_WINDOWS);

// codificacion de caracteres
if(!\defined("NGL_CHARSET")) {
	\define("NGL_CHARSET", "UTF-8");
}
@\header("Charset: ".NGL_CHARSET);

// tipo de archivo
if(\defined("NGL_CONTENT_TYPE") && !empty(NGL_CONTENT_TYPE)) {
	@header("Content-Type: ".NGL_CONTENT_TYPE);
}

// ruta del framework
if(!\defined("NGL_PATH_FRAMEWORK")) {
	$NGL_PATH_FRAMEWORK = \pathinfo(__FILE__);
	\define("NGL_PATH_FRAMEWORK", $NGL_PATH_FRAMEWORK["dirname"]);
	unset($NGL_PATH_FRAMEWORK);
}

// libreria nogal
require_once(NGL_PATH_FRAMEWORK."/libraries/nglRoot.php");

// entorno
require_once(NGL_PATH_FRAMEWORK."/environment.php");
require_once(NGL_PATH_FRAMEWORK."/interfaces.php");

// librerias
$NGL_LIBS = $NGL_GRAFTS = [];
require_once(NGL_PATH_FRAMEWORK."/libraries.php");
if(\file_exists(NGL_PATH_FRAMEWORK."/graftslibs.php")) {
	require_once(NGL_PATH_FRAMEWORK."/graftslibs.php");
}

// creacion del objecto $ngl
$ngl = new nglRoot($NGL_LIBS, $NGL_GRAFTS);
unset($NGL_LIBS, $NGL_GRAFTS);

// fuera de linea
if(NGL_FALLEN) { $ngl()->errorHTTP(503); }

// session
if(!NGL_TERMINAL) {
	if(\file_exists(NGL_PATH_GARDEN."/session.php")) {
		require_once(NGL_PATH_GARDEN."/session.php");
	} else {
		\session_start();
	}
	if(!isset($_SESSION[NGL_SESSION_INDEX])) {
		$_SESSION[NGL_SESSION_INDEX] = ["SESS" => [],"ONCECODES" => []];
	}

	if(!\defined("SID")) { \define("SID", \session_id()); }
	$ngl("sysvar")->sessionVars();
	$ngl("validate");
}

// variables de entorno del proyecto
if(\file_exists(NGL_PATH_GARDEN."/.env")) {
	$ENV = $ngl()->parseConfigFile(NGL_PATH_GARDEN."/.env", true);
}

// alvin
if(NGL_ALVIN!==null || NGL_AUTHORIZED_IPS!==null) {
	if(\file_exists(NGL_PATH_FRAMEWORK."/alvin.php")) {
		require_once(NGL_PATH_FRAMEWORK."/alvin.php");
	}
}

// nogal sembrado
\define("NGL_SOWED", true);

// rutas y redirecciones
if(!NGL_TERMINAL && NGL_PATH_CROWN!==null) {
	require_once(NGL_PATH_FRAMEWORK."/gardener.php");
} else {
	\define("NGL_GARDENS_PLACE", null);
}

?>