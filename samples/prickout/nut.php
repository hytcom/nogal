<?php defined("NGL_SOWED") || exit();

// determinacion del origen de datos ---------------------------------------------------------------
if(isset($_SERVER["REQUEST_METHOD"])) {
	$aRequest = ($_SERVER["REQUEST_METHOD"]=="GET") ? $_GET : $_POST; // datos enviados
}

// ejecucion del nut -----------------------------------------------------------------------------
try {
	$aNut = explode("/", $PRICKOUT[1]);
	$sNut = array_shift($aNut);
	$sMethod = (isset($aNut[0])) ? array_shift($aNut) : $sNut;
	$aRequest["_NUT"] = array(
							"path" => $PRICKOUT[1],
							"name" => $sNut,
							"method" => $sMethod,
							"args" => $aNut
						);
	$nut = $ngl("nut.".$sNut);
	echo $nut->run($sMethod, $aRequest);
} catch (Exception $e) {
	die($e->getMessage());
}

?>