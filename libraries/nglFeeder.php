<?php

namespace nogal;

/** CLASS {
	"name" : "nglFeeder",
	"revision" : "20140127",
	"extends" : "nglTrunk",
	"description" : "Contenedor de objetos globales",
	"variables": {
		"$class" : ["string", "Clase a la que pertenece el objeto"],
		"$me" : ["string", "Nombre Real del objeto"],
		"$object" : ["string", "Nombre del objeto"]
	}
} **/
abstract class nglFeeder extends nglTrunk {

	protected $class;
	protected $me;
	protected $object;

	final public function __builder__($vArguments) {
		$this->class	= $vArguments[0];
		$this->me		= $vArguments[1];
		$this->object	= $vArguments[1]; 
	}

	/** FUNCTION {
		"name" : "__toString", 
		"type" : "public",
		"description" : "Método mágico que retorna el nombre del objeto y el de la clase a la que instancia, separados por dos puntos (:).",
		"return" : "string"
	} **/
	final public function __toString() {
		return $this->me.":".$this->class;
	}

	/** FUNCTION {
		"name" : "__destroy__", 
		"type" : "public",
		"description" : "Elimina el objeto utilizando el método kill del framework",
		"return" : "boolean"
	} **/
	final public function __destroy__() {
		return self::kill($this->me);
	}

	/** FUNCTION {
		"name" : "__me__", 
		"type" : "public",
		"description" : "Retorna un objeto o array con los nombre objeto y clase a la que instancia",
		"parameters" : { "$bArray" : ["boolean", "Si el valor es True se retorna un array", "false"] },
		"examples" : {
			"objeto" : "
				$object->name = nombre del objeto;
				$object->class = nombre de la clase;
			",
			"array" : "
				array(
				→ "0" => "nombre del objeto",
				→ "1" => "nombre de la clase",
				→ "name" => "nombre del objeto",
				→ "class" => "nombre de la clase",
				);
			"
		},
		"return" : "object o array"
	} **/
	final public function __me__($bArray=false) {
		if(!$bArray) {
			$me = new \stdClass();
			$me->name = $this->me;
			$me->class = $this->class;
			return $me;
		} else {
			$vMe = array();
			$vMe[0] 		= $this->me;
			$vMe[1] 		= $this->class;
			$vMe["name"]	= $this->me;
			$vMe["class"]	= $this->class;
			return $vMe;
		}
	}

	/** FUNCTION {
		"name" : "__Whoami__", 
		"type" : "protected",
		"description" : "
			Retorna metodos del objeto.
			Este método es llamado por el método whois del framework
		",
		"return" : "array"
	} **/
	final public function __whoami__() {
		$aMethods = array("__destroy__", "__me__", "__whoami__");
		$reflection = new \ReflectionClass(__NAMESPACE__."\\".$this->class);
		$aThisMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach($aThisMethods as $method) {
			if($method->class==__NAMESPACE__."\\".$this->class && $method->name[0]!="_") {
				$aMethods[] = $method->name;
			}
		}
		sort($aMethods);
		return $aMethods;
	}
}

?>