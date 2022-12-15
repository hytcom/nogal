<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
*/
namespace nogal;
class nglScion extends nglBranch implements inglBranch {

	public function __init__() {
	}

	// grafts composer autoload
	final public function __vendor__() {
		if(\file_exists(self::path("grafts").NGL_DIR_SLASH."composer".NGL_DIR_SLASH."vendor".NGL_DIR_SLASH."autoload.php")) {
			require_once(self::path("grafts").NGL_DIR_SLASH."composer".NGL_DIR_SLASH."vendor".NGL_DIR_SLASH."autoload.php");
		}
	}

	// install package
	final public function installPackage($sPackage, $sVersion) {
		$this->__errorMode__("die");
		$sComposeDir = self::path("grafts").NGL_DIR_SLASH."composer";
		$sComposeFile = $sComposeDir.NGL_DIR_SLASH."composer.json";
		$aComposer = false;
		if(\is_writable($sComposeDir)) {
			if(\is_writable($sComposeFile)) {
				$aComposer = self::call()->isJSON(\file_get_contents($sComposeFile), "array");
			}
		} else {
			self::errorMessage($this->object, 1001, $sPackage.":".$sVersion);
		}

		if($aComposer===false) { $aComposer = ["require"=>[]]; }
		$aComposer["require"][$sPackage] = $sVersion;

		\file_put_contents($sComposeFile, \json_encode($aComposer, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
		self::errorMessage($this->object, 1000);
	}
}

?>