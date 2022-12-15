<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# files
https://hytcom.net/nogal/docs/objects/files.md
*/
namespace nogal;
class nglFiles extends nglFeeder implements inglFeeder {

	private $sSandBox;

	final public function __init__($mArguments=null) {
		$this->sSandBox = (\defined("NGL_SANDBOX")) ?  \realpath(NGL_SANDBOX) : \realpath(NGL_PATH_GARDEN);
	}

	public function absPath($sPath, $sSlash=NGL_DIR_SLASH) {
		$sStart = (!empty($sPath) && $sPath[0]==$sSlash) ? $sSlash : "";
		if(\strtoupper(\substr(PHP_OS, 0, 3))=="WIN") {
			if(\strpos($sPath, ":")) {
				list($sStart, $sPath) = \explode(":", $sPath, 2);
				$sStart .= ":".$sSlash;
			}
		}

		$sPath = \str_replace(["/", "\\"], $sSlash, $sPath);
		$aPath = \array_filter(\explode($sSlash, $sPath), "strlen");

		$aAbsolute = [];
		foreach($aPath as $sDir) {
			if($sDir==".") { continue; }
			if($sDir=="..") {
				\array_pop($aAbsolute);
			} else {
				$aAbsolute[] = $sDir;
			}
		}

		$sAbsolutePath = $sStart.\implode($sSlash, $aAbsolute);
		return $sAbsolutePath;
	}

	public function basePaths($sPath1, $sPath2, $sSlash=NGL_DIR_SLASH) {
		$sPath1 = self::call()->clearPath($sPath1, false, $sSlash);
		$sPath2 = self::call()->clearPath($sPath2, false, $sSlash);
		return self::call()->strCommon($sPath1, $sPath2);
	}

	public function copyr($sSource, $sDestine, $sMask="*", $bRecursive=true, $bIncludeHidden=true, $mCase=false, $bLog=false) {
		$aLog = [];
		$nCopied = 0;

		$sSource = self::call()->sandboxPath($sSource);
		$sDestine = self::call()->sandboxPath($sDestine);
		if(!\is_dir($sDestine)) {
			if(@\mkdir($sDestine)) {
				@\chmod($sDestine, NGL_CHMOD_FOLDER);
				$sLog = "mkdir\t".$sDestine."\n";
			} else {
				self::errorMessage($this->object, 1003, \dirname($sDestine));
			}
		}

		$sMode = ($bIncludeHidden) ? "signed-h" : "signed";
		$aFiles = $this->ls($sSource, $sMask, "signed", $bRecursive);
		if($sMask!=="*") {
			$aDirs = $aTmpDirs = [];
			foreach($aFiles as $sFile) {
				if(!empty($sFile) && $sFile[0]!="*") {
					$sDirname = \dirname($sFile);
					$aTmpDirs[$sDirname] = true;
				}
			}

			$aTmpDirs =\array_keys($aTmpDirs);
			foreach($aTmpDirs as $sDirname) {
				$sPath = "*".\strtok($sDirname, NGL_DIR_SLASH);
				while($sTok = \strtok(NGL_DIR_SLASH)) {
					$sPath .= NGL_DIR_SLASH.$sTok;
					$aDirs[] = $sPath;
				}
			}

			$aFiles = \array_merge($aDirs, $aFiles);
		}

		if($mCase!==false) { $mCase = \strtolower($mCase); }
		$nSource = \strlen($sSource);
		foreach($aFiles as $sFile) {
			$nDir = (!empty($sFile) && $sFile[0]=="*") ? 1 : 0;
			$sFile = \substr($sFile, $nSource+$nDir);

			$sSourceFile = self::call()->clearPath($sSource.NGL_DIR_SLASH.$sFile);
			$sDestineFile = self::call()->clearPath($sDestine.NGL_DIR_SLASH.$sFile);
			if($mCase=="lower") {
				$sDestineFile = \strtolower($sDestineFile);
			} else if($mCase=="upper") {
				$sDestineFile = \strtoupper($sDestineFile);
			} else if($mCase=="secure") {
				$sDestineFile = self::call()->secureName($sDestineFile, NGL_DIR_SLASH.".:");
			}

			if(!$nDir) {
				\copy($sSourceFile, $sDestineFile);
				@\chmod($sDestineFile, NGL_CHMOD_FILE);
				$sLog = "copy\t".$sSourceFile." => ".$sDestineFile."\n";
			} else {
				if(\is_dir($sDestineFile)) { continue; }
				if(@\mkdir($sDestineFile)) {
					@\chmod($sDestineFile, NGL_CHMOD_FOLDER);
					$sLog = "mkdir\t".$sDestineFile."\n";
				} else {
					self::errorMessage($this->object, 1003, \dirname($sDestineFile));
				}
			}

			$nCopied++;
			if($bLog) { $aLog[] = $sLog; }
		}

		$aReport = [];
		$aReport[]	= $nCopied;
		if($bLog) { $aReport[] = \implode($aLog); }

		return $aReport;
	}

