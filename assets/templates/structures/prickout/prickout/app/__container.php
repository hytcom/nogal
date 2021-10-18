<?php defined("NGL_SOWED") || exit();

// __container.php es el receptor de las peticiones de archivos inexistentes
// por lo general es utilizado para la utilización del objeto RIND

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

$sPath = $ngl("files")->absPath($PRICKOUT["dirname"]);
$sBuffer = $ngl("rind")
	->root(NGL_PATH_PRICKOUT)
	->gui(NGL_PATH_PRICKOUT."/../gui")
	->stamp($PRICKOUT["filename"].".".$sExtension)
;

if($sBuffer===false) {
	$ngl()->errorPages(404);
	exit();
}

echo $sBuffer;

?>