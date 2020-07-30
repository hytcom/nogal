<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___
  
# definición de constantes
Para consultar la información acerca de las contantes, visitar

https://github.com/hytcom/wiki/blob/master/nogal/docs/constants.md

*/
#===============================================================================
# CONFIGURACION
#===============================================================================
// nombre del proyecto
define("NGL_PROJECT",															"NOGAL");

// version del proyecto
define("NGL_PROJECT_RELEASE",													"lasted");

// RUTAS -----------------------------------------------------------------------
// document_root
define("NGL_DOCUMENT_ROOT",														"/var/www");

// directorio nogal
define("NGL_PATH_FRAMEWORK",													"/usr/share/nogal");

// directorio project
define("NGL_PATH_PROJECT",														NGL_DOCUMENT_ROOT."/project");

// directorio public
define("NGL_PATH_PUBLIC",														NGL_PATH_PROJECT."/html");

// configuraciones
define("NGL_PATH_CONF",															NGL_PATH_PROJECT."/conf");

// repositorio de datos
define("NGL_PATH_DATA",															NGL_PATH_PROJECT."/data");

// project grafts (third-party)
define("NGL_PATH_GRAFTS",														NGL_PATH_PROJECT."/grafts");

// nuts
define("NGL_PATH_NUTS",															NGL_PATH_PROJECT."/nuts");

// repositorio de sesiones para los modos fs o sqlite
define("NGL_PATH_SESSIONS",														NGL_PATH_PROJECT."/sessions");

// carpeta de logs
define("NGL_PATH_LOGS",															NGL_PATH_PROJECT."/logs");

// carpeta temporal
define("NGL_PATH_TMP",															NGL_PATH_PROJECT."/tmp");

// contenedor de operaciones con archivos
define("NGL_SANDBOX",															NGL_PATH_PROJECT);

// tutores - chequeos de REFERER y ONCECODE
define("NGL_PATH_TUTORS",														NGL_PATH_PROJECT."/tutors");

// reglas para la validacion de variables
define("NGL_PATH_VALIDATE",														NGL_PATH_PROJECT."/validate");

// directorio de código fuente para el uso de prickout
define("NGL_PATH_PRICKOUT",														NGL_PATH_PROJECT."/prickout");

// URL del proyecto
define("NGL_URL",																((isset($_SERVER["HTTP_HOST"])) ? ((isset($_SERVER["HTTPS"]) && !empty($_SERVER["HTTPS"])) ? "https" : "http")."://".$_SERVER["HTTP_HOST"] : ""));

// SEGURIDAD -------------------------------------------------------------------
// id de session
define("NGL_SESSION_INDEX",														"NOGAL");

// clave AES para encriptar passwords (NULL para desactivar encriptación)
define("NGL_PASSWORD_KEY",														null);

// control de accesos y generacion de passwords (NULL para desactivar)
define("NGL_ALVIN",																null);

// tipo de carga de ALVIN-TOKEN (TOKEN|TOKENUSER|PROFILE)
define("NGL_ALVIN_MODE",														"PROFILE");

// array de IPs autorizadas (NULL para desactivar)
define("NGL_AUTHORIZED_IPS",													null);

// valida que el referer sea del mismo dominio 
define("NGL_REFERER",															false);

// valida la vigencia de un código ONCE
define("NGL_ONCECODE",															false);

// tiempo de vigencia de los códigos ONCE
define("NGL_ONCECODE_TIMELIFE",													900);


// CABECERAS DEL ARCHIVO -------------------------------------------------------
// UTF-8 | ISO-8859-1 | ...
define("NGL_CHARSET",															"UTF-8");

// text/html | text/plain | ...
define("NGL_CONTENT_TYPE",														"text/html");


// ERRORES ---------------------------------------------------------------------
// manipulacion de errores (true | false)
define("NGL_HANDLING_ERRORS",													true);

// formato de impresion de errores cuando NGL_HANDLING_ERRORS es true (html | text)
define("NGL_HANDLING_ERRORS_FORMAT",											"text");

// tipo de salida de errores cuando NGL_HANDLING_ERRORS es true (boolean | code | die | print | return)
define("NGL_HANDLING_ERRORS_MODE",												"print");

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

// idiomas aceptados
define("NGL_ACCEPTED_LANGUAGES",												"es");


// OTRAS -----------------------------------------------------------------------
// inicia el framework ignorando los chequeos de compatibilidad
define("NGL_RUN_ANYWAY", 														false);

// fuera de linea
define("NGL_FALLEN",															false);

// activa/desactiva la pantalla de configuración
define("NGL_GARDENER", 															false);

// activa/desactiva a bee (NULL para desactivar)
define("NGL_BEE", 																null);

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