<?php defined("NGL_SOWED") || exit();

// determinacion del origen de datos ---------------------------------------------------------------
if(isset($_SERVER["REQUEST_METHOD"])) {
	$aRequest = ($_SERVER["REQUEST_METHOD"]=="GET") ? $_GET : $_POST; // datos enviados
	$aRequest["_FILES"] = (!empty($_FILES)) ? true : false;
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
	if(is_array($response)) {
		if(isset($response["NGL_REDIRECT"])) { $aRequest["NGL_REDIRECT"] = $response["NGL_REDIRECT"]; unset($response["NGL_REDIRECT"]); }
		if(isset($response["NGL_LOCATION"])) { $aRequest["NGL_LOCATION"] = $response["NGL_LOCATION"]; unset($response["NGL_LOCATION"]); }
	}

	$sResponse = json_encode($response);
	if(!empty($aRequest["NGL_REDIRECT"]) || !empty($aRequest["NGL_LOCATION"])) {
		$sURL = (!empty($aRequest["NGL_LOCATION"])) ? $aRequest["NGL_LOCATION"] : $aRequest["NGL_REDIRECT"];
		if(strpos($sURL, "?")!==false) {
			$sURL = $ngl("url")
				->url($sURL)
				->update("params", array("response" => base64_encode($sResponse)))
				->unparse()
			;
		} else {
			$sURL = $sURL."?response=".base64_encode($sResponse);
		}

		if(!empty($aRequest["NGL_LOCATION"])) {
			header("location:".$sURL);
			exit();
		} else {
			die($sURL);
		}
	} else {
		die($sResponse);
	}
} catch (Exception $e) {
	die(json_encode($e->getMessage()));
}

?>