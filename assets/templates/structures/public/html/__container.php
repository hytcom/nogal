<?php 
require_once("config.php");

// __container.php es el receptor de las peticiones de archivos inexistentes
// por lo general es utilizado para la utilización del objeto RIND

$PRICKOUT = (isset($_SERVER["REDIRECT_SCRIPT_URL"])) ? $_SERVER["REDIRECT_SCRIPT_URL"] : $_SERVER["REDIRECT_URL"];
$ISDIR = (substr($PRICKOUT, -1)==NGL_DIR_SLASH) ? NGL_DIR_SLASH : "";
$PRICKOUT = $ngl()->prickout($PRICKOUT);
$PRICKOUT[1] .= $ISDIR;
if($PRICKOUT[0]!==false) {
	require_once($PRICKOUT[0]);
	unset($PRICKOUT);
	exit();
} else {
	if($ISDIR==NGL_DIR_SLASH) {
		$PRICKOUT = array("dirname"=>$PRICKOUT[1]);
		$PRICKOUT["filename"] = $PRICKOUT["basename"] = "index";
	} else {
		$PRICKOUT = pathinfo($PRICKOUT[1]);
	}
	unset($ISDIR);

	// exepciones --------------------------------------------------------------
	/*
	if($PRICKOUT["filename"]==$PRICKOUT["basename"] && $PRICKOUT["filename"]=="FILENAME!!!") {
		.
		.
		.
		exit();
	}
	//--------------------------------------------------------------------------
	*/

	// extensiones permitidas
	$sExtension = "html";
	$aExtension = [
		"css" => "text/css",
		"js" => "application/js",
		"json" => "application/json",
		"html" => "text/html",
		"yml" => "application/yml"
	];
	if(!empty($PRICKOUT["extension"]) && $aExtension[$PRICKOUT["extension"]]) {
		$sExtension = $PRICKOUT["extension"];
		header("Content-Type: ".$aExtension[$PRICKOUT["extension"]]);
	}

	// lectura de plantilla
	$sPath = $ngl("files")->absPath($PRICKOUT["dirname"]);
	$sBuffer = $ngl("rind")->stamp($PRICKOUT["filename"].".html");
	if($sBuffer===false) {
		$ngl()->errorPages(404);
		exit();
	}

	echo $sBuffer;
}

?>