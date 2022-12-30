<?php

namespace nogal;
$this->TutorCaller(self::requirer());

class tutorMaster extends nglTutor {

	protected function init($mArguments=null) {
		$this->Lockable();
	}

	/*
		$aUploads = $this->master->unlock()->run("upload", array(
			"image" => array("path" => NGL_PATH_PUBLIC.NGL_DIR_SLASH."files"), // upload simple
			"image2" => array(
				"path" => NGL_PATH_PUBLIC.NGL_DIR_SLASH."files", // upload original
				"copies" => array(
					array("path" => NGL_PATH_PUBLIC.NGL_DIR_SLASH."files/thumb"), // copia del original
					array("path" => NGL_PATH_PUBLIC.NGL_DIR_SLASH."files/thumb", "img-size"=>800), // copia de 800 (el lado mas grande)
					array("path" => NGL_PATH_PUBLIC.NGL_DIR_SLASH."files/thumb", "img-size"=>200, "img-bgcolor"=>"#FFFFFF") // copia de 200(el lado mas grande) x 200 relleno con blanco
				)
			)
		));
	*/
	protected function upload($aArguments=[]) {
		if(!\count($aArguments)) { return false; }
		$this->alvin();

		$aPaths = [];
		foreach($aArguments as $sFile =>$aFile) {
			if(isset($aFile["path"])) {
				if(self::call("files")->mkdirr($aFile["path"])) {
					$aPaths[$sFile] = $aFile["path"];
				}
			} else {
				unset($aArguments[$sFile]);
			}
		}

		$aUploads = self::call("files")->upload($aPaths);
		if(count($aUploads["files"])) {
			foreach($aUploads["files"] as $sIndex=>$aFile) {
				$aUploads["files"][$sIndex]["image"] = ($aFile["image"]) ? "1" : "0";
				if(isset($aArguments[$aFile["field"]]["copies"])) {
					foreach($aArguments[$aFile["field"]]["copies"] as $aCopy) {
						if(isset($aCopy["path"]) && self::call("files")->mkdirr($aCopy["path"])) {
							if($aFile["image"] && isset($aCopy["img-size"])) {
								$img = self::call("image")->load($aFile["path"])->quality(100);
								$img->resize($aCopy["img-size"], "max");
								if(isset($aCopy["img-bgcolor"])) { $img->canvas($aCopy["img-size"], $aCopy["img-size"], $aCopy["img-bgcolor"]); }
								$img->write($aCopy["path"].NGL_DIR_SLASH.$aFile["realname"]);
							} else {
								copy($aFile["path"], $aCopy["path"].NGL_DIR_SLASH.$aFile["realname"]);
							}
						}
					}
				}
			}

			return $aUploads["files"];
		}

		return false;
	}

	protected function mail($aArguments=[]) {
		global $ENV;
		if(empty($aArguments["to"])) { return false; }
		$sTo 		= $aArguments["to"];
		$sSubject	= !empty($aArguments["subject"]) ? $aArguments["subject"] : "";
		$sMessage	= !empty($aArguments["message"]) ? $aArguments["message"] : "";
		$sCC		= !empty($aArguments["cc"]) ? $aArguments["cc"] : null;
		$sCCO		= !empty($aArguments["cco"]) ? $aArguments["cco"] : null;

		$mail = self::call("mail")
			->server("smtp")
			->host($ENV["mail"]["host"])
			->secure($ENV["mail"]["protocol"])
			->port($ENV["mail"]["port"])
			->user($ENV["mail"]["user"])
			->pass($ENV["mail"]["pass"])
			->charset($ENV["mail"]["charset"])
			->connect()
			->login()
			->from($ENV["mail"]["name"])
			->cc($sCC)
			->bcc($sCCO)
			->subject($sSubject)
			->message($sMessage)
		;
		$mail->send($sTo);
		// echo $mail->log;
	}
}

?>
