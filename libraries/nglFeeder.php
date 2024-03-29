<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___
Contenedor de objetos globales
*/
namespace nogal;
abstract class nglFeeder extends nglTrunk {

	protected $class; // clase a la que pertenece el objeto
	protected $me; // nombre Real del objeto
	protected $object; // nombre del objeto

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
		"name" : "__Whoami__",
		"type" : "protected",
		"description" : "
			Retorna metodos del objeto.
			Este método es llamado por el método whois del framework
		",
		"return" : "array"
	} **/
	final public function __whoami__() {
		$aMethods = ["__destroy__", "__me__", "__whoami__"];
		$reflection = new \ReflectionClass(__NAMESPACE__."\\".$this->class);
		$aThisMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach($aThisMethods as $method) {
			if($method->class==__NAMESPACE__."\\".$this->class && $method->name[0]!="_") {
				$aMethods[] = $method->name;
			}
		}
		\sort($aMethods);
		return $aMethods;
	}
}

?>