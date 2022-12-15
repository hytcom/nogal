<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# ftp
https://hytcom.net/nogal/docs/objects/ftp.md
*/
namespace nogal;
class nglFTP extends nglBranch implements inglBranch {

	private $ftp;
	private $sSlash;
	private $aLastDir;

	final protected function __declareArguments__() {
		$vArguments						= [];
		$vArguments["filepath"]			= ['(string)$mValue', null];
		$vArguments["force_create"]		= ['self::call()->isTrue($mValue)', false];
		$vArguments["host"]				= ['(string)$mValue', "127.0.0.1"];
		$vArguments["local"]			= ['(string)$mValue', null];
		$vArguments["ls_mode"]			= ['(string)$mValue', "single"];
		$vArguments["mask"]				= ['(string)$mValue'];
		$vArguments["newname"]			= ['(string)$mValue', null];
		$vArguments["pass"]				= ['(string)$mValue'];
		$vArguments["passive_mode"]		= ['self::call()->isTrue($mValue)', true];
		$vArguments["port"]				= ['(int)$mValue', 21];
		$vArguments["recursive"]		= ['self::call()->isTrue($mValue)', false];
		$vArguments["transfer"]			= ['$mValue', FTP_BINARY];
		$vArguments["user"]				= ['(string)$mValue', "anonymous"];

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes					= [];
		$vAttributes["system"]			= null;
		$vAttributes["windows"]			= null;
		$vAttributes["list"]			= null;
		$vAttributes["tree"]			= null;
		$vAttributes["log"]				= null;

		return $vAttributes;
	}

	final protected function __declareVariables__() {
		$this->aLastDir = [];
	}

	final public function __init__() {
	}

	public function cd() {
		list($sPath, $bForce) = $this->getarguments("filepath,force_create", \func_get_args());

		$sPath = self::call()->clearPath($sPath, false, $this->sSlash);
		$aPath = \explode($this->sSlash, $sPath);
		for($x=0; $x<\count($aPath); $x++) {
			if($aPath[$x]!="") {
				if(!@\ftp_chdir($this->ftp, $aPath[$x])) {
					if($bForce) {
						if(!$this->makedir($aPath[$x])) { return false; }
						if(!\ftp_chdir($this->ftp, $aPath[$x])) {
							$this->Logger(self::errorMessage($this->object, 1003, $aPath[$x]));
							return false;
						}
					} else {
						$this->Logger(self::errorMessage($this->object, 1003, $aPath[$x]));
						return false;
					}
				}

				$this->Logger("CHDIR ".$aPath[$x]);
			}
		}

		return $this;
	}

	public function connect() {
		list($sHost, $nPort) = $this->getarguments("host,port", \func_get_args());

		if(!$ftp = \ftp_connect($sHost, (int)$nPort)) {
			$this->Logger(self::errorMessage($this->object, 1001, $sHost));
		} else {
			$this->Logger("CONNECTED TO ".$sHost.":".$nPort);
			$this->ftp = $ftp;
			return $this;
		}

		return false;
	}

	public function curdir() {
		return \ftp_pwd($this->ftp);
	}

	public function delete() {
		list($sPath) = $this->getarguments("filepath", \func_get_args());

		$bIsDir = ($this->cd($sPath)===false) ? false : true;
		if($bIsDir) {
			$aSource = $this->ls(null, null, "info", true);
			$vDelete = self::call()->treeWalk($aSource,
				function($aNode, $nLevel, $bFirst, $bLast) {
					if($aNode["type"]=="file") {
						if(!@\ftp_delete($this->ftp, $aNode["path"])) {
							$this->Logger(self::errorMessage($this->object, 1008, $aNode["path"]));
							return false;
						}
						return true;
					}
				}, null, [
					"nodeOpen" => function($aNode) {
						if($aNode["type"]=="dir") { $this->aLastDir[] = $aNode["path"]; }
						return true;
					},
					"branchClose" => function() {
						$sLastDir = array_pop($this->aLastDir);
						if($sLastDir!==null && !@ftp_rmdir($this->ftp, $sLastDir)) {
							$this->Logger(self::errorMessage($this->object, 1008, $sLastDir));
							return false;
						}
						return true;
					}
				]
			);

			foreach($vDelete as $bDelete) { if($bDelete===false) { return false; } }
			$this->cd("..");
			return @\ftp_rmdir($this->ftp, $sPath);
		} else {
			if(@\ftp_delete($this->ftp, $sPath)) {
				$this->Logger(self::errorMessage($this->object, 1008));
				return false;
			}
			return true;
		}
	}

