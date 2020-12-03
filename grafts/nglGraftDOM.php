<?php

namespace nogal;

/** CLASS {
	"name" : "nglGraftDOM",
	"object" : "dom",
	"type" : "instanciable",
	"revision" : "20171124",
	"extends" : "nglBranch",
	"interfaces" : "inglFeeder",
	"description" : "Implementa http://simplehtmldom.sourceforge.net/",

} **/


class nglGraftDOM extends nglScion {

	public $dom = null;

	final protected function __declareArguments__() {
		$vArguments							= [];
		$vArguments["content"]				= ['(string)$mValue', "test1234"];
		$vArguments["selector"]				= ['(string)$mValue', ""];
		$vArguments["index"]				= ['(int)$mValue', 0];
		$vArguments["lowercase"]			= ['(boolean)$mValue', true];
		$vArguments["tagsclosed"]			= ['(boolean)$mValue', true];
		$vArguments["charset"]				= ['(string)$mValue', "UTF-8"];
		$vArguments["brtext"]				= ['(string)$mValue', "\r\n"];
		$vArguments["spantext"]				= ['(string)$mValue', " "];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = [];
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		require_once(self::path("grafts").NGL_DIR_SLASH."classes".NGL_DIR_SLASH."simple_html_dom.php");
	}

	public function load() {
		list($sContent,$bLowerCase,$sBrText,$sSpanText,$bTagsClosed,$sCharset) = $this->getarguments("content,lowercase,brtext,spantext,tagsclosed,charset", \func_get_args());
		$this->dom = new \simple_html_dom(
			null,
			$bLowerCase,
			$bTagsClosed,
			$sCharset
		);
		$this->dom->load($sContent, $bLowerCase, true, $sBrText, $sSpanText);

		return $this;
	}

	public function get() {
		list($sSelector, $nIndex) = $this->getarguments("selector,index", \func_get_args());
		$element = $this->dom->find($sSelector, $nIndex);
		if($element!==null) {
			return $this->GetElementData($element,true);
		}
		return null;
	}

	public function getall() {
		list($sSelector) = $this->getarguments("selector", \func_get_args());
		$aElements = [];
		foreach($this->dom->find($sSelector) as $element) {
			$aElements[] = $this->GetElementData($element);
		}
		return $aElements;
	}

	private function GetElementData($element,$f=false) {
		$tag			= new \stdClass();
		$tag->tag		= $element->tag;
		$tag->nodeType	= $element->nodeType;
		$tag->outerHTML	= $element->outertext;
		$tag->html		= $element->innertext;
		$tag->text		= $element->plaintext;

		$tag->attr = [];
		if(\is_array($element->attr) && \count($element->attr)) {
			foreach($element->attr as $sAttrName => $sAttrValue) {
				$tag->attr[$sAttrName] = $sAttrValue;
			}
		}

		$tag->children = [];
		if(\is_array($element->children) && \count($element->children)) {
			foreach($element->children as $nChild => $aChild) {
				$tag->children[$nChild] = $this->GetElementData($aChild);
			}
		}

		return $tag;
	}
}

?>