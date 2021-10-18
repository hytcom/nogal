<?php

namespace nogal;
$this->TutorCaller(self::requirer());

class tutor<{=CAMELNAME=}> extends nglTutor {

	private $db;
	private $owl;
	
	protected function init($aArguments=[]) {
		$this->db = self::call("mysql");
		$this->owl = self::call("owl");
		$this->owl->connect($this->db);
		$this->owl->select("<{=LOWERNAME=}>");
		$this->debuggable = false; // el tutor admite debug
		$this->aNulls = [true]; // listar los campos que en caso de estar vacios deberán guardarse como NULL. Usar TRUE para indicar TODOS
		// $this->aEmpty = []; // listar los campos que en caso de ser NULL deben guardarse como vacios
		// $this->aZeros = []; // listar los campos que en caso de ser NULL o vacios, deben guardarse como zeros
		// $this->master = self::call("tutor.master");
	}

	protected function insert($aArguments=[]) {
		if(!count($aArguments)) { return false; }
		// $this->alvin("<{=LOWERNAME=}>.insert");
		$aArguments = $this->Sanitize($aArguments);

		// owl insert
		$insert = $this->owl->insert($aArguments);
		$sImya = $this->owl->last_imya;

		/* adjuntos ------------------------------------------------------------
			field = nombre del campo en el formulario HTML
			path = ruta donde se alojará el adjunto
			copies: solo aplica para JPG y PNG
				path = ruta donde se alojará la copia
				img-size = tamaño de la copia INTxINT ó INT que en combinación con img-bgcolor creará una copia cuadrada de INT de lado
				img-bgcolor = color de relleno cuanto img-size sea un INT
			tags = etiquetas asociadas al adjunto

			ejecutar self::call("tutor.uploads")->run("insert", []); por cada adjunto en el formulario
		----------------------------------------------------------------------*/
		// if(!empty($aArguments["_FILES"])) {
		// 	self::call("tutor.uploads")->run("insert", [
		// 		"parent" => $sImya,
		// 		"field"  => "INPUTFILENAME",
		// 		"path" => NGL_PATH_PUBLIC."/files/<{=LOWERNAME=}>",
		// 		"copies" => [
		// 			["path" => NGL_PATH_PUBLIC."/files/<{=LOWERNAME=}>/thumbs", "img-size"=>"200", "img-bgcolor"=>"#FFFFFF"],
		// 			["path" => NGL_PATH_PUBLIC."/files/<{=LOWERNAME=}>/thumbs400", "img-size"=>"400x500", "img-bgcolor"=>"#FFFFFF"]
		// 		]
		// 	]);
		// }

		return ($insert!==false) ? $insert : $this->owl->validate;
	}

	protected function update($aArguments=[]) {
		if(!count($aArguments)) { return false; }
		// $this->alvin("<{=LOWERNAME=}>.update");
		$aArguments = $this->Sanitize($aArguments);

		// owl update
		$update = $this->owl->update($aArguments);

		/* adjuntos ------------------------------------------------------------
			field = nombre del campo en el formulario HTML
			path = ruta donde se alojará el adjunto
			copies: solo aplica para JPG y PNG
				path = ruta donde se alojará la copia
				img-size = tamaño de la copia INTxINT ó INT que en combinación con img-bgcolor creará una copia cuadrada de INT de lado
				img-bgcolor = color de relleno cuanto img-size sea un INT
			tags = etiquetas asociadas al adjunto

			ejecutar self::call("tutor.uploads")->run("insert", []); por cada adjunto en el formulario
		----------------------------------------------------------------------*/
		// if(!empty($aArguments["_FILES"])) {
		// 	self::call("tutor.uploads")->run("insert", [
		// 		"parent" => $aArguments["imya"],
		// 		"field"  => "INPUTFILENAME",
		// 		"path" => NGL_PATH_PUBLIC."/files/<{=LOWERNAME=}>",
		// 		"copies" => [
		// 			["path" => NGL_PATH_PUBLIC."/files/<{=LOWERNAME=}>/thumbs", "img-size"=>"200", "img-bgcolor"=>"#FFFFFF"],
		// 			["path" => NGL_PATH_PUBLIC."/files/<{=LOWERNAME=}>/thumbs400", "img-size"=>"400x500", "img-bgcolor"=>"#FFFFFF"]
		// 		]
		// 	]);
		// }

		return ($update!==false) ? $update : $this->owl->validate;
	}

	protected function delete($aArguments=[]) {
		if(!count($aArguments)) { return false; }
		// $this->alvin("<{=LOWERNAME=}>.delete");

		$nDelete = $this->owl->delete($aArguments);
		if($nDelete===false) {
			return $this->owl->log;
		} else {
			return $nDelete;
		}
	}
}

?>