<?php

namespace nogal;
$this->TutorCaller(self::requirer());

class tutorUploads extends nglTutor {

	private $db;
	private $owl;
	
	protected function init($aArguments=[]) {
		$this->db = self::call("mysql");
		$this->owl = self::call("owl");
		$this->owl->connect($this->db);
		$this->owl->select("uploads");
		$this->debuggable = true; // el tutor admite debug
		// $this->aNulls = []; // listar los campos que en caso de estar vacios deberán guardarse como NULL
		// $this->aEmpty = []; // listar los campos que en caso de ser NULL deben guardarse como vacios
		// $this->aZeros = []; // listar los campos que en caso de ser NULL o vacios, deben guardarse como zeros
		$this->master = self::call("tutor.master");
	}

	protected function insert($aArguments=[]) {
		if(!count($aArguments) || empty($_FILES)) { return false; }
		$aArguments = $this->Sanitize($aArguments);
		// $this->alvin();

		if(empty($aArguments[file])) {
			$table = $this->db->query("SELECT `name` FROM `__ngl_owl_structure__` WHERE `code` = '".substr($aArguments["parent"],0,12)."'");
			if(!$table->rows()) { return false; }
			$sFolder = $table->get("name");
			$aFile = [
				"file" => [
					"path" => NGL_PATH_PUBLIC."/files/".$sFolder
					,"copies" => [["path" => NGL_PATH_PUBLIC."/files/".$sFolder."/thumbs", "img-size"=>"200", "img-bgcolor"=>"#FFFFFF"]]
				]
			];
		} else {
			$aFile = [$aArguments["field"] => ["path" => $aArguments["path"]]];
			if(!empty($aArguments["copies"])) { $aFiles[$aArguments["field"]]["copies"] = $aArguments["copies"]; }
			unset($aArguments["field"], $aArguments["path"], $aArguments["copies"]);
		}

		$aUploads = $this->master->unlock()->run("upload", $aFile);
		reset($aUploads);
		$insert = $this->owl->insert(array_merge($aArguments, current($aUploads)));

		// tags
		if(!empty($aArguments["tags"])) {
			$this->master->unlock()->run("tags", ["entity"=>$this->owl->last_imya, "tags"=>$aArguments["tags"]]);
		}

		return ($insert!==false) ? $insert : $this->owl->validate;
	}

	protected function update($aArguments=[]) {
		if(!count($aArguments)) { return false; }
		$aArguments = $this->Sanitize($aArguments);
		// $this->alvin();

		$update = $this->owl->update($aArguments);

		// tags
		if(!empty($aArguments["tags"])) {
			$this->master->unlock()->run("tags", ["entity"=>$aArguments["imya"], "tags"=>$aArguments["tags"]]);
		}

		return ($update!==false) ? $update : $this->owl->validate;
	}

	protected function delete($aArguments=[]) {
		if(!count($aArguments)) { return false; }
		$this->alvin();
		$nDelete = $this->owl->delete($aArguments);
		if($nDelete===false) {
			return $this->owl->log;
		} else {
			return $nDelete;
		}
	}
}

?>