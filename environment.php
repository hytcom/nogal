<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___

# definición de constantes
Para consultar la información acerca de las contantes, visitar

https://hytcom.net/nogal/docs/constants.md

*/
namespace nogal;

$TMP_DOCUMENT_ROOT = (!empty($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["DOCUMENT_ROOT"] : \dirname($_SERVER["PHP_SELF"]));

#===============================================================================
# CONFIGURACIONES POR DEFECTO
#===============================================================================
// nombre del proyecto
nglRoot::defineConstant("NGL_PROJECT",											"NOGAL");

// version del proyecto
nglRoot::defineConstant("NGL_PROJECT_RELEASE",									"lasted");


// CONTROL ---------------------------------------------------------------------
// activa/desactiva a bee (NULL para desactivar)
nglRoot::defineConstant("NGL_BEE", 												null);

// activa/desactiva la pantalla de configuración
nglRoot::defineConstant("NGL_GARDENER_JOURNAL", 								false);

// fuera de linea
nglRoot::defineConstant("NGL_FALLEN",											NULL);

// inicia el framework ignorando los chequeos de compatibilidad
nglRoot::defineConstant("NGL_RUN_ANYWAY", 										false);


// RUTAS -----------------------------------------------------------------------
// document_root
nglRoot::defineConstant("NGL_DOCUMENT_ROOT",									$TMP_DOCUMENT_ROOT);

// directorio con estructuras de archivos
nglRoot::defineConstant("NGL_PATH_SEEDBED",										NGL_PATH_FRAMEWORK."/assets/seedbed");

// directorio project
nglRoot::defineConstant("NGL_PATH_GARDEN",										NGL_DOCUMENT_ROOT);

// directorio de código fuente
nglRoot::defineConstant("NGL_PATH_CROWN",										NGL_PATH_GARDEN);

// tutores
nglRoot::defineConstant("NGL_PATH_TUTORS",										NGL_PATH_GARDEN."/tutors");

// nuts
nglRoot::defineConstant("NGL_PATH_NUTS",										NGL_PATH_GARDEN."/nuts");

// project grafts (third-party)
nglRoot::defineConstant("NGL_PATH_GRAFTS",										NGL_PATH_GARDEN."/grafts");

// directorio rind cache
nglRoot::defineConstant("NGL_PATH_CACHE",										NGL_PATH_GARDEN."/cache");

// configuraciones
nglRoot::defineConstant("NGL_PATH_CONF",										NGL_PATH_GARDEN."/conf");

// repositorio de datos
nglRoot::defineConstant("NGL_PATH_DATA",										NGL_PATH_GARDEN."/data");

// repositorio de sesiones para los modos fs o sqlite
nglRoot::defineConstant("NGL_PATH_SESSIONS",									NGL_PATH_GARDEN."/sessions");

// carpeta temporal
nglRoot::defineConstant("NGL_PATH_TMP",											NGL_PATH_GARDEN."/tmp");

// carpeta logs
nglRoot::defineConstant("NGL_PATH_LOGS",										NGL_PATH_GARDEN."/logs");

// contenedor de operaciones con archivos
nglRoot::defineConstant("NGL_SANDBOX",											"/");

// directorio public
nglRoot::defineConstant("NGL_PATH_PUBLIC",										NGL_DOCUMENT_ROOT);

// URL del proyecto
nglRoot::defineConstant("NGL_URL",												((isset($_SERVER["HTTP_HOST"])) ? ((isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"])) ? "https" : "http")."://".$_SERVER["HTTP_HOST"] : ""));


// SEGURIDAD -------------------------------------------------------------------
// id de session
nglRoot::defineConstant("NGL_SESSION_INDEX",									"NOGAL");

// clave AES para encriptar passwords (NULL para desactivar encriptación)
nglRoot::defineConstant("NGL_PASSWORD_KEY",										null);

// control de accesos (NULL para desactivar, true para utilizar keystore)
nglRoot::defineConstant("NGL_ALVIN",											null);

// array de IPs autorizadas (NULL para desactivar)
nglRoot::defineConstant("NGL_AUTHORIZED_IPS",									null);

// valida que el referer sea del mismo dominio
nglRoot::defineConstant("NGL_REFERER",											true);

// valida la vigencia de un código ONCE
nglRoot::defineConstant("NGL_ONCECODE",											true);

// detiene la ejecución de tutores
nglRoot::defineConstant("NGL_READONLY",											false);

// tiempo de vigencia de los códigos ONCE
nglRoot::defineConstant("NGL_ONCECODE_TIMELIFE",								900);


// ERRORES ---------------------------------------------------------------------
// desarrollo: E_ALL | produccion: E_ERROR | E_WARNING | E_PARSE | E_NOTICE
nglRoot::defineConstant("NGL_ERROR_REPORTING",									E_ALL);

// manipulacion de errores (true | false)
nglRoot::defineConstant("NGL_HANDLING_ERRORS",									true);

// formato de impresion de errores cuando NGL_HANDLING_ERRORS es true (html | text)
nglRoot::defineConstant("NGL_HANDLING_ERRORS_FORMAT",							"html");

// tipo de salida de errores cuando NGL_HANDLING_ERRORS es true (boolean | code | die | log | return)
nglRoot::defineConstant("NGL_HANDLING_ERRORS_MODE",								"die");

// muestra el rastreo del error cuando NGL_HANDLING_ERRORS es true (true | false)
nglRoot::defineConstant("NGL_HANDLING_ERRORS_BACKTRACE", 						false);


// FORMATOS --------------------------------------------------------------------
// separador de filas
nglRoot::defineConstant("NGL_STRING_LINEBREAK", 								"\n");

// separador de columnas
nglRoot::defineConstant("NGL_STRING_SPLITTER", 									"\t");

// separador de números
nglRoot::defineConstant("NGL_STRING_NUMBERS_SPLITTER", 							";");

// separador decimal
nglRoot::defineConstant("NGL_NUMBER_SEPARATOR_DECIMAL", 						".");

// separador de miles
nglRoot::defineConstant("NGL_NUMBER_SEPARATOR_THOUSANDS",						",");


// FECHA, HORA E IDIOMA --------------------------------------------------------
// zona horaria: http://php.net/manual/es/timezones.php
nglRoot::defineConstant("NGL_TIMEZONE",											"America/Argentina/Buenos_Aires");

// nombre de los meses del año
nglRoot::defineConstant("NGL_DATE_MONTHS",										"Enero,Febrero,Marzo,Abril,Mayo,Junio,Julio,Agosto,Septiembre,Octubre,Noviembre,Diciembre");

// nombre de los días de la semana
nglRoot::defineConstant("NGL_DATE_DAYS",										"Domingo,Lunes,Martes,Miércoles,Jueves,Viernes,Sábado");


// OTRAS -----------------------------------------------------------------------
// separador de directorios
nglRoot::defineConstant("NGL_DIR_SLASH",										DIRECTORY_SEPARATOR);

// indica valor nulo pudiendo ser o no NULL
nglRoot::defineConstant("NGL_NULL", 											"__NGL_NULL_VALUE__");

// tipografia por defecto
nglRoot::defineConstant("NGL_FONT", 											NGL_PATH_FRAMEWORK."/assets/roboto.ttf");

// permisos aplicados a las nuevas carpetas
nglRoot::defineConstant("NGL_CHMOD_FOLDER",										0775);

// permisos aplicados a los nuevos archivos
nglRoot::defineConstant("NGL_CHMOD_FILE",										0664);

unset($TMP_DOCUMENT_ROOT);


// SISTEMA ---------------------------------------------------------------------
$NGL_URL = \constant("NGL_URL");
if(!empty($NGL_URL)) {
	$NGL_URLPARTS = \parse_url(NGL_URL);
	if(isset($NGL_URLPARTS["port"])) {
		$NGL_URLPARTS["urlport"] =  ":".$NGL_URLPARTS["port"];
	} else {
		$NGL_URLPARTS["port"] = $NGL_URLPARTS["urlport"] = "";
	}

	nglRoot::defineConstant("NGL_URL_PROTOCOL", 			$NGL_URLPARTS["scheme"]);
	nglRoot::defineConstant("NGL_URL_HOST", 				$NGL_URLPARTS["host"]);
	nglRoot::defineConstant("NGL_URL_PORT", 				$NGL_URLPARTS["port"]);
	nglRoot::defineConstant("NGL_URL_ROOT", 				$NGL_URLPARTS["scheme"]."://".$NGL_URLPARTS["host"].$NGL_URLPARTS["urlport"]);
	unset($NGL_URLPARTS);
}
unset($NGL_URL);

// path del archivo actual desde NGL_URL y REQUEST_URI
if(PHP_SAPI!="cli") {
	$NGL_PATH_CURRENT = \explode("?", NGL_URL_ROOT.$_SERVER["REQUEST_URI"], 2);
	$NGL_PATH_CURRENT_QUERY = isset($NGL_PATH_CURRENT[1]) ? $NGL_PATH_CURRENT[1] : "";
	$NGL_PATH_CURRENT = \str_replace(NGL_URL, "", $NGL_PATH_CURRENT[0]);
	\define("NGL_TERMINAL", false);
	\define("NGL_PATH_CURRENT", \preg_replace("#/+$#", "/", $NGL_PATH_CURRENT));
	\define("NGL_PATH_CURRENT_QUERY", $NGL_PATH_CURRENT_QUERY);
	unset($NGL_PATH_CURRENT, $NGL_PATH_CURRENT_QUERY);
} else {
	\define("NGL_TERMINAL", true);
	\define("NGL_PATH_CURRENT", \getcwd()."/".$_SERVER["PHP_SELF"]);
}

#===============================================================================
# VARIABLES RESERVADAS
#===============================================================================
$_SET	= [];		// variables seteadas en las plantillas RIND
$ENV 	= []; 		// variables de entorno tambien disponibles en las plantillas RIND
$env	= &$ENV; 	// EVN alias

#===============================================================================
# CONFIGURACION PHP
#===============================================================================
// errores || errors
if(@\ini_set("display_errors", true)===false) {
	echo "<b>NOGAL ERROR</b> Can't modify PHP display_errors.<br />\n";
} else {
	@\ini_set("track_errors", 0);
	@\ini_set("html_errors", 0);
	\error_reporting(NGL_ERROR_REPORTING);
}

// manejadores de errores y excepciones || errors and exceptions handlers
if(NGL_HANDLING_ERRORS) {
	\set_error_handler(__NAMESPACE__."\\nglRoot::errorsHandler", E_ALL | E_STRICT);
	\set_exception_handler(__NAMESPACE__."\\nglRoot::exceptionsHandler");
}

// timezone
if(\function_exists("date_default_timezone_set")) {
	\date_default_timezone_set(NGL_TIMEZONE);
}


?>