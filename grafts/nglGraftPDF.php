<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# pdf
https://hytcom.net/nogal/docs/objects/pdf.md
*/
namespace nogal;
class nglGraftPDF extends nglScion {

	public $pdf = null;
	private $sHeader;
	private $sFooter;
	private $sCSS;

	final protected function __declareArguments__() {
		$vArguments					= [];
		$vArguments["content"]		= ['$mValue', null];
		$vArguments["css"]			= ['$this->SetCSS($mValue)', null];
		$vArguments["encoding"]		= ['(string)$mValue', "UTF-8"];
		$vArguments["filename"]		= ['(string)$mValue', "document.pdf"];
		$vArguments["font"]			= ['(string)$mValue', "helvetica"];
		$vArguments["footer"]		= ['$this->SetFooter($mValue)', null];
		$vArguments["header"]		= ['$this->SetHeader($mValue)', null];
		$vArguments["margin"]		= ['$mValue', [5,5,5,8]];
		$vArguments["marginbottom"]	= ['$mValue', "-"];
		$vArguments["marginleft"]	= ['$mValue', "-"];
		$vArguments["marginright"]	= ['$mValue', "-"];
		$vArguments["margintop"]	= ['$mValue', "-"];
		$vArguments["output"]		= ['$mValue', null];
		$vArguments["page"]			= ['(string)$mValue', "A4"];
		$vArguments["sense"]		= ['(string)$mValue', "P"];
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = [];
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
		if(!\class_exists("\Mpdf\Mpdf")) {
			$this->installPackage("mpdf/mpdf", "^8.0.11");
		}
		require_once(__DIR__."/composer/vendor/paragonie/random_compat/lib/random.php");
		$this->sCSS = "";
	}

	public function page() {
		list($sPageSize, $sSense, $mMargins, $sEncoding, $sFontName) = $this->getarguments("page,sense,margin,encoding,font", \func_get_args());

		if(\is_string($mMargins)) { $mMargins = \json_decode($mMargins, true); }
		if($this->margintop!="-") { $mMargins[0] = $this->margintop; }
		if($this->marginright!="-") { $mMargins[1] = $this->marginright; }
		if($this->marginbottom!="-") { $mMargins[2] = $this->marginbottom; }
		if($this->marginleft!="-") { $mMargins[3] = $this->marginleft; }

		$sTmpDir = self::call()->tempDir().NGL_DIR_SLASH."mpdf";
		if(!\is_dir($sTmpDir)) {
			if(!\mkdir($sTmpDir, 07777)) {
				self::errorMode("die");
				self::errorMessage($this->object, 1002, $sTmpDir);
			}
		}

		$this->pdf = new \Mpdf\Mpdf([
			"tempDir" => $sTmpDir,
			"format" => $sPageSize,
			"mode" => $sEncoding,
			"orientation" => $sSense,
			"margin_top" => $mMargins[0],
			"margin_right" =>  $mMargins[1],
			"margin_bottom" =>  $mMargins[2],
			"margin_left" => $mMargins[3],
			"default_font" => $sFontName
		]);

		return $this;
	}

	protected function SetHeader($sHeader) {
		$this->sHeader = $sHeader;
		return $this;
	}

	protected function SetFooter($sFooter) {
		$this->sFooter = $sFooter;
		return $this;
	}

	protected function SetCSS($sCSS) {
		$this->sCSS = $sCSS;
		return $this;
	}

	public function base64() {
		list($sContent,$sFilename) = $this->getarguments("content,filename", \func_get_args());
		if($this->pdf===null) { $this->CreatePDF($sFilename); }
		return \base64_encode($this->WriteContent($sContent, "source", false));
	}

	public function download() {
		list($sContent,$sFilename) = $this->getarguments("content,filename", \func_get_args());
		if($this->pdf===null) { $this->CreatePDF($sFilename); }
		if(\count(self::errorGetLast())) { exit(); }
		return $this->WriteContent($sContent, "download", $sFilename);
	}

	public function load() {
		list($sFilename) = $this->getarguments("filename", \func_get_args());
		if($this->pdf===null) { $this->CreatePDF($sFilename); }
		return $this;
	}

	public function save() {
		list($sContent,$sFilename) = $this->getarguments("content,filename", \func_get_args());
		if($this->pdf===null) { $this->CreatePDF($sFilename); }
		$this->WriteContent($sContent, "save", $sFilename);
		return true;
	}

	public function view() {
		list($sContent,$sFilename) = $this->getarguments("content,filename", \func_get_args());
		if($this->pdf===null) { $this->CreatePDF($sFilename); }
		return $this->WriteContent($sContent);
	}

	private function CreatePDF() {
		list($sFileName) = $this->getarguments("filename", \func_get_args());
		if(empty($sFileName)) { $sFileName = "document.pdf"; }
		$sFileName = self::call()->sandboxPath($sFileName);
		$this->args(["filename"=>$sFileName]);
		return $this->page();
	}

	private function WriteContent($sContent, $sOutputMode=true, $sFilename=null) {
		if($this->pdf===null) { $this->CreatePDF($sFilename); }
		if($sFilename===null) { $sFilename = $this->argument("filename"); }

		$sOutput = \Mpdf\Output\Destination::INLINE;
		if($sOutputMode!==true) {
			$sOutputMode = \strtolower($sOutputMode);
			if($sOutputMode=="view") {
				$sOutput = \Mpdf\Output\Destination::INLINE;
			} else if($sOutputMode=="download") {
				$sOutput = \Mpdf\Output\Destination::DOWNLOAD;
			} else if($sOutputMode=="source") {
				$sOutput = \Mpdf\Output\Destination::STRING_RETURN;
			} else if($sOutputMode=="save") {
				$sFilename = self::call()->sandboxPath($sFilename);
				$sOutput = \Mpdf\Output\Destination::FILE;
			}
		}

		$this->pdf->WriteHTML($this->sCSS , \Mpdf\HTMLParserMode::HEADER_CSS);
		if($this->sHeader!==null) { $this->pdf->SetHTMLHeader($this->sHeader); }
		if($this->sFooter!==null) { $this->pdf->SetHTMLFooter($this->sFooter); }
		$this->pdf->WriteHTML($sContent, \Mpdf\HTMLParserMode::HTML_BODY);

		return $this->pdf->Output($sFilename, $sOutput);
	}
}

?>