	public function download() {
		list($sPath, $sLocalPath, $nTransfer) = $this->getarguments("filepath,local,transfer", \func_get_args());

		$sSource = \basename($sPath);
		$sDestination = ($sLocalPath!==null) ? $sLocalPath : $sSource;

		$vList = $this->ls(null,null,"info");
		if($vList[$sSource]["type"]=="dir") {
			$aSource = $this->ls($sSource, null, "info", true);
			if(@!\chdir($sDestination)) {
				if(@!\mkdir($sDestination, 0755)) {
					$this->Logger(self::errorMessage($this->object, 1003));
				} else {
					\chdir($sDestination);
				}
			}

			$sMainDir = $vList[$sSource]["basename"];
			if(@!\chdir($sMainDir)) {
				if(@!\mkdir($sMainDir, 0755)) {
					$this->Logger(self::errorMessage($this->object, 1003));
				} else {
					\chdir($sMainDir);
				}
			}

			$vDownloads = self::call()->treeWalk($aSource, function($aNode, $nLevel, $bFirst, $bLast) use ($nTransfer) {
					return $this->DownloadTree($aNode, $nTransfer);
				}, null, array("branchClose"=>function() { \chdir(".."); return true; })
			);
			\chdir(\getcwd());

			foreach($vDownloads as $bDownload) {
				if($bDownload===false) { return false; }
			}
		} else {
			if(!@\ftp_get($this->ftp, $sDestination, $sSource, $nTransfer)) {
				$this->Logger(self::errorMessage($this->object, 1005));
				return false;
			}
			$this->Logger("DOWNLOAD ".$sSource." -> ".$sDestination);
		}

		return true;
	}

	private function DownloadTree($vFile, $nTransfer=FTP_ASCII) {
		if($vFile["type"]=="dir") {
			if(@!\chdir($vFile["basename"])) {
				$this->Logger(self::errorMessage($this->object, 1003, $vFile["basename"]));
				if(@!\mkdir($vFile["basename"], 0755)) {
					$this->Logger(self::errorMessage($this->object, 1004, $vFile["basename"]));
					return false;
				} else {
					\chdir($vFile["basename"]);
				}
			}
		} else {
			$sSource = $vFile["path"];
			$sDestination = $vFile["basename"];
			if(!@\ftp_get($this->ftp, $sDestination, $sSource, $nTransfer)) {
				return false;
			}
			$this->Logger("DOWNLOAD ".$sSource." -> ".$sDestination);
		}

		return true;
	}

	private function GetChmod($sCHMOD) {
		$vTrans["-"] = "0";
		$vTrans["r"] = "4";
		$vTrans["w"] = "2";
		$vTrans["x"] = "1";

		$sCHMOD = \strtolower($sCHMOD);
		$sCHMOD = \substr(\strtr($sCHMOD, $vTrans), 1);
		$aCHMOD = \str_split($sCHMOD, 3);

		$nCHMOD = \array_sum(\str_split($aCHMOD[0])) . \array_sum(\str_split($aCHMOD[1])) . \array_sum(\str_split($aCHMOD[2]));
		return $nCHMOD;
	}

	private function GetTimestamp($sYear, $sMonth, $sDay) {
		$nMonth = \date("n", \strtotime($sMonth));
		$nToday = \date("n");

		if(\strpos($sYear,":")===false) {
			$nTimestamp	= \strtotime($sDay." ".$sMonth." ".$sYear);
		} else {
			$sNewYear = \date("Y");
			if($nMonth > $nToday) { $sNewYear--; }
			$nTimestamp	= \strtotime($sDay." ".$sMonth." ".$sNewYear." ".$sYear);
		}

		return $nTimestamp;
	}

	public function login() {
		list($sUser, $sPass, $bPassive) = $this->getarguments("user,pass,passive", \func_get_args());
		$sPass = self::passwd($sPass, true);
		if(\ftp_login($this->ftp, $sUser, $sPass)) {
			$nSystem = \ftp_systype($this->ftp);
			$this->Logger("LOGIN OK");
			$this->attribute("system", $nSystem);
			$this->attribute("windows", \preg_match("/windows/i", $nSystem));
			$this->sSlash = ($this->attribute("windows")) ? "\\" : "/";
			$this->passive($bPassive);
			return $this;
		} else {
			$this->Logger(self::errorMessage($this->object, 1002));
		}
		return false;
	}

	private function Logger($sLog) {
		$sHistory = $this->attribute("log");
		$sHistory .= $sLog."\r\n";
		$this->attribute("log", $sHistory);
	}

