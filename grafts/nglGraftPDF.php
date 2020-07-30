<?php

namespace nogal;

/** CLASS {
	"name" : "nglGraftPDF",
	"object" : "pdf",
	"type" : "instanciable",
	"revision" : "20150930",
	"extends" : "nglBranch",
	"interfaces" : "inglFeeder",
	"description" : "Implementa https://mpdf.github.io",
	"arguments": {
		"after" : ["mixed", "
			Determina una posible acción luego de ejecutar el método write
			<ul>
				<li><b>null</b> solo escribe</li>
				<li><b>view</b> muestra el documento en el navegador</li>
				<li><b>download</b> fuerza la descarga del documento</li>
			</ul>
		", "null"],
		"content" : ["string", "Contanido del PDF", "test1234"],
		"filename" : ["string", "Nombre de archivo de salida", "document.pdf"],
		"sense" : ["string", "Sentido de la hoja, P (vertical) ó L (horizontal)", "P"],
		"page" : ["string", "
			Tamaño de la página
			<ul>
				<li>A4</li>
				<li>A5</li>
				<li>LETTER</li>
				<li>widthxheight (100x200)</li>
			</ul>
		", "A4"],
		"language" : ["string", "Lenguaje", "es"],
		"unicode" : ["boolean", "Determina el uso de Unicode", "true"],
		"encoding" : ["string", "Juego de caracteres predeterminado", "UTF-8"],
		"margins" : ["array", "Array o JSON con los margenes de la página (top,right,bottom,left)", "[5,5,5,8]"]
	}
}

**/
class nglGraftPDF extends nglScion {

	public $pdf = null;
	private $sContent;
	private $sHeader;
	private $sFooter;
	private $sCSS;

	final protected function __declareArguments__() {
		$vArguments					= array();
		$vArguments["output"]		= array('$mValue', null);
		$vArguments["css"]			= array('$this->SetCSS($mValue)', null);
		$vArguments["header"]		= array('$this->SetHeader($mValue)', null);
		$vArguments["footer"]		= array('$this->SetFooter($mValue)', null);
		$vArguments["content"]		= array('$this->SetContent($mValue)', null);
		$vArguments["filename"]		= array('(string)$mValue', "document.pdf");
		$vArguments["font"]			= array('(string)$mValue', "helvetica");
		$vArguments["sense"]		= array('(string)$mValue', "P");
		$vArguments["page"]			= array('(string)$mValue', "A4");
		$vArguments["encoding"]		= array('(string)$mValue', "UTF-8");
		$vArguments["margin"]		= array('$mValue', array(5,5,5,8));
		$vArguments["margintop"]	= array('$mValue', "-");
		$vArguments["marginright"]	= array('$mValue', "-");
		$vArguments["marginbottom"]	= array('$mValue', "-");
		$vArguments["marginleft"]	= array('$mValue', "-");
		$vArguments["tmpdir"]		= array('$mValue', NGL_PATH_TMP.NGL_DIR_SLASH."mpdf");
		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes = array();
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
		require_once(__DIR__."/vendor/paragonie/random_compat/lib/random.php");
		$this->sCSS = "";
	}

	public function create() {
		list($sFileName) = $this->getarguments("filename", func_get_args());
		if($sFileName===null) { $sFileName = "document.pdf"; }
		$sFileName = self::call()->sandboxPath($sFileName);
		$this->args(array("filename"=>$sFileName));
		return $this->page();
	}

	public function page() {
		list($sPageSize, $sSense, $mMargins, $sEncoding, $sFontName) = $this->getarguments("page,sense,margin,encoding,font", func_get_args());
		
		if(is_string($mMargins)) { $mMargins = json_decode($mMargins, true); }
		if($this->argument("margintop")!="-") { $mMargins[0] = $this->argument("margintop"); }
		if($this->argument("marginright")!="-") { $mMargins[1] = $this->argument("marginright"); }
		if($this->argument("marginbottom")!="-") { $mMargins[2] = $this->argument("marginbottom"); }
		if($this->argument("marginleft")!="-") { $mMargins[3] = $this->argument("marginleft"); }

		$sTmpDir = self::call()->sandboxPath($this->argument("tmpdir"));
		if(!is_dir($sTmpDir)) {
			if(!mkdir($sTmpDir, 07777)) {
				self::errorMode("die");
				self::errorMessage($this->object, 1001, "Can't create TMPDIR: ".$sTmpDir);
			}
		}

		$this->pdf = new \Mpdf\Mpdf(array(
			"tempDir" => $sTmpDir,
			"format" => $sPageSize,
			"mode" => $sEncoding,
			"orientation" => $sSense,
			"margin_top" => $mMargins[0], 
			"margin_right" =>  $mMargins[1], 
			"margin_bottom" =>  $mMargins[2], 
			"margin_left" => $mMargins[3], 
			"default_font" => $sFontName 
		));

		return $this;
	}

	protected function SetContent($sContent) {
		$this->sContent = $sContent;
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
		list($sFilename) = $this->getarguments("filename", func_get_args());
		if($this->pdf===null) { $this->create(); }
		return base64_encode($this->WriteContent($this->sContent, "source", false));
	}

	public function download() {
		list($sFilename) = $this->getarguments("filename", func_get_args());
		if($this->pdf===null) { $this->create(); }
		if(count(self::errorGetLast())) { exit(); }
		return $this->WriteContent($this->sContent, "download", $sFilename);
	}

	public function save() {
		list($sFilename) = $this->getarguments("filename", func_get_args());
		if($this->pdf===null) { $this->create(); }
		return $this->WriteContent($this->sContent, "save", $sFilename);
	}

	public function view() {
		list($sFilename) = $this->getarguments("filename", func_get_args());
		if($this->pdf===null) { $this->create(); }
		return $this->WriteContent($this->sContent);
	}

	protected function WriteContent($sContent, $sOutputMode=true, $sFilename=null) {
		if($this->pdf===null) { $this->create()->SetContent(); }
		if($sFilename===null) { $sFilename = $this->argument("filename"); }

		$sOutput = \Mpdf\Output\Destination::INLINE;
		if($sOutputMode!==true) {
			$sOutputMode = strtolower($sOutputMode);
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
		$this->pdf->WriteHTML($this->sContent, \Mpdf\HTMLParserMode::HTML_BODY);

		return $this->pdf->Output($sFilename, $sOutput);
	}
}

?>