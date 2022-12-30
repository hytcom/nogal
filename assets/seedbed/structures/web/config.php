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
#===============================================================================
# CONFIGURACION
#===============================================================================
// nombre del proyecto
define("NGL_PROJECT",															"NOGAL");

// version del proyecto
define("NGL_PROJECT_RELEASE",													"latest");


// CONTROL ---------------------------------------------------------------------
// activa/desactiva a bee (NULL para desactivar)
define("NGL_BEE", 																true);

// activa/desactiva la pantalla de configuración
define("NGL_GARDENER_JOURNAL", 													false);

// fuera de linea
define("NGL_FALLEN",															false);

// inicia el framework ignorando los chequeos de compatibilidad
define("NGL_RUN_ANYWAY", 														false);


// RUTAS -----------------------------------------------------------------------
// document_root
define("NGL_DOCUMENT_ROOT",														"<{=ROOTDIR=}>");

// directorio nogal
define("NGL_PATH_FRAMEWORK",													"/usr/share/nogal");

// directorio con estructuras de archivos
define("NGL_PATH_SEEDBED",														NGL_PATH_FRAMEWORK."/assets/seedbed");

// directorio project
define("NGL_PATH_GARDEN",														NGL_DOCUMENT_ROOT);

// directorio de código fuente
define("NGL_PATH_CROWN",														NGL_PATH_GARDEN."/html");

// tutores
define("NGL_PATH_TUTORS",														NGL_PATH_GARDEN."/tutors");

// nuts
define("NGL_PATH_NUTS",															NGL_PATH_GARDEN."/nuts");

// project grafts (third-party)
define("NGL_PATH_GRAFTS",														NGL_PATH_GARDEN."/grafts");

// directorio rind cache
define("NGL_PATH_CACHE",														NGL_PATH_GARDEN."/ground/cache");

// configuraciones
define("NGL_PATH_CONF",															NGL_PATH_GARDEN."/ground/conf");

// repositorio de datos
define("NGL_PATH_DATA",															NGL_PATH_GARDEN."/ground/data");

// repositorio de sesiones para los modos fs o sqlite
define("NGL_PATH_SESSIONS",														NGL_PATH_GARDEN."/ground/sessions");

// carpeta de logs
define("NGL_PATH_LOGS",															NGL_PATH_GARDEN."/ground/logs");

// carpeta temporal
define("NGL_PATH_TMP",															NGL_PATH_GARDEN."/ground/tmp");

// contenedor de operaciones con archivos
define("NGL_SANDBOX",														    NGL_PATH_GARDEN);

// directorio public
define("NGL_PATH_PUBLIC",														NGL_PATH_GARDEN."/html");

// URL del proyecto
define("NGL_URL",																((isset($_SERVER["HTTP_HOST"])) ? ((isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"])) ? "https" : "http")."://".$_SERVER["HTTP_HOST"] : ""));


// SEGURIDAD -------------------------------------------------------------------
// id de session
define("NGL_SESSION_INDEX",														"NOGAL");

// clave AES para encriptar passwords (NULL para desactivar encriptación)
define("NGL_PASSWORD_KEY",														null);

// control de accesos (NULL para desactivar, true para utilizar keystore)
define("NGL_ALVIN",																null);

// url del login
define("NGL_ALVIN_LOGIN",														NGL_URL."/login");

// array de IPs autorizadas (NULL para desactivar)
define("NGL_AUTHORIZED_IPS",													null);

// valida que el referer sea del mismo dominio
define("NGL_REFERER",															false);

// valida la vigencia de un código ONCE
define("NGL_ONCECODE",															false);

// detiene la ejecución de tutores
define("NGL_READONLY",															false);

// tiempo de vigencia de los códigos ONCE
define("NGL_ONCECODE_TIMELIFE",													900);


// CABECERAS DEL ARCHIVO -------------------------------------------------------
// UTF-8 | ISO-8859-1 | ...
define("NGL_CHARSET",															"UTF-8");

// text/html | text/plain | ...
// define("NGL_CONTENT_TYPE",													"text/html");


// ERRORES ---------------------------------------------------------------------
// manipulacion de errores (true | false)
define("NGL_HANDLING_ERRORS",													true);

// formato de impresion de errores cuando NGL_HANDLING_ERRORS es true (html | text)
define("NGL_HANDLING_ERRORS_FORMAT",											"text");

// tipo de salida de errores cuando NGL_HANDLING_ERRORS es true (boolean | code | die | log | return)
define("NGL_HANDLING_ERRORS_MODE",												"die");

// muestra el rastreo del error cuando NGL_HANDLING_ERRORS es true (true | false)
define("NGL_HANDLING_ERRORS_BACKTRACE",											false);


// SEPARADORES -----------------------------------------------------------------
// separador de filas
define("NGL_STRING_LINEBREAK", 													"\n");

// separador de columnas
define("NGL_STRING_SPLITTER", 													"\t");

// separador de números
define("NGL_STRING_NUMBERS_SPLITTER", 											";");

// separador decimal
define("NGL_NUMBER_SEPARATOR_DECIMAL", 											".");

// separador de miles
define("NGL_NUMBER_SEPARATOR_THOUSANDS",										",");


// FECHA, HORA E IDIOMA --------------------------------------------------------
// zona horaria: http://php.net/manual/es/timezones.php
define("NGL_TIMEZONE",															"America/Argentina/Buenos_Aires");

// nombre de los meses del año
define("NGL_DATE_MONTHS",														"Enero,Febrero,Marzo,Abril,Mayo,Junio,Julio,Agosto,Septiembre,Octubre,Noviembre,Diciembre");

// nombre de los días de la semana
define("NGL_DATE_DAYS",															"Domingo,Lunes,Martes,Miércoles,Jueves,Viernes,Sábado");


// OTRAS -----------------------------------------------------------------------
// separador de directorios
define("NGL_DIR_SLASH",															"/");

// desarrollo: E_ALL | produccion: E_ERROR | E_WARNING | E_PARSE | E_NOTICE
define("NGL_ERROR_REPORTING",													E_ALL);

// indica valor nulo pudiendo ser o no NULL
define("NGL_NULL", 																"__NGL_NULL_VALUE__");

// tipografia por defecto
define("NGL_FONT", 																NGL_PATH_FRAMEWORK."/assets/roboto.ttf");

// permisos aplicados a las nuevas carpetas
define("NGL_CHMOD_FOLDER",														0775);

// permisos aplicados a los nuevos archivos
define("NGL_CHMOD_FILE", 														0664);


#===============================================================================
# INICIO
#===============================================================================
// carga de configuraciones y creacion del objeto $ngl
if(file_exists(NGL_PATH_FRAMEWORK."/nogal.php")) {
	require_once(NGL_PATH_FRAMEWORK."/nogal.php");
} else {
	if(!defined("NGL_IM_GARDENER")) { die("Call the Gardener"); }
}

?>