<?php defined("NGL_SOWED") || exit();

/** $REDIRECTURL ---------------------------------------------------------------
contiene el path completo de la URL solicitada
------------------------------------------------------------------------------*/

/*
switch(true) {
	// equivalente a una RewriteRule. Siempre dentro de NGL_PATH_PRICKOUT
	case (substr($REDIRECTURL, 0, 7) == "/admin/"):
		$REDIRECTURL = "/tools/".substr($REDIRECTURL, 7);
		break;

	// http://www.dominio.com/foo/bar.php => http://www.dominio.com/index.php?id=foobar
	case ($REDIRECTURL == "/foo/bar.php"):
		$NGL_REPRICKOUT_URL = "/index.php?id=foobar";
		break;

	// http://www.dominio.com/foo/1234 => http://www.dominio.com/foo.php?id=1234
	case (substr($REDIRECTURL, 0, 5)=="/foo/"):
		$NGL_REPRICKOUT_URL = explode("/", $REDIRECTURL, 3);
		$NGL_REPRICKOUT_URL = "/foo.php?id=".$NGL_REPRICKOUT_URL[2];
		break;
	
	// http://www.dominio.com/ws/getinfo => http://www.dominio.com/ws/ws/webservice.php con datos POST
	case ($REDIRECTURL == "/ws/getinfo"):
		$NGL_REPRICKOUT_REQUEST	= array("method"=>"getinfo", "body"=>file_get_contents("php://input"));
		$NGL_REPRICKOUT_URL		= NGL_URL."/ws/webservice.php";
		break;
}
*/

if(isset($NGL_REPRICKOUT_URL)) {
	if(isset($NGL_REPRICKOUT_REQUEST)) {
		$req = $ngl("file")->load($NGL_REPRICKOUT_URL);
		$options = array(
			"CURLOPT_CUSTOMREQUEST" => "POST",
			"CURLOPT_POST" => 1,
			"CURLOPT_POSTFIELDS" => $NGL_REPRICKOUT_REQUEST
		);

		echo $req->read(null, $options);
	} else {
		header("location:".$NGL_REPRICKOUT_URL);
	}
	
	exit();
}

?>