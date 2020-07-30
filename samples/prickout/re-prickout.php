<?php defined("NGL_SOWED") || exit();
/*
switch(true) {
	// http://www.dominio.com/foo/bar.php => http://www.dominio.com/index.php?id=foobar
	case ($_SERVER["REDIRECT_URL"] == "/foo/bar.php"):
		$NGL_REPRICKOUT_URL = "/index.php?id=foobar";
		break;

	// http://www.dominio.com/foo/1234 => http://www.dominio.com/foo.php?id=1234
	case (substr($_SERVER["REDIRECT_URL"], 0, 5)=="/foo/"):
		$NGL_REPRICKOUT_URL = explode("/", $_SERVER["REDIRECT_URL"], 3);
		$NGL_REPRICKOUT_URL = "/foo.php?id=".$NGL_REPRICKOUT_URL[2];
		break;
	
	// http://www.dominio.com/ws/getinfo => http://www.dominio.com/ws/ws/webservice.php con datos POST
	case ($_SERVER["REDIRECT_URL"] == "/ws/getinfo"):
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