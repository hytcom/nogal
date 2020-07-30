<?php
namespace Picqer\Barcode {
	class BarcodeGeneratorText extends BarcodeGenerator {
		public function getBarcode($sCode, $sType) {
			$aCode = $this->getBarcodeData($sCode, $sType);
			$sCode = "";
			foreach($aCode["bars"] as $aBar) {
				$sCode .= str_repeat(($aBar["drawBar"]) ? 1 : 0, $aBar["width"]);
			}
			return $sCode;
		}
	}
}
?>