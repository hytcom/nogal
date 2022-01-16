<?php

namespace nogal;

interface inglFeeder {
	public function __init__();
}

interface inglBranch {
	public function __init__();
}

interface iNglClient extends inglBranch {
	public function count();	// retorna el número de registros del conjunto de resultados
	public function get();		// retorna un registro del conjunto de resultados
	public function getall();	// retorna un array con todos los registros del conjunto de resultados
}

interface iNglDataBase extends inglBranch {
	public function close();	// cierra la conexión
	public function connect();	// abre la conexión
	public function escape();	// escapa valores de manera segura en sentencias SQL
	public function exec();		// ejecuta una sentencia y retorna el objeto original de PHP
	public function mexec();	// ejecuta varias sentencias y retorna un array de objetos originales de PHP
	public function mquery();	// ejecuta varias sentencias y retorna un array de objetos Query del framework
	public function insert();	// inserta datos provenientes de un array asociativo o de una cadena de variables en una tabla
	public function query();	// ejecuta una sentencia y retorna el objeto Query del framework
	public function update();	// actualiza datos provenientes de un array asociativo o de una cadena de variables en una tabla
}

interface iNglDBQuery extends iNglClient {
	public function allrows();	// retorna el número de registros del conjunto de resultados ignorando los valores LIMIT
	public function columns();	// devuelve las columnas de un conjunto de resultados
	public function lastid();	// retorna el id de la última sentencia de inserción
	public function reset();	// posiciona el puntero en la fila 0 del conjunto de resultados
	public function rows();		// alias de count
	public function toArray();	// retorna un array con todas las filas del conjunto de resultados en modo asociativo y resetea el puntero
}

interface iNglJSQL extends inglFeeder {
	public function colAdd($aJSQL);
	public function colDrop($aJSQL);
	public function colModify($aJSQL);
	public function colRename($aJSQL);
	public function column($mColumn);
	public function comment($aJSQL);
	public function create($aJSQL);
	public function datatypes(); // debe retornar un array con la equivalencia de tipos de datos. Indices en mayúsculas
	public function delete($aJSQL);
	public function drop($aJSQL);
	public function index($aJSQL);
	public function indexdrop($aJSQL);
	public function insert($aJSQL);
	public function rename($aJSQL);
	public function select($aJSQL);
	public function update($aJSQL);
}

?>