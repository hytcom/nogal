<?php defined("NGL_SOWED") || exit();

// determinacion del origen de datos ---------------------------------------------------------------
if(isset($_SERVER["REQUEST_METHOD"])) {
	$aRequest = ($_SERVER["REQUEST_METHOD"]=="GET") ? $_GET : $_POST; // datos enviados
	// control de archivos adjuntos
	$aRequest["_FILES"] = false;
	if(isset($_FILES)) {
		foreach($_FILES as $x => $aFile) {
			if($aFile["error"]!==0) { unset($_FILES[$x]); }
		}
		if(count($_FILES)) { $aRequest["_FILES"] = true; }
	}
}

// ejecucion del tutor -----------------------------------------------------------------------------
try {
	$aTutor = explode("/", $PRICKOUT[1]);
	$sTutor = array_shift($aTutor);
	$sMethod = (isset($aTutor[0])) ? array_shift($aTutor) : $sTutor;
	$aRequest["_TUTOR"] = array(
							"path" => $PRICKOUT[1],
							"name" => $sTutor,
							"method" => $sMethod,
							"args" => $aTutor
						);
	$tutor = $ngl("tutor.".$sTutor);
	$response = $tutor->run($sMethod, $aRequest);
	if($tutor->debugging()) { exit($response); }

	// respuesta ---------------------------------------------------------------------------------------
	if(is_array($response) && isset($response["NGL_REDIRECT"])) {
		$aRequest["NGL_REDIRECT"] = $response["NGL_REDIRECT"];
		unset($response["NGL_REDIRECT"]);
	}

	$sResponse = json_encode($response);
	if(isset($aRequest["NGL_REDIRECT"])) { 
		if(strpos($aRequest["NGL_REDIRECT"], "?")!==false) {
			$sURL = $ngl("url")
				->url($aRequest["NGL_REDIRECT"])
				->update("params", array("response" => base64_encode($sResponse)))
				->unparse()
			;
		} else {
			$sURL = $aRequest["NGL_REDIRECT"]."?response=".base64_encode($sResponse);
		}
		exit($sURL);
	} else {
		die($sResponse);
	}
} catch (Exception $e) {
	die(json_encode($e->getMessage()));
}

?>