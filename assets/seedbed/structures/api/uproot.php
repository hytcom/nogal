<?php defined("NGL_SOWED") || exit();

// equivalente a una RewriteRule. Siempre dentro de NGL_PATH_CROWN
// $REDIRECTURL contiene el path completo de la URL solicitada
// cuando el valor es una funcion, se pasa el valor de la variable $REDIRECTURL
//
// formato:
// 		clave: expresion regular con la que hará match $REDIRECTURL
//		valor: path o URL de destino

$NGL_UPROOTS = [
	"^/help$"				=> "https://github.com/hytcom/wiki/tree/master/nogal",

	// "^/default$"			=> NGL_PATH_CROWN."/index.php",

	// "^/foo/1234$"		=> function($r) {
	// 							$a = explode("/", $r, 3);
	// 							$_REQUEST[$a[1]] = $a[2];
	// 							return NGL_PATH_CROWN."/index.php";
	// 						},

	"^/api/" 				=> function($r) {
								return NGL_PATH_CROWN."/api.php";
							}
];

?>