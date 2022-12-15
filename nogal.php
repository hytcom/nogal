<?php

namespace nogal;

#===============================================================================
# CHEQUEOS DE COMPATIBILIDAD
#===============================================================================
if(!\defined("NGL_RUN_ANYWAY") || (\defined("NGL_RUN_ANYWAY") && NGL_RUN_ANYWAY===false)) {
	// version
	if(\version_compare(PHP_VERSION, "7.3") < 0) {
		if(PHP_SAPI!="cli") {
			die("
				<b>NOGAL FATAL ERROR:</b> System require PHP <b>7.3</b> or higher.<br />
				Current version is: ".\phpversion()."<br />
				<br />
				<small>
					You can use de <b>NGL_RUN_ANYWAY</b> constant for ignore this check<br />
					but some methods could not work as expected<br /><br />
				</small>
			");
		} else {
			echo "\n";
			echo "\033[91;91mNOGAL FATAL ERROR:\033[0m System require PHP \033[1m7.3\033[0m or higher\n";
			echo "Current version is: \033[1m".phpversion()."\033[0m\n";
			echo "You can use de \033[1mNGL_RUN_ANYWAY\033[0m constant for ignore this check,\n";
			echo "but some methods could not work as expected\n\n";
			exit();
		}
	}

	// include path
	$NGL_INCPATH = \get_include_path();
	if(\set_include_path($NGL_INCPATH)===false) {
		die("<b>NOGAL FATAL ERROR:</> System requires <b>set_include_path</b> enabled.");
	}
	unset($NGL_INCPATH);
}

#===============================================================================
# FUNCIONES GLOBALES
#===============================================================================
function call($sObjectName=null, $aArguments=[]) {
	return \nogal\nglRoot::call($sObjectName, $aArguments);
}

function env($sVarName) {
	global $ENV;
	if(!preg_match("/[a-z_][a-z0-9_]+/is", $sVarName)) { return null; }
	return \array_key_exists($sVarName, $ENV) ? $ENV[$sVarName] : null;
}

function dump() {
	if(NGL_TERMINAL) {
		echo \nogal\nglRoot::call()->dump(...\func_get_args());
	} else {
		echo \nogal\nglRoot::call()->dumphtml(...\func_get_args());
	}
}

function dumpc() {
	echo \nogal\nglRoot::call()->dumpconsole(...\func_get_args());
}

// retorna true cuando el objeto es un objeto nogal
// si se especifica type, tambien chequea el tipo
function is($obj, $sType=null) {
	return \nogal\nglRoot::is($obj, $sType);
}

#===============================================================================
# CARGA DEL FRAMEWORK
#===============================================================================
require_once("sower.php");

?>