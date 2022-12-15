<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# ocr
https://hytcom.net/nogal/docs/objects/ocr.md
*/
namespace nogal;
class nglGraftOCR extends nglScion {

	public $ocr;

	final protected function __declareArguments__() {
		$vArguments					= [];
		$vArguments["filepath"]		= ['(string)$mValue', null];
		$vArguments["savefile"]		= ['(string)$mValue', null];
		$vArguments["langs"]		= ['$this->SetLangs($mValue)', null];
		$vArguments["patterns"]		= ['$this->SetPatterns($mValue)', null];
		$vArguments["dictionary"]	= ['$this->SetDictionary($mValue)', null];
		$vArguments["digits"]		= ['self::call()->istrue($mValue)', false];
		$vArguments["whitelist"]	= ['$this->SetWhiteList($mValue)', null];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = [];
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
		if(!\class_exists('\thiagoalessio\TesseractOCR\TesseractOCR')) {
			$this->installPackage("thiagoalessio/tesseract_ocr", "^2.9");
		}
		$this->ocr = new \thiagoalessio\TesseractOCR\TesseractOCR();
		$this->ocr->tempDir(self::call()->tempDir());
	}

	public function load() {
		list($sFilePath) = $this->getarguments("filepath", \func_get_args());
		$sFilePath = self::call()->sandboxPath($sFilePath);
		if(!\file_exists($sFilePath)) { return false; }
		$this->ocr->image($sFilePath);

		// digitos
		if($this->argument("digits")) { $this->ocr->digits(); }

		return $this;
	}

	public function get() {
		return $this->ocr->run();
	}

	public function save() {
		list($sFilePath) = $this->getarguments("savefile", \func_get_args());
		$sFilePath = self::call()->sandboxPath($sFilePath);
		if(\is_writable(\pathinfo($sFilePath, PATHINFO_DIRNAME))) {
			$this->ocr->setOutputFile($sFilePath)->run();
			return $this;
		}

		return false;
	}

	protected function SetLangs($mLangs) {
		if($mLangs!==null) {
			$this->ocr->tessdataDir(__DIR__."/data/thiagoalessio/");
			$aLangs = (\is_array($mLangs)) ? $mLangs : self::call()->explodeTrim(",", $sLangs);
			$this->ocr->lang(...$aLangs);
			return $sFilePath;
		}
	}

	protected function SetPatterns($sFilePath) {
		$sFilePath = self::call()->sandboxPath($sFilePath);
		if(\file_exists($sFilePath)) {
			$this->ocr->userPatterns($sFilePath);
		}
		return $sFilePath;
	}

	protected function SetDictionary($sFilePath) {
		$sFilePath = self::call()->sandboxPath($sFilePath);
		if(\file_exists($sFilePath)) {
			$this->ocr->userWords($sFilePath);
		}
		return $sFilePath;
	}

	protected function SetWhiteList($aLists) {
		if(!self::call()->isArrayArray($aLists, "edges")) { $aLists = [$aLists]; }
		$this->ocr->whitelist(...$aLists);
		return $aLists;
	}
}

?>