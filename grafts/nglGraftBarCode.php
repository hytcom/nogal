<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___
  
# crypt
## nglCrypt *extends* nglBranch [instanciable] [2018-08-12]
Implementa la clase 'barcode-generator', para generar Códigos de Barras

https://github.com/hytcom/wiki/blob/master/nogal/docs/barcode.md

*/
namespace nogal {

	class nglGraftBarCode extends nglScion {
		
		public $barcode;

		final protected function __declareArguments__() {
			$vArguments					= array();
			$vArguments["color"]		= array('(string)$mValue', "#000000");
			$vArguments["content"]		= array('(string)$mValue', "test1234");
			$vArguments["height"]		= array('(int)$mValue', 30);
			$vArguments["margin"]		= array('(int)$mValue', 2);
			$vArguments["size"]			= array('(int)$mValue', 1);
			$vArguments["type"]			= array('(string)$mValue', "code_128");
		
			return $vArguments;
		}

		final protected function __declareAttributes__() {
			$vAttributes = array();
			return $vAttributes;
		}

		final protected function __declareVariables__() {
		}

		public function base64() {
			list($sContent, $sType, $nSize, $nMargin, $nHeight, $sColor) = $this->getarguments("content,type,size,margin,height,color", func_get_args());
			$code = new \Picqer\Barcode\BarcodeGeneratorPNG();
			$aColor = self::call()->colorRGB($sColor);
			$sSource = $code->getBarcode($sContent, $this->SetType($sType), $nSize, $nHeight, array($aColor["red"],$aColor["green"],$aColor["blue"]));
			return "data:image/png;base64,".base64_encode($sSource);
		}
	
		public function html() {
			list($sContent, $sType, $nSize, $nMargin, $nHeight, $sColor) = $this->getarguments("content,type,size,margin,height,color", func_get_args());
			$code = new \Picqer\Barcode\BarcodeGeneratorHTML();
			return $code->getBarcode($sContent, $this->SetType($sType), $nSize, $nHeight, $sColor);
		}

		public function image() {
			list($sContent, $sType, $nSize, $nMargin, $nHeight, $sColor) = $this->getarguments("content,type,size,margin,height,color", func_get_args());
			$code = new \Picqer\Barcode\BarcodeGeneratorPNG();
			$aColor = self::call()->colorRGB($sColor);
			$sSource = $code->getBarcode($sContent, $this->SetType($sType), $nSize, $nHeight, array($aColor["red"],$aColor["green"],$aColor["blue"]));
			return imagecreatefromstring($sSource);
		}

		public function png() {
			list($sContent, $sType, $nSize, $nMargin, $nHeight, $sColor) = $this->getarguments("content,type,size,margin,height,color", func_get_args());
			$code = new \Picqer\Barcode\BarcodeGeneratorPNG();
			$aColor = self::call()->colorRGB($sColor);
			$sSource = $code->getBarcode($sContent, $this->SetType($sType), $nSize, $nHeight, array($aColor["red"],$aColor["green"],$aColor["blue"]));
			header("Content-type: image/png");
			die($sSource);
		}

		public function svg() {
			list($sContent, $sType, $nSize, $nMargin, $nHeight, $sColor) = $this->getarguments("content,type,size,margin,height,color", func_get_args());
			$code = new \Picqer\Barcode\BarcodeGeneratorSVG();
			return $code->getBarcode($sContent, $this->SetType($sType), $nSize, $nHeight, $sColor);
		}

		public function text() {
			list($sContent, $sType) = $this->getarguments("content,type", func_get_args());
			require_once(__DIR__."/nglGraftBarCodeText.php");
			$code = new \Picqer\Barcode\BarcodeGeneratorText();
			return $code->getBarcode($sContent, $this->SetType($sType));
		}
		
		private function SetType($sType) {
			return constant('\Picqer\Barcode\BarcodeGenerator::TYPE_'.strtoupper($sType));
		}
	}
}

?>