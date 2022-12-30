<?php

namespace nogal;

class nutPecan extends nglNut {

	protected function init() {
		$this->SafeMethods(["apigetjson", "getjson","color"]);
	}

	protected function apigetjson($aArguments) {
		return $this->getjson($aArguments[0]["query"]);
	}
	
	protected function getjson($aArguments) {
		$sFormat = !empty($aArguments["format"]) ? \strtolower($aArguments["format"]) : "array";
		if($json = self::call("file")->load($aArguments["url"])) {
			$aJson = \json_decode($json->read(), true);
			if(!empty($aJson[$aArguments["claim"]])) {
				return $sFormat=="array" ? $aJson[$aArguments["claim"]] : self::call("shift")->convert($aJson[$aArguments["claim"]], "array-".$sFormat);
			}
		}
		return $sFormat=="array" ? [] : self::call("shift")->convert([], "array-".$sFormat);
	}

	protected function color($aArguments) {
		if($json = self::call("file")->load("https://cdn.upps.cloud/json/material-design-colors.json")) {
			$aJson = \json_decode($json->read(), true);
			\shuffle($aJson);
			$aColor = array_pop($aJson);
			\shuffle($aColor);
			return array_pop($aColor);
		}
		return "#AB0000";
	}
}

?>