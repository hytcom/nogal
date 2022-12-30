<?php
#===============================================================================
# NOGAL GARDENER JOURNAL
#===============================================================================
# desescapar cuando $CONFIGPATH sea nogal.php
// define("NGL_GARDENER_JOURNAL", true);

# config file path
$CONFIGPATH = "../config.php";

# INI ==========================================================================
function nglGardenerError($sMsg) {
	global $GARDENER_JOURNAL;
	$GARDENER_JOURNAL .= "<h6 class='error'>ERROR, ".$sMsg."</h6>";
	die($GARDENER_JOURNAL);
}

function nglGardenerPrint($sKey, $mValue) {
	if(is_object($mValue)) { $mValue = (array)$mValue; }
	if(!is_array($mValue)) {
		$sValue = ($mValue!==null) ? addcslashes($mValue, "\r\n\t") : "<i>NULL</i>";
		return "<tr><th>".$sKey."</th><td>".$sValue."</td></tr>";
	} else {
		$sReturn = "<tr><th>".$sKey.'</th><td><table align="center" border="1" class="table table-striped table-bordered">';
		foreach($mValue as $sIdx => $mVal) {
			$sReturn .= nglGardenerPrint($sIdx, $mVal);
		}
		$sReturn .= "</table></td></tr>";
		return $sReturn;
	}
}

define("NGL_IM_GARDENER", true);
ini_set("display_errors", 0);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);

$PHPVERSION = phpversion();
$PHPINTERFACE = PHP_SAPI;
$CURPATH = getcwd();