	public function ls() {
		list($sDirname, $sMask, $sMode, $bRecursive) = $this->getarguments("filepath,mask,ls_mode,recursive", \func_get_args());

		$sCurrentDir = $this->curdir();
		if($sDirname==null) {
			$sDirname = self::call()->clearPath($sCurrentDir, false, $this->sSlash);
		} else {
			if(\strpos($sDirname, $this->sSlash)===false) {
				$sDirname = self::call()->clearPath($sCurrentDir.$this->sSlash.$sDirname, false, $this->sSlash);
			}
		}
		$aList = \ftp_rawlist($this->ftp, $sDirname);
		$this->Logger("LIST (".$sMode.") ".$sDirname);

		$vTree = [];
		if($aList && \count($aList)) {
			$sMode = \strtolower($sMode);
			if($this->attribute("system")=="UNIX") {
				foreach($aList as $sFile) {
					if($sMode=="raw") { $vTree[] = $sFile; continue; }

					// $sFile = trim($sFile);
					$aFileInfo = \preg_split("/[\s]+/", $sFile, 9, PREG_SPLIT_NO_EMPTY);
					if(\is_array($aFileInfo)) {
						$sName = $aFileInfo[8];
						$sFileType = ($aFileInfo[0][0]=="d") ? "dir" : (($aFileInfo[0][0]=="l") ? "link" : "file");
						$sPath = $sDirname.$this->sSlash.$sName;

						$sLink = null;
						if($sFileType=="link") {
							unset($vTree[$sName]);
							$sLink = \substr($sName, \strrpos($sName, " -> ")+1);
							$sName = \substr($sName, 0, \strpos($sName, " -> "));
						}

						if($sMode=="single") {
							$vTree[] = $sPath;
						} else if($sMode=="signed") {
							$sSing = ($sFileType=="dir") ? "*" : "";
							$vTree[] = $sSing.$sPath;
						} else {
							$vTree[$sName]["raw"]		= $sFile;
							$vTree[$sName]["type"]		= $sFileType;
							$vTree[$sName]["path"]		= $sPath;
							$vTree[$sName]["basename"]	= $sName;
							$vTree[$sName]["link"]		= $sLink;

							$aBasename = \explode(".", $vTree[$sName]["basename"]);
							if(\is_array($aBasename) && \count($aBasename)>1 && $sFileType!="dir") {
								$vTree[$sName]["extension"] = \array_pop($aBasename);
								$vTree[$sName]["filename"] = \implode(".", $aBasename);
							} else {
								$vTree[$sName]["extension"] = "";
								$vTree[$sName]["filename"] = $vTree[$sName]["basename"];
							}

							$vTree[$sName]["bytes"]			= $aFileInfo[4];
							$vTree[$sName]["size"]			= self::call()->strSizeEncode($aFileInfo[4]);
							$vTree[$sName]["chmod"]			= $this->GetChmod($aFileInfo[0]);
							$vTree[$sName]["timestamp"]		= $this->GetTimestamp($aFileInfo[7], $aFileInfo[5], $aFileInfo[6]);
							$vTree[$sName]["date"]			= \date("Y-m-d H:i:s", $vTree[$sName]["timestamp"]);
							$vTree[$sName]["mime"] 			= ($vTree[$sName]["type"]=="file") ? self::call()->mimeType($vTree[$sName]["extension"]) : "application/x-unknown-content-type";
							$vTree[$sName]["image"] 		= (\strpos($vTree[$sName]["mime"], "image")===0);
						}

						if($bRecursive && $sFileType=="dir") {
							if(isset($vTree[$sName])) {
								$vTree[$sName]["_children"] = $this->ls($sPath, $sMask, $sMode, true);
							} else {
								$vTree = \array_merge($vTree, $this->ls($sPath, $sMask, $sMode, true));
							}
						}
					}
				}
			} else if($this->attribute("system")=="Windows_NT") {
				foreach($aList as $sFile) {
					if($sMode=="raw") { $vTree[] = $sFile; continue; }

					\preg_match("/([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +([0-9]+|<DIR>) +(.+)/is", $sFile, $aFileInfo);
					if(\is_array($aFileInfo)) {
						if($aFileInfo[3]<70) { $aFileInfo[3]+=2000; } else { $aFileInfo[3]+=1900; }

						$sName = $aFileInfo[8];
						$sFileType = $aFileInfo[7];
						$sPath = $sDirname.$this->sSlash.$sName;

						if($sMode=="single") {
							$vTree[] = $sPath;
						} else if($sMode=="signed") {
							$sSing = ($sFileType=="dir") ? "*" : "";
							$vTree[] = $sSing.$sPath;
						} else {
							$vTree[$sName]["raw"]		= $sFile;
							$vTree[$sName]["type"] 		= ($sFileType=="<DIR>") ? "dir" : "file";
							$vTree[$sName]["path"] 		= $sPath;
							$vTree[$sName]["basename"] 	= $sName;
							$vTree[$sName]["link"]		= null;

							$aBasename = \explode(".", $sName);
							if(\is_array($aBasename) && \count($aBasename)>1 && $sFileType!="<DIR>") {
								$vTree[$sName]["extension"] = \array_pop($aBasename);
								$vTree[$sName]["filename"] = \implode(".", $aBasename);
							} else {
								$vTree[$sName]["extension"] = "";
								$vTree[$sName]["filename"] = $vTree[$sName]["basename"];
							}

							$vTree[$sName]["bytes"] 	= ($sFileType=="<DIR>") ? 0 : $aFileInfo[7];
							$vTree[$sName]["size"] 		= self::call()->strSizeEncode($vTree[$sName]["bytes"]);
							$vTree[$sName]["chmod"]		= null;
							$vTree[$sName]["timestamp"]	= $this->GetTimestamp($aFileInfo[3], $aFileInfo[1], $aFileInfo[2]);
							$vTree[$sName]["date"]		= \date("Y-m-d H:i:s", $vTree[$sName]["timestamp"]);
							$vTree[$sName]["mime"] 		= ($vTree[$sName]["type"]=="file") ? self::call()->mimeType($vTree[$sName]["extension"]) : "application/x-unknown-content-type";
							$vTree[$sName]["image"] 	= (\strpos($vTree[$sName]["mime"], "image")===0);
						}

						if($bRecursive && $sFileType=="dir") {
							if(isset($vTree[$sName])) {
								$vTree[$sName]["_children"] = $this->ls($sPath, $sMask, $sMode, true);
							} else {
								$vTree = \array_merge($vTree, $this->ls($sPath, $sMask, $sMode, true));
							}
						}
					}
				}
			}
		}
		return $vTree;
	}