	public function ls($sPath=".", $mMask=null, $sMode="single", $bRecursive=false, $sChildren="_children", $bIni=true) {
		if(\strpos($sMode, "-")) {
			$sMode = \strstr($sMode, "-", true);
			$bHiddenFiles = true;
		}
		$sMode = \strtolower($sMode);

		if($bIni) {
			$sPath = \str_replace("*", "", $sPath);
			$sPath = self::call()->clearPath($sPath);
		}

		if($mMask) {
			if(\is_array($mMask)) {
				$aMatch = [];
				foreach($mMask as $sMask) {
					$aMatch[] = \preg_quote($sMask);
				}
				$sMask = "/(".\implode("|", $aMatch).")/i";
			} else {
				$sMask = "/".\preg_quote($mMask)."/i";
			}

			$sMask = \str_replace("\*", ".*", $sMask);
		} else {
			$sMask = $mMask;
		}

		$sPath .= NGL_DIR_SLASH.((isset($bHiddenFiles)) ? "{,.}[!.,!..]*" : "*");
		$sPath = self::call()->sandboxPath($sPath);
		$aPath = \glob($sPath, GLOB_BRACE);

		$aDirs  = [];
		$file = self::call("file")->extend_info(true);
		foreach($aPath as $sFile) {
			$bDir = is_dir($sFile);
			if($sMode=="info") {
				$file->load($sFile);
				if(!$bDir) {
					if(!$sMask || ($sMask && \preg_match($sMask, $sFile))) {
						$vTree = $file->fileinfo();
						$aDirs[$vTree["basename"]] = $vTree;
					}
				} else {
					$vTree = $file->fileinfo();
					if($bRecursive) {
						$aDirs[$vTree["basename"]] = $vTree;
						$aDirs[$vTree["basename"]][$sChildren] = $this->ls($sFile, $mMask, $sMode, $bRecursive, $sChildren, false);
					} else if(!$sMask || ($sMask && \preg_match($sMask, $sFile))) {
						$aDirs[$vTree["basename"]] = $vTree;
					}

				}
			} else {
				if(!$sMask || ($sMask && \preg_match($sMask, $sFile))) {
					$sFilename = ($sMode=="signed" && $bDir) ? "*".$sFile : $sFile;
					$aDirs = \array_merge($aDirs, [$sFilename]);
				}

				if($bDir && $bRecursive) {
					$aDirs = \array_merge($aDirs, $this->ls($sFile, $mMask, $sMode, $bRecursive, $sChildren, false));
				}
			}
		}

		return $aDirs;
	}

