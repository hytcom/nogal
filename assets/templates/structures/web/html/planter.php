<?php defined("NGL_SOWED") || exit();

// planter.php es el receptor de las peticiones de archivos inexistentes
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
if(!empty(NGL_GARDENS_PLACE["EXTENSION"]) && !empty($aExtension[NGL_GARDENS_PLACE["EXTENSION"]])) {
	$sExtension = NGL_GARDENS_PLACE["EXTENSION"];
	header("Content-Type: ".$aExtension[NGL_GARDENS_PLACE["EXTENSION"]]);
}

// lectura de plantilla
$sBuffer = $ngl("rind")->stamp(NGL_GARDENS_PLACE["FILENAME"].".".$sExtension);
if($sBuffer===false) {
	$ngl()->errorHTTP(404);
	exit();
}

echo $sBuffer;

?>