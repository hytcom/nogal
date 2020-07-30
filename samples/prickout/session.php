<?php

// sesiones nativas de PHP -----------------------------------------------------
$ngl("sess")->start();


// sesiones en file system (NGL_PATH_SESSIONS) ---------------------------------
// $ngl("sess")->start("fs");


// sesiones en MySQL -----------------------------------------------------------
/*
$ngl("sess")->start(
	$ngl("mysql.sessions")
		->host("localhost")
		->user("root")
		->pass("")
		->base()
		->connect()
);
*/


// sesiones en SQLite ----------------------------------------------------------
/*
$ngl("sess")->start(
	$ngl("sqlite.sessions")
		->base(NGL_PATH_SESSIONS.NGL_DIR_SLASH."sessions.sqlite")
		->pass(NGL_PASSWORD_KEY)
		->connect()
);
*/

// idioma
if(!isset($_SESSION[NGL_SESSION_INDEX]["LANGUAGE"])) {
	$_SESSION[NGL_SESSION_INDEX]["LANGUAGE"] = "en";
	if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
		$_SESSION[NGL_SESSION_INDEX]["LANGUAGE"] = strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2));
	}
	if(!in_array($_SESSION[NGL_SESSION_INDEX]["LANGUAGE"], array("es","en"))) {
		$_SESSION[NGL_SESSION_INDEX]["LANGUAGE"] = "en";
	}
}

?>