	public function lsprint($sPath=".", $mMask=null, $sChildren="_children") {
		$aLs = $this->ls($sPath, $mMask, "info", true, $sChildren);
		$aList = self::call()->treeWalk($aLs, function($aFile, $nLevel, $bFirst, $bLast) {
				$sOutput  = "";
				$sOutput .= ($nLevel) ? \str_repeat("│   ", $nLevel) : "";
				$sOutput .= ($bLast) ? "└─── " : "├─── ";
				$sOutput .= (($aFile["type"]=="dir") ? $aFile["basename"]."/" : $aFile["basename"]);
				$sOutput .= "\n";
				return $sOutput;
			}
		);

		return implode($aList);
	}

	public function maxUploadSize() {
		$nUpload 	= \ini_get("upload_max_filesize");
		$nUpload 	= self::call()->strSizeDecode($nUpload);
		$nPost 		= \ini_get("post_max_size");
		$nPost 		= self::call()->strSizeDecode($nPost);
		$nMemory 	= \ini_get("memory_limit");
		$nMemory 	= self::call()->strSizeDecode($nMemory);
		return \min($nUpload, $nPost, $nMemory);
	}

	public function mkdirr($sPath, $bForce=false) {
		$sPath = self::call()->sandboxPath($sPath);
		if(!$bForce) {
			if(!\is_dir($sPath)) {
				if(!@\mkdir($sPath, 0777, true)) {
					return self::errorMessage($this->object, 1001, $sPath);
				}
				@\chmod($sPath, NGL_CHMOD_FOLDER);
			}
		} else {
			if(!@\mkdir($sPath)) {
				$aPath = \explode(NGL_DIR_SLASH, $sPath);
				$sDirname = \array_pop($aPath);
				$sDirPath = \implode(NGL_DIR_SLASH, $aPath);

				$sTestDir = self::call()->unique(16);
				if(!@\mkdir($sDirPath.NGL_DIR_SLASH.$sTestDir)) {
					return self::errorMessage($this->object, 1001, $sPath);
				} else {
					\rmdir($sDirPath.NGL_DIR_SLASH.$sTestDir);
				}

				$x = 1;
				while(1) {
					$sDirToCreateForced = $sDirname."_".$x;
					if(@\mkdir($sDirPath.NGL_DIR_SLASH.$sDirToCreateForced)) {
						@\chmod($sDirPath.NGL_DIR_SLASH.$sDirToCreateForced, NGL_CHMOD_FOLDER);
						break;
					}
					$x++;
				}
			}
			@\chmod($sPath, NGL_CHMOD_FOLDER);
		}

		return true;
	}

	private function RebuildFILES($aFiles, $bTop=true) {
		$vFiles = [];
		foreach($aFiles as $sName => $aFile){
			$sSubName = ($bTop) ? $aFile["name"] : $sName;
			if(\is_array($sSubName)){
				foreach(\array_keys($sSubName) as $nKey){
					$vFiles[$sName][$nKey] = [
						"name"     => $aFile["name"][$nKey],
						"type"     => $aFile["type"][$nKey],
						"tmp_name" => $aFile["tmp_name"][$nKey],
						"error"    => $aFile["error"][$nKey],
						"size"     => $aFile["size"][$nKey],
					];
					$vFiles[$sName] = $this->RebuildFILES($vFiles[$sName], false);
				}
			} else {
				if($bTop) {
					$vFiles[$sName] = [$aFile];
				} else {
					$vFiles[$sName] = $aFile;
				}
			}
		}

		return $vFiles;
	}

	public function unlinkr($sSource, $sMask="*", $bRecursive=true, $bIncludeHidden=false, $bLog=false) {
		$sMode = ($bIncludeHidden) ? "signed-h" : "signed";
		$aFiles = $this->ls($sSource, $sMask, $sMode, $bRecursive);
		$aFiles = \array_reverse($aFiles);

		$sEnd = \substr($sSource, -1, 1);
		if($sMask=="*" && $sEnd!="/" && $sEnd!="\\") { $aFiles[] = "*".$sSource; }

		$aLog = [];
		$nDeleted = 0;

		foreach($aFiles as $sFile) {
			$nDir = (!empty($sFile) && $sFile[0]=="*") ? 1 : 0;
			$sFile = \substr($sFile, $nDir);

			if(\file_exists($sFile)) {
				if(!$nDir) {
					@\unlink($sFile);
				} else {
					@\rmdir($sFile);
				}

				$nDeleted++;
				if($bLog) { $aLog[] = "delete \t".$sFile."\n"; }
			} else {
				if($bLog) { $aLog[] = "error \t".$sFile."\n"; }
			}
		}

		$aReport = [];
		$aReport[]	= $nDeleted;
		if($bLog) { $aReport["log"] = \implode($aLog); }

		return $aReport;
	}

