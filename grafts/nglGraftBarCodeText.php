<?php
namespace Picqer\Barcode {
	class BarcodeGeneratorText extends BarcodeGenerator {
		public function getBarcode($sCode, $sType) {
			$barcode = $this->getBarcodeData($sCode, $sType,2,100);
			$sCode = "";
			foreach($barcode->getBars() as $bar) {
				$sCode .= \str_repeat($bar->isBar() ? 1 : 0, $bar->getWidth());
			}
			return $sCode;
		}
	}
}
?>