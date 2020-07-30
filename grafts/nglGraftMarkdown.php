<?php

namespace nogal;

/** CLASS {
	"name" : "nglGraftMarkdown",
	"object" : "md",
	"type" : "instanciable",
	"revision" : "20171124",
	"extends" : "nglBranch",
	"interfaces" : "inglFeeder",
	"description" : "Implementa http://parsedown.org/",
	"arguments": {
		"content" : ["string", "Contenido markdown", "test1234"],
	}
} **/

/*
echo $ngl("md.")->get('Hello _Parsedown_!');
*/
class nglGraftMarkdown extends nglScion {

	public $md = null;

	final protected function __declareArguments__() {
		$vArguments				= array();
		$vArguments["content"]	= array('(string)$mValue', "test1234");
		$vArguments["links"]	= array('$this->SetUrlsLinked($mValue)', true);
		$vArguments["nl2br"]	= array('$this->SetNewLineToBreak($mValue)', true);
		$vArguments["html"]		= array('$this->SetMarkupHTML($mValue)', true);
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = array();
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		require_once(self::path("grafts").NGL_DIR_SLASH."classes".NGL_DIR_SLASH."Parsedown.php");
		$this->md = new \Parsedown();
	}

	final public function __init__() {
		$this->md->setUrlsLinked($this->argument("links"));
		$this->md->setBreaksEnabled($this->argument("nl2br"));
		$this->md->setMarkupEscaped($this->argument("html"));
	}

	public function format() {
		list($sContent) = $this->getarguments("content", func_get_args());
		return $this->md->text($sContent);
	}

	public function formatfile() {
		list($sFileName) = $this->getarguments("filepath", func_get_args());
		$sFileName = self::call()->sandboxPath($sFileName);
		$sContent = self::call("file")->load($sFileName)->read();
		return $this->md->text($sContent);
	}

	protected function SetNewLineToBreak($bBoolean) {
		$bBoolean = self::call()->isTrue($bBoolean);
		$this->md->setBreaksEnabled($bBoolean);
		return $bBoolean;
	}

	protected function SetUrlsLinked($bBoolean) {
		$bBoolean = self::call()->isTrue($bBoolean);
		$this->md->setUrlsLinked($bBoolean);
		return $bBoolean;
	}

	protected function SetMarkupHTML($bBoolean) {
		$bBoolean = self::call()->isTrue($bBoolean);
		$this->md->setMarkupEscaped($bBoolean);
		return $bBoolean;
	}

}

?>