	public function upload($mDestine, $bOriginalName=false, $aExtensions=null, $nLimit=null) {
		$vUploads = ["errors"=>0, "report"=>[], "files"=>[]];

		if(\count($_FILES)) {
			if($nLimit===null) { $nLimit = $this->maxUploadSize(); }

			$_FILES = $this->RebuildFILES($_FILES);
			foreach($_FILES as $mIndex => $vFiles) {
				foreach($vFiles as $nIndex => $vFile) {
					$sIndex = $mIndex."_".$nIndex;
					switch($vFile["error"]) {
						case UPLOAD_ERR_OK:
							break;

						case UPLOAD_ERR_INI_SIZE:
							$vUploads["errors"]++;
							$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1010);
							break;

						case UPLOAD_ERR_FORM_SIZE:
							$vUploads["errors"]++;
							$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1011);
							break;

						case UPLOAD_ERR_PARTIAL:
							$vUploads["errors"]++;
							$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1012);

						case UPLOAD_ERR_NO_FILE:
							$vUploads["errors"]++;
							$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1013);
							break;

						case UPLOAD_ERR_NO_TMP_DIR:
							$vUploads["errors"]++;
							$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1014);
							break;

						case UPLOAD_ERR_CANT_WRITE:
							$vUploads["errors"]++;
							$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1015);
							break;

						default:
							$vUploads["errors"]++;
							$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1016);
					}

					if($vFile["size"] > $nLimit) {
						$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1017);
					}

					$vInfo = \pathinfo($vFile["name"]);
					$vInfo = \pathinfo($vFile["name"]);
					if($aExtensions!==null && !\in_array($vInfo["extension"], $aExtensions)) {
						$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1019);
						continue;
					}

					$sFilename = ($bOriginalName) ? $vFile["name"] : self::call()->unique(32).".".$vInfo["extension"];
					$sDestine = (\is_array($mDestine)) ? $mDestine[$mIndex] : $mDestine;
					$sDestineFilePath = self::call()->sandboxPath($sDestine.NGL_DIR_SLASH.$sFilename);
					$bIsImage = self::call()->isImage($vFile["tmp_name"]);
					if(\move_uploaded_file($vFile["tmp_name"], $sDestineFilePath)) {
						@\chmod($sDestineFilePath, NGL_CHMOD_FILE);
						unset($vFile["error"], $vFile["tmp_name"]);
						@\chmod($sDestineFilePath, NGL_CHMOD_FILE);
						$vFile["path"]		= $sDestineFilePath;
						$vFile["filename"]	= $vFile["name"];
						$vFile["realname"]	= $sFilename;
						$vFile["extension"]	= $vInfo["extension"];
						$vFile["mimetype"]	= $vFile["type"];
						$vFile["image"]		= $bIsImage;
						$vFile["field"]		= $mIndex;

						unset($vFile["error"], $vFile["tmp_name"], $vFile["name"], $vFile["type"]);

						$vUploads["files"][$sIndex] 	= $vFile;
						$vUploads["report"][$sIndex]	= "OK";
					} else {
						$vUploads["errors"]++;
						$vUploads["report"][$sIndex] = self::errorMessage($this->object, 1018, $vFile["tmp_name"]." => ".$sDestineFilePath);
					}
				}
			}
		}

		return $vUploads;
	}
}

?>