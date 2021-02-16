<?php

#===============================================================================
#	VARIABLES DE ENTORNO
#===============================================================================
// sistema
$ENV["title"]											= NGL_PROJECT;
$ENV["release"]											= NGL_PROJECT_RELEASE;
$ENV["nogal_version"]									= $ngl("sysvar")->VERSION;
$ENV["charset"]											= NGL_CHARSET;
$ENV["content_type"]									= NGL_CONTENT_TYPE;
$ENV["bee"]												= NGL_BEE;

// paths (sin Slash al final)
$ENV["system"]											= NGL_PATH_PROJECT;
$ENV["root"]											= NGL_PATH_PUBLIC;
$ENV["files"]											= NGL_PATH_PUBLIC.NGL_DIR_SLASH."files";

// urls (sin Slash al final)
$ENV["url"]												= NGL_URL;
$ENV["theme"]											= NGL_URL;
$ENV["css"]												= NGL_URL."/css";
$ENV["js"]												= NGL_URL."/js";
$ENV["storage"]											= NGL_URL."/files";
$ENV["cdn"]												= "https://cdn.upps/rel-".NGL_PROJECT_RELEASE;
$ENV["site"]											= NGL_URL;

// archivos
$ENV["self"]											= $ngl("sysvar")->SELF;
$ENV["tutor"]											= NGL_URL."/tutor";

// fechas
$ENV["dates"]											= $ngl("dates")->settings();
$ENV["now"]												= $ngl("dates")->info();

// idioma
$ENV["lang"]											= (isset($_SESSION, $_SESSION[NGL_SESSION_INDEX]["LANGUAGE"])) ? $_SESSION[NGL_SESSION_INDEX]["LANGUAGE"] : "es";

// búsquedas
$ENV["q"]												= (isset($_REQUEST["values"]["q"])) ? $_REQUEST["values"]["q"] : "";
$ENV["search_limit"]									= 250; //limite de registros mostrados

// net
$ENV["ip"]												= $ngl("sysvar")->IP;
$ENV["uri"]												= (isset($_SERVER["REQUEST_URI"])) 	? basename($_SERVER["REQUEST_URI"]) : "";
$ENV["server_ip"]										= (isset($_SERVER["SERVER_ADDR"])) 	? $_SERVER["SERVER_ADDR"] : "";
$ENV["hostname"]										= (isset($_SERVER["HTTP_HOST"])) 	? $_SERVER["HTTP_HOST"] : "";
$ENV["referer"]											= (isset($_SERVER["HTTP_REFERER"])) ? $_SERVER["HTTP_REFERER"] : "";
$ENV["redirect_url"]									= (isset($_SERVER["REDIRECT_URL"])) ? $_SERVER["REDIRECT_URL"] : "";
$ENV["redirect_query_string"]							= (isset($_SERVER["REDIRECT_QUERY_STRING"])) ? $_SERVER["REDIRECT_QUERY_STRING"] : "";

// login
$ENV["alvin"]											= false;
$ENV["alvin_home"]										= NGL_URL."/home";
if(isset($_SESSION, $_SESSION[NGL_SESSION_INDEX], $_SESSION[NGL_SESSION_INDEX]["ALVIN"])) {
	$ENV["alvin"]										= $_SESSION[NGL_SESSION_INDEX]["ALVIN"];
}


#===============================================================================
#	REFERER
#===============================================================================
/** $NGL_REFERER_CHECKS
Archivos que solo puede ser accedidos si el REFERER es el propio dominio, ó sus excepciones
La URL de los archivos son relativas al valor de la constante NGL_URL y deben comenzar con "/"
Cuando la URL termine en / se aplicará la regla para toda la carpeta y sub-carpetas
Ej:
	NGL_URL = http://www.dominio.com/project
	URL COMPLETA = http://www.dominio.com/project/folder/filename.php
	$NGL_REFERER_CHECKS[] = "/folder/";
	$NGL_REFERER_IGNORES[] = "/folder/filename.php";
**/
// chequeos
$NGL_REFERER_CHECKS										= array();
$NGL_REFERER_CHECKS[]									= "/tutor/";

// excepciones
$NGL_REFERER_IGNORES									= array();


#===============================================================================
#	ONCE CODE
#===============================================================================
/** $NGL_ONCECODE_CHECKS
Archivos sobre los que se efectuará la verificación ó excepción por once code
La URL de los archivos son relativas al valor de la constante NGL_URL y deben comenzar con "/"
Cuando la URL termine en / se aplicará la regla para toda la carpeta y sub-carpetas
Ej:
	NGL_URL = http://www.dominio.com/project
	URL COMPLETA = http://www.dominio.com/project/folder/filename.php
	$NGL_ONCECODE_CHECKS[] = "/folder/";
	$NGL_ONCECODE_IGNORES[] = "/folder/filename.php";
**/
// chequeos
$NGL_ONCECODE_CHECKS									= array();
$NGL_ONCECODE_CHECKS[]									= "/tutor/";

// excepciones
$NGL_ONCECODE_IGNORES									= array();


#===============================================================================
#	VARIABLES DEL PROYECTO
#===============================================================================
$ENV["gtools"] = array();
$ENV["gtools"]["apikey"] = null;
$ENV["gtools"]["maps"] = array(
	//eg: , Buenos Aires, Argentina
	"bound" => "",

	//eg: obelisco, Buenos Aires, Argentina
	"center" => "",
	
	//eg: ["buenos", "aires", "argentina"]
	"clearwords" => []
);

?>