<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# qr
https://hytcom.net/nogal/docs/objects/qr.md
*/
namespace nogal;
class nglGraftQR extends nglScion {

	public $qr = null;

	final protected function __declareArguments__() {
		$vArguments					= [];
		$vArguments["content"]		= ['(string)$mValue', "test1234"];
		$vArguments["eclevel"]		= ['\strtolower($mValue)', "L", ["L","M","Q","H"]];
		$vArguments["size"]			= ['$this->SetPointSize($mValue)', 4];
		$vArguments["margin"]		= ['$this->SetMargin($mValue)', 0];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = [];
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
		if(!\class_exists("\PHPQRCode\QRcode")) {
			$this->installPackage("aferrandini/phpqrcode","^1.0");
		}
		$this->qr = new \PHPQRCode\QRcode();
	}

	public function image() {
		list($sContent, $nMargin, $nPointSize, $sECLevel) = $this->getarguments("content,margin,size,eclevel", \func_get_args());
		\ob_start();
		$this->qr->png($sContent, false, $sECLevel, $nPointSize, $nMargin);
		@\header("Content-Type: ".NGL_CONTENT_TYPE);
		$sSource = \ob_get_contents();
		\ob_end_clean();
		return \imagecreatefromstring($sSource);
	}

	public function png() {
		list($sContent, $nMargin, $nPointSize, $sECLevel) = $this->getarguments("content,margin,size,eclevel", \func_get_args());
		return $this->qr->png($sContent, false, $sECLevel, $nPointSize, $nMargin);
	}

	public function text() {
		list($sContent, $nMargin, $nPointSize, $sECLevel) = $this->getarguments("content,margin,size,eclevel", \func_get_args());
		return $this->qr->text($sContent, false, $sECLevel, $nPointSize, $nMargin);
	}

	public function raw() {
		list($sContent, $nMargin, $nPointSize, $sECLevel) = $this->getarguments("content,margin,size,eclevel", \func_get_args());
		return $this->qr->raw($sContent, false, $sECLevel, $nPointSize, $nMargin);
	}

	protected function SetMargin($nMargin) {
		$nMargin = (int)$nMargin;
		if($nMargin<0) { $nMargin = 0; }
		return $nMargin;
	}

	protected function SetPointSize($nPointSize) {
		$nPointSize = (int)$nPointSize;
		if($nPointSize<0) { $nPointSize = 0; }
		return $nPointSize;
	}
}

?>