	public function mkdir() {
		list($sPath, $bForce) = $this->getarguments("filepath,force_create", \func_get_args());

		if(!$bForce) {
			if(!@\ftp_mkdir($this->ftp, $sPath)) {
				$this->Logger(self::errorMessage($this->object, 1004, $sPath));
				return false;
			}
		} else {
			$sTestDir = self::call()->unique(16);
			if(!@\ftp_mkdir($this->ftp, $sTestDir)) {
				$this->Logger(self::errorMessage($this->object, 1004, $sPath));
				return false;
			} else {
				\ftp_rmdir($this->ftp, $sTestDir);
			}

			$x = 1;
			$sDirToCreateForced = $sPath;
			while(1) {
				if(@\ftp_mkdir($this->ftp, $sDirToCreateForced)) {
					$sPath = $sDirToCreateForced;
					break;
				}
				$sDirToCreateForced = $sPath."_".$x;
				$x++;
			}
		}

		$this->Logger("MKDIR ".$sPath);
		return $this;
	}

	public function passive() {
		list($bPassive) = $this->getarguments("passive_mode", \func_get_args());
		\ftp_pasv($this->ftp, $bPassive);
		$this->Logger("PASIVE MODE ".($bPassive ? "ON" : "OFF"));
		return $this;
	}

	public function rename() {
		list($sPath, $sNewName) = $this->getarguments("filepath,newname", \func_get_args());

		$sPath = \basename($sPath);
		$sNewName = \basename($sNewName);

		if($sPath=="" || $sNewName=="") { return false; }
		if(!@\ftp_rename($this->ftp, $sPath, $sNewName)) {
			$this->Logger(self::errorMessage($this->object, 1007));
			return false;
		}

		$this->Logger("REN ".$sPath." => ".$sNewName);
		return $this;
	}

	public function upload() {
		list($sLocalPath, $sPath, $nTransfer) = $this->getarguments("local,filepath,transfer", \func_get_args());

		if(\is_dir($sLocalPath)) {
			$this->cd($sPath);
			$aSource = self::call("files")->ls($sLocalPath, null, "info", true);
			$vUploads = self::call()->treeWalk($aSource, function($aNode, $nLevel, $bFirst, $bLast) use ($nTransfer) {
					return $this->UploadTree($aNode, $nTransfer);
				}, null, ["branchClose"=>function() { $this->cd(".."); return true; }]
			);
			$this->cd("..");
			foreach($vUploads as $bUpload) {
				if($bUpload===false) { return false; }
			}
		} else {
			if(!@\ftp_put($this->ftp, $sPath, $sLocalPath, $nTransfer)) {
				$this->Logger(self::errorMessage($this->object, 1006));
				return false;
			}
			$this->Logger("UPLOAD ".$sLocalPath." -> ".$sPath);
		}

		return true;
	}

	private function UploadTree($vFile, $nTransfer) {
		if($vFile["type"]=="dir") {
			if(!$this->cd($vFile["basename"], true)) { return false; }
		} else {
			$sSource = $vFile["path"];
			$sDestination = $vFile["basename"];
			if(!@\ftp_put($this->ftp, $sDestination, $sSource, $nTransfer)) {
				return false;
			}
			$this->Logger("UPLOAD ".$sSource." -> ".$sDestination);
		}

		return true;
	}
}

?>