$GARDENER_JOURNAL = <<<'NOGAL'
<!DOCTYPE html>
<html>
	<head>
		<title>nogal gardener journal</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" />
		<style>
			body { font-family:sans-serif; text-align:center; }
			th,td { text-align: left; font-size: 11px; padding: 2px 6px; }
			.nogal { font-size: 10px; font-weight: normal; line-height: 1em; }
			.nogal b { font-size: 12px; font-weight: bold !important}
			small, small * { font-size: 10px; font-weight: normal; }
			address { color: #AB0000; font-size: 18px; font-weight: bold; padding: 5px; text-align: center; }
			.success { background-color: #00AB00; color: #FFFFFF; font-size: 18px; font-weight: bold; margin: 10px; padding: 10px; text-align: center; }
			.error { background-color: #AB0000; color: #FFFFFF; margin: 4px; padding: 4px; text-align: center; }
			h6 { padding: 10px !important; }
		</style>
	</head>
	<body>
		<div class="container">
			<br />
			<svg
				xmlns="http://www.w3.org/2000/svg"
				xml:space="preserve"
				height="100px"
				version="1.1"
				style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd"
				viewBox="0 0 287 230"
				xmlns:xlink="http://www.w3.org/1999/xlink"
			>
				<defs>
					<style type="text/css">
						<![CDATA[
							.cup {fill:#000000;fill-rule:nonzero}
							.trunk {fill:#AB0000;fill-rule:nonzero}
						]]>
					</style>
				</defs>
				<g id="nogal">
					<path class="cup" d="M40 80c-26,0 -39,32 -21,50 6,5 13,8 21,8 3,0 5,3 5,6 0,22 25,36 44,25 5,-3 7,4 20,4 13,0 15,-7 20,-4 9,5 20,5 29,0 5,-3 7,4 20,4 13,0 15,-7 20,-4 19,11 44,-3 44,-25 0,-3 2,-6 5,-6 26,0 39,-31 21,-50 -5,-5 -13,-8 -21,-8 -3,0 -5,-2 -5,-5 0,-17 -13,-30 -29,-30 -3,0 -6,-2 -6,-5 0,-8 -3,-15 -8,-21 -9,-9 -24,-11 -35,-4 -5,3 -7,-4 -20,-4 -13,0 -15,7 -20,4 -19,-11 -44,2 -44,25 0,3 -2,5 -5,5l0 0c-17,0 -30,13 -30,30 0,3 -2,5 -5,5zm-28 1c6,-6 14,-10 23,-12 2,-18 16,-32 34,-34 4,-27 33,-43 57,-31 11,-5 24,-5 35,0 24,-12 53,4 57,31 18,2 32,16 34,34 33,5 47,45 24,68 -6,6 -15,11 -24,12 -3,27 -32,42 -56,31 -11,5 -24,5 -35,0 -11,5 -24,5 -35,0 -11,5 -23,5 -34,0 -25,11 -54,-4 -57,-31 -33,-4 -47,-45 -23,-68z"/>
					<path class="trunk" d="M129 123l0 -39c0,-7 -3,-14 -8,-19 -5,-5 -12,-8 -19,-8 -5,0 -8,-3 -8,-7 0,-4 3,-8 8,-8 11,0 22,5 29,12 8,8 13,19 13,30 0,-11 4,-22 12,-30 8,-7 18,-12 30,-12 4,0 7,4 7,8 0,4 -3,7 -7,7 -8,0 -14,3 -19,8 -5,5 -8,12 -8,19l0 39 33 -19 0 0c7,-4 11,-10 13,-17 2,-6 1,-14 -3,-20 -2,-4 -1,-8 3,-11 4,-2 8,0 10,3 6,10 7,22 5,32 -3,11 -10,20 -20,26l0 0 -21 12 24 0c8,0 14,-3 19,-8 5,-5 8,-12 8,-19 0,-5 3,-8 8,-8 4,0 7,3 7,8 0,11 -4,22 -12,29 -8,8 -18,13 -30,13l-44 0 0 44c0,7 3,14 8,19 5,5 11,8 19,8 4,0 7,3 7,8 0,4 -3,7 -7,7 -12,0 -22,-5 -30,-12 -8,-8 -12,-18 -12,-30 0,12 -5,22 -13,30 -7,7 -18,12 -29,12 -5,0 -8,-3 -8,-7 0,-5 3,-8 8,-8 7,0 14,-3 19,-8 5,-5 8,-12 8,-19l0 -44 -45 0c-11,0 -22,-5 -30,-13 -7,-7 -12,-18 -12,-29 0,-5 4,-8 8,-8 4,0 7,3 7,8 0,7 3,14 8,19 5,5 12,8 19,8l24 0 -21 -12 0 0c-10,-6 -16,-15 -19,-26 -3,-10 -2,-22 4,-32 2,-3 7,-5 10,-3 4,3 5,7 3,11 -4,6 -4,14 -3,20 2,7 7,13 13,17l0 0 34 19z"/>
				</g>
			</svg>
			<br />
			<small class="nogal">
				<b>nogal</b><br />
				the most simple PHP framework<br />
				click <a href="https://github.com/hytcom/wiki/tree/master/nogal" target="_blank">aqu&iacute;</a> para mas info<br />
				<br />
			</small>
NOGAL;

// phpinfo
if(isset($_GET["info"])) { phpinfo(); exit(); }

if(isset($_SERVER["SHELL"])) { $_SERVER["DOCUMENT_ROOT"] = $_SERVER["PWD"]; }
if(!isset($_SERVER["SERVER_SOFTWARE"])) { $_SERVER["SERVER_SOFTWARE"] = ""; }
if(!isset($_SERVER["HTTP_HOST"])) { $_SERVER["HTTP_HOST"] = "localhost"; }
if(!isset($_SERVER["REMOTE_ADDR"])) { $_SERVER["REMOTE_ADDR"] = "localhost"; }
if(!isset($_SERVER["SERVER_ADDR"])) { $_SERVER["SERVER_ADDR"] = "localhost"; }
$GARDENER_JOURNAL .= <<<NOGAL
			<table align="center" border="1" class="table table-striped table-bordered">
				<tr><th>PHP VERSION</th><td>{$PHPVERSION}</td></tr>
				<tr><th>PHP CLIENT</th><td>{$PHPINTERFACE}</td></tr>
				<tr><th>CURRENT PATH</th><td>{$CURPATH}</td></tr>
				<tr><th>\$_SERVER['SERVER_SOFTWARE']</th><td>{$_SERVER["SERVER_SOFTWARE"]}</td></tr>
				<tr><th>\$_SERVER['DOCUMENT_ROOT']</th><td>{$_SERVER["DOCUMENT_ROOT"]}
NOGAL;
if(isset($_SERVER["SHELL"])) { $GARDENER_JOURNAL .= " ( using \$_SERVER['PWD'] )"; }
$GARDENER_JOURNAL .= <<<NOGAL
				</td></tr>
				<tr><th>\$_SERVER['PHP_SELF']</th><td>{$_SERVER["PHP_SELF"]}</td></tr>
				<tr><th>\$_SERVER['HTTP_HOST']</th><td>{$_SERVER["HTTP_HOST"]}</td></tr>
				<tr><th>\$_SERVER['SERVER_ADDR']</th><td>{$_SERVER["SERVER_ADDR"]}</td></tr>
				<tr><th>\$_SERVER['REMOTE_ADDR']</th><td>{$_SERVER["REMOTE_ADDR"]}</td></tr>
				<tr><td colspan="2" class="text-center">Para más detalles consultar <a href="gardener.php?info=1" target="_blank">phpinfo()</a></td></tr>
			</table>
			<br />
NOGAL;

// load config
if(!file_exists($CONFIGPATH)) {
	nglGardenerError("no se pudo hallar el archivo de configuración: ".$CONFIGPATH);
}
require_once($CONFIGPATH);

if(defined("NGL_GARDENER_JOURNAL") && !NGL_GARDENER_JOURNAL) { die("404 Not Found"); }

if(!is_dir(NGL_PATH_FRAMEWORK)) { nglGardenerError("NGL_PATH_FRAMEWORK no es una ruta válida: ".NGL_PATH_FRAMEWORK); }
if(!is_dir(NGL_PATH_GARDEN)) { nglGardenerError("NGL_PATH_GARDEN no es una ruta válida: ".NGL_PATH_GARDEN); }
if(defined("NGL_SOWED")) {
	$INFO = "";

	// services passwords
	if(NGL_PASSWORD_KEY!==null) {
		$INFO .= "<tr><th>SERVICES PASSWORD <br><small>utilizado para encriptar los passwords de los servicios (MySQL, gmail, FTP)</small></th><td><form method='post'><input type='text' name='pwd' placeholder='password a encriptar/desencriptar' /> &nbsp;<input type='checkbox' name='pwdrev' /> decrypt &nbsp;<input type='submit' /></form>";
		if(isset($_POST["pwd"])) {
			$INFO .= "<br />".$ngl->passwd($_POST["pwd"], isset($_POST["pwdrev"]));
		}
		$INFO .= "</td></tr>";
	} else {
		$INFO .= "<tr><th>SERVICES PASSWORD <br><small>utilizado para encriptar las passwords de los servicios (MySQL, gmail, FTP)</small></th><td>para utilizarse, deber&aacute; establecer un valor en la constante <b>NGL_PASSWORD_KEY</b></td></tr>";
	}

	$CONSTANTS = get_defined_constants(true);
	$CONSTANTS = $CONSTANTS["user"];
	ksort($CONSTANTS);
	foreach($CONSTANTS as $key => $value) {
		if(substr($key, 0, 4)!="NGL_") { continue; }
		$value = (is_bool($value)) ? ($value===false) ? "<i>FALSE</i>" : "<i>TRUE</i>" : $value;

		if(in_array($key, array("NGL_ALVIN", "NGL_BEE", "NGL_PASSWORD_KEY"))) { $value = ($value!==null) ? "<b>DEFINED</b>" : "<b>UNDEFINED</b>"; }

		// $value = ($value!==null) ? nglGardenerPrint($value) : "<i>NULL</i>";
		// $INFO .= "<tr><th>".$key."</th><td>".$value."</td></tr>";
		$INFO .= nglGardenerPrint($key, $value);
	}
}

$GARDENER_JOURNAL .= <<<NOGAL
	<address>
		Cuando la configuraci&oacute;n est&eacute; completa, podr&aacute; verse "LISTO!" al pie de esta p&aacute;gina<br />
		ATENCION!!! Desactivar el Gardener al finalizar su utilizaci&oacute;n (NGL_GARDENER_JOURNAL = false)<br />
		<br />
	</address>
	<table align="center" border="1" class="table table-striped table-bordered">
		{$INFO}
	</table>
NOGAL;

if(file_exists($sConfigFile = NGL_PATH_CONF.NGL_DIR_SLASH."rind.conf")) {
	$RINDERROR = array("root"=>"","gui"=>"","cache"=>"");
	$RIND = $ngl()->parseConfigString(file_get_contents($sConfigFile), true);
	if(!is_dir($RIND["arguments"]["root"])) { $RINDERROR["root"] = " <b class='error'> no es un directorio</b>"; }
	if(!is_dir($RIND["arguments"]["gui"])) { $RINDERROR["gui"] = " <b class='error'> no es un directorio</b>"; }
	if(!is_dir($RIND["arguments"]["cache"])) {
		$RINDERROR["cache"] = " <b class='error'> no es un directorio</b>";
	} else {
		if(@file_put_contents($RIND["arguments"]["cache"]."/NOGAL.txt", "NOGAL")) {
			unlink($RIND["arguments"]["cache"]."/NOGAL.txt");
		} else {
			$RINDERROR["cache"] = " <b class='error'> fall&oacute; al intentar escribir en el directorio cache. Permiso denegado</b>";
		}
	}

$GARDENER_JOURNAL .= <<<NOGAL
			<address style="margin-bottom: 5px;">RIND CONFIG</address>
			<table align="center" border="1" class="table table-striped table-bordered">
				<tr><th>root</th><td>{$RIND["arguments"]["root"]}{$RINDERROR["root"]}</td></tr>
				<tr><th>gui</th><td>{$RIND["arguments"]["gui"]}{$RINDERROR["gui"]}</td></tr>
				<tr><th>cache</th><td>{$RIND["arguments"]["cache"]}{$RINDERROR["cache"]}</td></tr>
			</table>
NOGAL;
}

$HOME = NGL_URL;
$GARDENER_JOURNAL .= <<<NOGAL
			<br />
			<div class="success">LISTO</div>
			<br />
			<a href="{$HOME}/index.php" class="btn btn-sm btn-primary">ir al home</a>
			<br /><br />
		</div>
	</body>
</html>
NOGAL;

// report
if(isset($_SERVER["SHELL"])) {
	$GARDENER_JOURNAL = preg_replace("/<style>(.*?)<\/style>/s", "", $GARDENER_JOURNAL);
	$GARDENER_JOURNAL = str_replace(
		array("\t", "\n", "<br />", "</th><td>","</td></tr>"),
		array("", "", "\n", "  =  ", "\n"),
		$GARDENER_JOURNAL
	);
	echo "\n--------------------------------------------------------------------------------\n\n";
	echo strip_tags($GARDENER_JOURNAL);
	echo "\n\n--------------------------------------------------------------------------------\n";
} else {
	die($GARDENER_JOURNAL);
}

?>