<?php

namespace nogal;

/** CLASS {
	"name" : "nglGraftQR",
	"object" : "qr",
	"type" : "instanciable",
	"revision" : "20150930",
	"extends" : "nglBranch",
	"interfaces" : "inglFeeder",
	"description" : "Implementa https://github.com/aferrandini/PHPQRCode",
	"arguments": {
		"content" : ["string", "Contenido del código", "test1234"],
		"eclevel" : ["int", "Nivel de corrección de errores (L|M|Q|H)", "L"],
		"size" : ["int", "Cantidad de pixels por punto", "4"],
		"margin" : ["int", "Margen entre el borde de la imagen y el contenido del código", "0"]
	}
} **/

/*
$ngl("image.foo")->load(
	$ngl("qr.")
		->args(array(
			"point_size" => 5,
			"margin" => 2
		))
		->image("http://qareful.com/q.yudWcFVxr7EbZB9o6g6FQBZND5TEBPTzZ2YIWnvN5osJgJNkpaOMRtI0YNW14j0Z")
	)
	->view()
;
*/
class nglGraftQR extends nglScion {

	public $qr = null;

	final protected function __declareArguments__() {
		$vArguments					= array();
		$vArguments["content"]		= array('(string)$mValue', "test1234");
		$vArguments["eclevel"]		= array('$this->SetECLevel($mValue)', "L");
		$vArguments["size"]			= array('$this->SetPointSize($mValue)', 4);
		$vArguments["margin"]		= array('$this->SetMargin($mValue)', 0);
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = array();
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		$this->qr = new \PHPQRCode\QRcode();
	}

	/** FUNCTION {
		"name" : "image",
		"type" : "public",
		"description" : "Genera y retorna el puntero de la imagen del código QR",
		"parameters" : { 
			"$sContent" : ["string", "Contenido del código","argument::content"],
			"$nMargin" : ["string", "Margen entre el borde de la imagen y el contenido del código","argument::margin"],
			"$nPointSize" : ["string", "Altura máxima de la imagen del código","argument::size"],
			"$sECLevel" : ["string", "Resolución de la imagen del código","argument::eclevel"]
		},
		"examples" : {
			"impresión de imagen" : "
				$qr = $ngl("qr.");
				$qr->args(array("size" => 5, "margin" => 2));
				$ngl("image.code")->load($qr->image("test"))->view();
			"
		},
		"return": "image resource"
	} **/
	public function image() {
		list($sContent, $nMargin, $nPointSize, $sECLevel) = $this->getarguments("content,margin,size,eclevel", func_get_args());
		ob_start();
		$this->qr->png($sContent, false, $sECLevel, $nPointSize, $nMargin);
		@header("Content-Type: ".NGL_CONTENT_TYPE);
		$sSource = ob_get_contents();
		ob_end_clean();
		return imagecreatefromstring($sSource);
	}

	public function png() {
		list($sContent, $nMargin, $nPointSize, $sECLevel) = $this->getarguments("content,margin,size,eclevel", func_get_args());
		return $this->qr->png($sContent, false, $sECLevel, $nPointSize, $nMargin);
	}

	public function text() {
		list($sContent, $nMargin, $nPointSize, $sECLevel) = $this->getarguments("content,margin,size,eclevel", func_get_args());
		return $this->qr->text($sContent, false, $sECLevel, $nPointSize, $nMargin);
	}

	public function raw() {
		list($sContent, $nMargin, $nPointSize, $sECLevel) = $this->getarguments("content,margin,size,eclevel", func_get_args());
		return $this->qr->raw($sContent, false, $sECLevel, $nPointSize, $nMargin);
	}

	protected function SetMargin($nMargin) {
		$nMargin = (int)$nMargin;
		if($nMargin<0) { $nMargin = 0; }
		return $nMargin;
	}
	
	protected function SetECLevel($sECLevel) {
		$sECLevel = strtoupper($sECLevel[0]);
		if(!in_array($sECLevel, array("L","M","Q","H"))) { $sECLevel = "L"; }
		return $sECLevel;
	}

	protected function SetPointSize($nPointSize) {
		$nPointSize = (int)$nPointSize;
		if($nPointSize<0) { $nPointSize = 0; }
		return $nPointSize;
	}
}

?>