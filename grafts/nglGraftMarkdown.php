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
		$vArguments				= [];
		$vArguments["content"]	= ['(string)$mValue', "test1234"];
		$vArguments["links"]	= ['$this->SetUrlsLinked($mValue)', true];
		$vArguments["nl2br"]	= ['$this->SetNewLineToBreak($mValue)', true];
		$vArguments["html"]		= ['$this->SetMarkupHTML($mValue)', true];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = [];
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		require_once(self::path("grafts").NGL_DIR_SLASH."classes".NGL_DIR_SLASH."Parsedown.php");
		$this->md = new \Parsedown();
	}

	final public function __init__() {
		if(!\class_exists("\Parsedown")) {
			$this->__errorMode__("die");
			self::errorMessage($this->object, 1000);
		}
		$this->md->setUrlsLinked($this->argument("links"));
		$this->md->setBreaksEnabled($this->argument("nl2br"));
		$this->md->setMarkupEscaped($this->argument("html"));
	}

	public function format() {
		list($sContent) = $this->getarguments("content", \func_get_args());
		$sMDCode = $this->md->text($sContent);
		return $sMDCode;
	}
	
	public function formatfile() {
		list($sFileName) = $this->getarguments("filepath", \func_get_args());
		$sFileName = self::call()->sandboxPath($sFileName);
		$sContent = self::call("file")->load($sFileName)->read();
		$sContent = $this->PlantUML($sContent);
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

	// plantuml 
	private function PlantUML($sMDCode) {
		return \preg_replace_callback(
			'/```plantuml(.*?)```/is',
			function($aMatchs) {
				return $this->PlantImage($aMatchs[1]);
			},
			$sMDCode
		);
	}

	private function PlantEncode($text) {
		$compressed = \gzdeflate($text, 9);
		return $this->PlantEncode64($compressed);
	}

	private function PlantImage($sCode) {
		return "![plantuml](http://www.plantuml.com/plantuml/png/".$this->PlantEncode($sCode).")";
	}

	private function PlantEncode6bit($b) {
		if($b < 10) { return \chr(48 + $b); }
		$b -= 10;
		if($b < 26) { return \chr(65 + $b); }
		$b -= 26;
		if($b < 26) { return \chr(97 + $b); }
		$b -= 26;
		if($b == 0) { return "-"; }
		if($b == 1) { return "_"; }
		return "?";
	}
	
	private function PlantAppend3bytes($b1, $b2, $b3) {
		$c1 = $b1 >> 2;
		$c2 = (($b1 & 0x3) << 4) | ($b2 >> 4);
		$c3 = (($b2 & 0xF) << 2) | ($b3 >> 6);
		$c4 = $b3 & 0x3F;
		$r = "";
		$r .= $this->PlantEncode6bit($c1 & 0x3F);
		$r .= $this->PlantEncode6bit($c2 & 0x3F);
		$r .= $this->PlantEncode6bit($c3 & 0x3F);
		$r .= $this->PlantEncode6bit($c4 & 0x3F);
		return $r;
	}
	
	private function PlantEncode64($c) {
		$str = "";
		$len = \strlen($c);
		for ($i = 0; $i < $len; $i+=3) {
			if ($i+2==$len) {
				$str .= $this->PlantAppend3bytes(\ord(\substr($c, $i, 1)), \ord(\substr($c, $i+1, 1)), 0);
			} else if ($i+1==$len) {
				$str .= $this->PlantAppend3bytes(\ord(\substr($c, $i, 1)), 0, 0);
			} else {
				$str .= $this->PlantAppend3bytes(\ord(\substr($c, $i, 1)), \ord(\substr($c, $i+1, 1)), \ord(\substr($c, $i+2, 1)));
			}
		}
		return $str;
	}
}

?>