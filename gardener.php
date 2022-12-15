<?php \defined("NGL_SOWED") || exit();

if(NGL_TERMINAL) { $_SERVER["REDIRECT_URL"] = $argv[1]; }

$REDIRECTURL = false;
if(isset($_SERVER["REDIRECT_URL"]) || isset($_SERVER["REDIRECT_SCRIPT_URL"])) {
	$REDIRECTURL = (isset($_SERVER["REDIRECT_SCRIPT_URL"]) ? $_SERVER["REDIRECT_SCRIPT_URL"] : $_SERVER["REDIRECT_URL"]);
	$GARDENS = $ngl()->gardensplace($REDIRECTURL, NGL_PATH_CROWN);
} else {
	$GARDENS = $ngl()->gardensplace($_SERVER["PHP_SELF"], NGL_PATH_CROWN);
}


//	chequeo del referer --------------------------------------------------------
if(NGL_REFERER && isset($ENV["referer-check"])) {
	if($ngl()->inCurrentPath(\array_keys($ENV["referer-check"]))!==false && $ngl()->inCurrentPath(\array_keys($ENV["referer-ignore"]))===false) {
		$ngl()->chkreferer();
	}
}


// redirecciones ---------------------------------------------------------------
if(\file_exists(NGL_PATH_GARDEN.NGL_DIR_SLASH."uproot.php")) {
	require_once(NGL_PATH_GARDEN.NGL_DIR_SLASH."uproot.php");

	foreach($NGL_UPROOTS as $COND => $RULE) {
		if(\preg_match("#".$COND."#", $REDIRECTURL)) {
			$NGL_UPROOT = \is_callable($RULE) ? $RULE($REDIRECTURL) : $RULE;
		}
	}
	unset($NGL_UPROOTS);

	if(isset($NGL_UPROOT) && $ngl()->isURL($NGL_UPROOT)) {
		\header("location:".$NGL_UPROOT);
		exit();
	}
}


// chequeo por once code -------------------------------------------------------
if(NGL_ONCECODE && isset($ENV["oncecode-check"])) {
	if($ngl()->inCurrentPath(\array_keys($ENV["oncecode-check"]))!==false && $ngl()->inCurrentPath(\array_keys($ENV["oncecode-ignore"]))===false) {
		$NGL_ONCECODE = (isset($_REQUEST["values"]["NGL_ONCECODE"])) ? $_REQUEST["values"]["NGL_ONCECODE"] : ( isset($_REQUEST["NGL_ONCECODE"]) ? $_REQUEST["NGL_ONCECODE"] : null);
		if($NGL_ONCECODE===null || !$ngl()->once($NGL_ONCECODE)) {
			$ngl()->errorHTTP(403);
		}
	}
}


// carga del documento ---------------------------------------------------------
$AUTOINDEX = $GARDENS[2];
if($GARDENS[0]!==false) {
	$sExtension = \strtolower(\pathinfo($GARDENS[0], PATHINFO_EXTENSION));
	if($sExtension!=="php") {
		$sMimeType = $ngl()->mimeType($sExtension);
		@header("Content-Type: ".$sMimeType);
	}
	if(\file_exists($GARDENS[0])) {
		$GARDENS_REQUIRE = $GARDENS[0];
	}
} else {
	if(\substr($GARDENS[1], -1)==NGL_DIR_SLASH) {
		$GARDENS = ["dirname"=>$GARDENS[1]];
		$GARDENS["filename"] = $GARDENS["basename"] = "index";
		$GARDENS["autoindex"] = true;
	} else {
		$GARDENS = \pathinfo($GARDENS[1]);
	}

	if(!\in_array($_SERVER["SCRIPT_FILENAME"], [
		$GARDENS["dirname"].NGL_DIR_SLASH."planter.php",
		NGL_PATH_CROWN.NGL_DIR_SLASH."planter.php",
		$GARDENS["dirname"].NGL_DIR_SLASH.$GARDENS["basename"]
	])) {
		if(isset($NGL_UPROOT)) {
			$GARDENS_REQUIRE = $NGL_UPROOT;
		} else if(\file_exists($GARDENS["dirname"].NGL_DIR_SLASH."planter.php")) {
			$GARDENS_REQUIRE = $GARDENS["dirname"].NGL_DIR_SLASH."planter.php";
		} else if(\file_exists(NGL_PATH_CROWN.NGL_DIR_SLASH."planter.php")) {
			$GARDENS_REQUIRE = NGL_PATH_CROWN.NGL_DIR_SLASH."planter.php";
		} else if(\file_exists($GARDENS["dirname"].NGL_DIR_SLASH.$GARDENS["basename"])) {
			$GARDENS_REQUIRE = $GARDENS[1];
		}
	}
}

if(isset($GARDENS, $GARDENS_REQUIRE)) {
	$aPlanter = $ngl("sysvar")->SELF;
	$aPlanter["autoindex"] = $AUTOINDEX;
	if(\array_key_exists("autoindex", $GARDENS)) {
		$aPlanter["autoindex"] 		= true;
		$aPlanter["dirname"]		= \basename($GARDENS["dirname"]);
		$aPlanter["urldirname"] 	= $aPlanter["dirname"]."/";
		$aPlanter["basename"]		= $GARDENS["basename"];
		$aPlanter["filename"]		= $GARDENS["filename"];
		$aPlanter["path"]			.= "/".\basename($GARDENS["dirname"]);
		$aPlanter["fullpath"]		.= "/index";
		$aPlanter["url"] 			.= "/index";
		$aPlanter["urlpath"]		.= "/index";
		$aPlanter["fullurl"]		.= "/index";
		$aPlanter["fullurlpath"]	.= "/index";
	}
	$aPlanter = \array_map(function($sPath){
		return \str_replace(NGL_PATH_GARDEN, NGL_PATH_CROWN, $sPath);
	}, $aPlanter);

	$aPlanter["uproot"] = isset($NGL_UPROOT) ? $NGL_UPROOT : null;
	$aPlanter = \array_merge(["method" => (!empty($_SERVER["REQUEST_METHOD"]) ? \strtoupper($_SERVER["REQUEST_METHOD"]) : null)], $aPlanter);
	\define("NGL_GARDENS_PLACE", \array_change_key_case($aPlanter, CASE_UPPER));
	unset($GARDENS, $AUTOINDEX, $NGL_UPROOT, $sExtension, $sMimeType, $aPlanter);
	require_once($GARDENS_REQUIRE);
} else {
	$ngl()->errorHTTP(418);
	exit();
}

?>