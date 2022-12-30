<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___

# sow
https://hytcom.net/nogal/docs/objects/sow.md
*/
namespace nogal;
class nglSow extends nglFeeder implements inglFeeder {

	private $sSeedBed;

	final public function __init__($mArguments=null) {
		$this->sSeedBed = NGL_PATH_SEEDBED;
	}

	public function seedbed($sPath) {
		if(\is_dir($sPath) && \is_readable($sPath)) {
			$this->sSeedBed = $sPath;
		}
	}

	public function conf($sObjectName, $sKey=null, $sValue=null) {
		if($obj = self::call($sObjectName)) {
			if($sKey!==null) {
				return $obj->__configFileValue__($sKey, $sValue);
			} else {
				return $obj->__configFile__();
			}
		}
	}

	public function skels() {
		$aSkels = ["structures"=>[], "components"=>[]];
		if(\is_dir($this->sSeedBed) && \is_readable($this->sSeedBed)) {
			foreach(\glob($this->sSeedBed.NGL_DIR_SLASH."structures".NGL_DIR_SLASH."*") as $sStructure) {
				$aSkels["structures"][] = \basename($sStructure);
			}
			$aSkels["components"] = self::call()->parseConfigFile($this->sSeedBed.NGL_DIR_SLASH."components/.index");
		}
		return $aSkels;
	}

	public function skel($sName, $sNewName=null) {
		if($sNewName===null) { $sNewName = $sName; }
		$sNewName = \strtolower(\preg_replace("/[^a-zA-Z0-9\_\-\/\.]+/", "", $sNewName));
		$sName = \strtolower(\preg_replace("/[^a-z0-9\_\-\.]+/", "", $sName));
		$aComponents = self::call()->parseConfigFile($this->sSeedBed.NGL_DIR_SLASH."components/.index");

		if(!empty($aComponents[$sName])) {
			$sFolder = "components";
			$sDestine = \str_replace("<NEWNAME>", $sNewName, $aComponents[$sName]);
			$sName .= ".sample";
		} else {
			$sFolder = "structures";
			$sDestine = $sNewName!="." ? $sNewName : \getcwd();
		}

		$sSource = $this->sSeedBed.NGL_DIR_SLASH.$sFolder.NGL_DIR_SLASH.$sName;
		if(\file_exists($sSource)) {
			if(\is_dir($sSource)) { $sNewName = null; }
			if($sNewName===null) {
				if(!\file_exists($sDestine)) {
					if(!@\mkdir($sDestine, NGL_CHMOD_FOLDER)) {
						self::errorMessage("sow", 1001, $sDestine, "die");
						return false;
					}
				}
				$source = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sSource, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
				foreach($source as $item) {
					$sDestinePath = $sDestine.NGL_DIR_SLASH.$source->getSubPathname();
					if(!\file_exists($sDestinePath)) {
						if($item->isDir()) {
							\mkdir($sDestinePath, NGL_CHMOD_FOLDER);
						} else {
							\copy($item, $sDestinePath);
							\chmod($sDestinePath, NGL_CHMOD_FILE);
						}
					}
				}
				if(\file_exists($sDestine.NGL_DIR_SLASH."aftersow")) {
					include_once($sDestine.NGL_DIR_SLASH."aftersow");
				}
			} else {
				if(!\file_exists($sDestine) && NGL_PATH_GARDEN!=NGL_PATH_FRAMEWORK) {
					if(!\is_dir(NGL_PATH_NUTS)) { \mkdir(NGL_PATH_NUTS, NGL_CHMOD_FOLDER); }
					if(!\is_dir(NGL_PATH_TUTORS)) { \mkdir(NGL_PATH_TUTORS, NGL_CHMOD_FOLDER); }
					$sCamelName = \ucwords($sNewName);
					$sBuffer = \file_get_contents($sSource);
					$sBuffer = \str_replace(
						["<{=LOWERNAME=}>", "<{=UPPERNAME=}>", "<{=CAMELNAME=}>", "<{=LOWERCAMELNAME=}>"],
						[$sNewName, \strtoupper($sNewName), $sCamelName, \lcfirst($sCamelName)],
						$sBuffer
					);
					\file_put_contents($sDestine, $sBuffer);
					\chmod($sDestine, NGL_CHMOD_FILE);
					return true;
				}
				return false;
			}
			return true;
		}
		return false;
	}
}

?>