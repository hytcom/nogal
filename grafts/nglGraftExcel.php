<?php

namespace nogal;

/** CLASS {
	"name" : "nglGraftExcel",
	"object" : "excel",
	"type" : "instanciable",
	"revision" : "20150930",
	"extends" : "nglBranch",
	"interfaces" : "inglFeeder",
	"description" : "Implementa la clase "PhpOffice\PhpSpreadsheet".
	
		// lee un excel
		$excel = $ngl("excel")->load("names.xlsx");
		print_r($excel->getall());exit();

		// lee un HTML
		$excel = $ngl("excel")->load("tabla.html");
		print_r($excel->getall());exit();
		

		// crear un excel
		$excel = $ngl("excel")->load("test.xls");
		$excel->set("A1", array(
			array("NOMBRE", "APELLIDO", "EDAD", "MESES"),
			array("ariel", "bottero", "40"),
			array("homero", "simpson", "35")
		));
		$excel->download("test.xls");
		exit();


		// lee un excel, modifica algo y escribe un html
		$excel = $ngl("excel")->load("names.xlsx");
		$excel->set("A2", "GROSO");
		$excel->download("names.html");
		exit();

		// lee un excel y retorna un arbol en foramto JSON
		$a = $ngl("excel")->load("impuestos_x_pais.xlsx")->getall(true);

		$a = $ngl()->arrayGroup($a, array(
			"MAIN" => array("pais", []),
			"tax" => array("nombre", array("sin", "con"))
		));

		echo $ngl("shift")->convert($a, "array-json");
	
	",
	"arguments": {
		"content" : ["string", "Contanido del PDF", "test1234"],
		"filename" : ["string", "Nombre de archivo de salida", "document.pdf"]
	}
}

**/
class nglGraftExcel extends nglScion {

	public $excel = null;
	private $nMaxRow;
	private $sMaxCol;
	private $nRow = 0;

	final protected function __declareArguments__() {
		$vArguments						= [];
		$vArguments["content"]			= ['$mValue', null];
		$vArguments["filename"]			= ['(string)$mValue', null];
		$vArguments["sheet"]			= ['$this->SetSheet($mValue)', 0];
		$vArguments["title"]			= ['$this->SetTitle($mValue)', "Hoja1"];
		$vArguments["index"]			= ['$mValue', "A1"];
		$vArguments["cellval"]			= ['strtolower($mValue)', "value"]; // value | calculated | formatted

		$vArguments["fontfamily"]		= ['$mValue', "Calibri"];
		$vArguments["fontsize"]			= ['$mValue', 8];

		$vArguments["empty"]			= ['$mValue', null];
		$vArguments["calculate"]		= ['self::call()->istrue($mValue)', true];
		$vArguments["format"]			= ['self::call()->istrue($mValue)', true];
		$vArguments["colnames"]			= ['self::call()->istrue($mValue)', true]; // usa la primer fila como clase
		$vArguments["colref"]			= ['self::call()->istrue($mValue)', true];

		$vArguments["styles"]			= ['$mValue', null];
		$vArguments["unmergefill"]		= ['self::call()->istrue($mValue)', false]; 

		$vArguments["csv_enclosed"]		= ['$mValue', '"'];
		$vArguments["csv_splitter"]		= ['$mValue', ";"];
		$vArguments["csv_eol"]			= ['$mValue', "\r\n"];

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes				= [];
		$vAttributes["rows"]		= null;
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	public function load() {
		list($sFileName,$sFontFamily,$nFontSize) = $this->getarguments("filename,fontfamily,fontsize", \func_get_args());
		$this->args(["filename"=>$sFileName]);

		$sFileName = self::call()->sandboxPath($sFileName);
		if(\file_exists($sFileName)) {
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($sFileName);
			self::call()->errorReporting(false);
			$this->excel = $reader->load($sFileName);
			$aError = self::call()->errorGetLast();
			if(\count($aError) && \strpos($aError["file"],"Spreadsheet")) { return false; }
			self::call()->errorClearLast();
			self::call()->errorReportingRestore();

			$this->nMaxRow = $this->excel->getActiveSheet()->getHighestRow(); 
			$this->sMaxCol = $this->excel->getActiveSheet()->getHighestColumn();
			$this->attribute("rows", $this->nMaxRow);
		} else {
			$this->excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
			$this->excel->getDefaultStyle()->getFont()->setName($sFontFamily);
			$this->excel->getDefaultStyle()->getFont()->setSize($nFontSize);
			$this->nMaxRow = "1"; 
			$this->sMaxCol = "A";
		}

		return $this;
	}

	public function rows() {
		return $this->nMaxRow;
	}

	public function cols() {
		return $this->sMaxCol;
	}

	public function get() {
		$this->nRow++;
		if($this->nRow>$this->nMaxRow) { $this->nRow = 0; return false; }
		return $this->row($this->nRow);
	}
	
	public function getall() {
		list($bColnames,$sFrom) = $this->getarguments("colnames,index", \func_get_args());
		$sRange = $sFrom.":".$this->sMaxCol.$this->nMaxRow;
		$aData = $this->GetRange($sRange);
		if($bColnames) {
			$aFirstRow = \array_shift($aData);
			$aData = self::call()->arrayArrayCombine($aFirstRow, $aData);
		}
		return $aData;
	}

	public function reset() {
		$this->nRow = 0;
		return $this;
	}
	
	// retorna el valor de una celda segun el tipo especificado
	public function cell() {
		list($sCell, $sFormat) = $this->getarguments("index,cellval", \func_get_args());
		$sFormat = strtolower($sFormat);
		$cell = $this->excel->getActiveSheet()->getCell($sCell);
		if($sFormat=="calculated") {
			return $cell->getCalculatedValue();
		} else if($sFormat=="formatted") {
			return $cell->getFormattedValue();
		} else {
			return $cell->getValue();
		}
	}

	// obtiene todos los valores de una fila. index debe ser un INT
	public function row() {
		list($nRow) = $this->getarguments("index", \func_get_args());
		$nRow = (int)$nRow;
		$sRange = "A".$nRow.":".$this->sMaxCol.$nRow;
		return \current($this->GetRange($sRange));
	}

	// obtiene todos los valores de una columna. index debe ser un STRING
	public function col() {
		list($sColumn) = $this->getarguments("index", \func_get_args());
		$sRange = $sColumn."1:".$sColumn.$this->nMaxRow;
		$aColumns = []; 
		$aGet = $this->GetRange($sRange);
		foreach($aGet as $nIndex => $aRow) {
			$aColumns[$nIndex] = \current($aRow);
		}
		return $aColumns;
	}
	
	/*
	A1		= celda
	A1:E9	= rango normal
	A1:E*	= desde A1 hasta columna E y el maximo de filas
	A1:*9	= desde A1 hasta la maxima columna fila 9
	A1:**	= desde A1 hasta maxima columna y maxima fila
	A*		= desde A1 hasta la maxima fila de A
	*5		= desde A5 hasta la maxima columna de la fila 5
	*/
	private function StrToRange($sRange) {
		if(\strpos($sRange, "*")!==false) {
			$sRange = \strtoupper($sRange);
			if(\strpos($sRange, ":")) {
				$aRange = \explode(":", $sRange);
				$aRange[0] = $this->CellParts($aRange[0]);
				$aRange[1] = $this->CellParts($aRange[1]);
				$sToCol	= ($aRange[1][0]=="*") ? $this->sMaxCol : $aRange[1][0];
				$sToRow	= ($aRange[1][1]=="*") ? $this->nMaxRow : $aRange[1][1];
				$sRange =  $aRange[0][0].$aRange[0][1].":".$sToCol.$sToRow;
			} else {
				$aRange = $this->CellParts($sRange);
				$sToCol = $aRange[0];
				$sToRow = $aRange[1];
				if($aRange[0]=="*") { $aRange[0] = "A"; $sToCol = $this->sMaxCol; }
				if($aRange[1]=="*") { $aRange[1] = 1; $sToRow = $this->nMaxRow; }
				$sRange =  $aRange[0].$aRange[1].":".$sToCol.$sToRow;
			}
		}

		return $sRange;
	}

	private function CellParts($sCell) {
		$sCell = \trim($sCell);
		$sCol = ($sCell[0]=="*") ? "*" : \preg_replace("/[^A-Z]/i", "", $sCell);
		$nRow = (\substr($sCell, -1)=="*") ? "*" : \preg_replace("/[^0-9]/", "", $sCell);
		return [$sCol, $nRow];
	}

	// obtiene un rango de filas y columnas
	public function range() {
		list($sRange) = $this->getarguments("index", \func_get_args());
		return $this->GetRange($this->StrToRange($sRange));
	}

	// une un rango de celdas. Prevalece como valor el de la primer celda
	public function merge() {
		list($sRange, $mContent) = $this->getarguments("index", \func_get_args());
		if(\strpos($sRange, ":")) {
			$this->excel->getActiveSheet()->mergeCells($sRange);
		}
		return $this;
	}

	// setea contenido en una celda
	// si index es un rango, hace merge de las celdas
	public function set() {
		list($sCell, $mContent) = $this->getarguments("index,content", \func_get_args());
		if(!\is_array($mContent)) {
			$sCellName = $sCell;
			if(\strpos($sCell, ":")) { $aCell = \explode(":", $sCell); $sCellName = $aCell[0]; }
			$this->excel->getActiveSheet()->setCellValue($sCellName, $mContent);
			if(isset($aCell)) { $this->excel->getActiveSheet()->mergeCells($sCell); }
			return $this;
		} else {
			$sEmptyValue = $this->argument("empty");
			if(!self::call()->isArrayArray($mContent)) { $mContent = [$mContent]; }
			$this->excel->getActiveSheet()->fromArray($mContent, $sEmptyValue, $sCell);
		}

		$this->nMaxRow = $this->excel->getActiveSheet()->getHighestRow(); 
		$this->sMaxCol = $this->excel->getActiveSheet()->getHighestColumn();
		return $this;
	}

	public function style() {
		list($sRange, $mStyles) = $this->getarguments("index,styles", \func_get_args());
		$sRange = $this->StrToRange($sRange);
		$aStyles = $this->GetStyles($mStyles);
		if(isset($aStyles["sizes"])) {
			$aRange = \explode(":", $sRange);
			if(!isset($aRange[1])) { $aRange[1] = $aRange[0]; }
			$aCellFrom = $this->CellParts($aRange[0]);
			$aCellTo = $this->CellParts($aRange[1]);

			if(isset($aStyles["sizes"]["width"])) {
				$aCellTo[0]++;
				for($sCol=$aCellFrom[0]; $sCol!==$aCellTo[0]; $sCol++) {
					if($aStyles["sizes"]["width"]!="auto") {
						$this->excel->getActiveSheet()->getColumnDimension($sCol)->setAutoSize(false);
						$this->excel->getActiveSheet()->getColumnDimension($sCol)->setWidth($aStyles["sizes"]["width"]);
					} else {
						$this->excel->getActiveSheet()->getColumnDimension($sCol)->setAutoSize(true);
					}
				}
			}

			if(isset($aStyles["sizes"]["height"])) {
				$aCellTo[1]++;
				for($nRow=$aCellFrom[1]; $nRow!==$aCellTo[1]; $nRow++) {
					$this->excel->getActiveSheet()->getRowDimension($nRow)->setRowHeight($aStyles["sizes"]["height"]);
				}
			}

			unset($aStyles["sizes"]);
		}

		$this->excel->getActiveSheet()->getStyle($sRange)->applyFromArray($aStyles);

		return $this;
	}

	public function write() {
		list($sFileName) = $this->getarguments("filename", \func_get_args());
		$sFileName = self::call()->sandboxPath($sFileName);
		$aFileType = $this->FileType($sFileName);
		$writer =  \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->excel, $aFileType[0]);
		if($aFileType[0]=="Csv") {
			$writer->setEnclosure($this->argument("csv_enclosed"));
			$writer->setDelimiter($this->argument("csv_splitter"));
			$writer->setLineEnding($this->argument("csv_eol"));
		}
		$writer->save($sFileName);
		return $this;
	}

	public function download() {
		list($sFileName) = $this->getarguments("filename", \func_get_args());
		$aFileType = $this->FileType($sFileName);

		if(\count(self::errorGetLast())) { exit(); }
		
		\header("Content-Type: ".$aFileType[1]);
		\header("Content-Disposition: attachment;filename=\"".$sFileName."\"");
		\header("Cache-Control: max-age=0");

		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->excel, $aFileType[0]);
		$writer->save("php://output");
	}

	// retorna el documento en formato HTML hacia la salida de datos
	public function html() {
		\header("Content-Type: text/html");
		\header("Cache-Control: max-age=0");
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->excel, "Html");
		\ob_start();
		$writer->save("php://output");
		$sSource = \ob_get_contents();
		\ob_end_clean();
		return $sSource;
	}

	public function source() {
		list($sFileName) = $this->getarguments("filename", \func_get_args());
		$aFileType = $this->FileType($sFileName);
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->excel, $aFileType[0]);
		\ob_start();
		$writer->save("php://output");
		$sSource = \ob_get_contents();
		\ob_end_clean();
		return $sSource;
	}

	// descombina celdas. Si unmergefill es true, completa el valor de todas en el de la primera
	public function unmergeAll() {
		list($bFill) = $this->getarguments("unmergefill", \func_get_args());
		$aMerged = $this->excel->getActiveSheet()->getMergeCells();
		if(\is_array($aMerged) && \count($aMerged)) {
			foreach($aMerged as $sRange) {
				$this->excel->getActiveSheet()->unmergeCells($sRange);
				if($bFill) {
					$aCells = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::extractAllCellReferencesInRange($sRange);
					$mValue = $this->cell($aCells[0]);
					for($x=1;$x<\count($aCells);$x++) {
						$this->set($aCells[$x], $mValue);
					}
				}
			}
		}

		return $this;
	}

	private function GetRange($sRange) {
		$sEmptyValue		= $this->argument("empty");
		$bCalculateFormulas	= $this->argument("calculate");
		$bApplyFormat		= $this->argument("format");
		$bColumnReference	= $this->argument("colref");
		return $this->excel->getActiveSheet()->rangeToArray($sRange, $sEmptyValue, $bCalculateFormulas, $bApplyFormat, $bColumnReference);
	}
	
	protected function SetTitle($sTitle) {
		$this->excel->getActiveSheet()->setTitle($sTitle);
		return $sTitle;
	}

	public function getTitle() {
		return $this->excel->getActiveSheet()->getTitle();
	}

	protected function SetSheet($mSheet) {
		if(\is_int($mSheet)) {
			$nSheets = $this->excel->getSheetCount();
			if(($nSheets-1)<$mSheet) {
				$this->excel->createSheet($mSheet);
			}
			$this->excel->setActiveSheetIndex($mSheet);
		} else {
			$bCreate = true;
			$aSheets = $this->excel->getSheetNames();
			foreach($aSheets as $nIndex => $sSheet) {
				if($mSheet==$sSheet) {
					$this->excel->setActiveSheetIndex($nIndex);
					$bCreate = false;
					break;
				}
			}
			if($bCreate) {
				$nIndex++;
				$this->excel->createSheet($nIndex);
				$this->excel->setActiveSheetIndex($nIndex);
				$this->excel->getActiveSheet()->setTitle($mSheet);
			}
		}
		
		$this->nMaxRow = $this->excel->getActiveSheet()->getHighestRow(); 
		$this->sMaxCol = $this->excel->getActiveSheet()->getHighestColumn();
		
		return $mSheet;
	}
	
	private function FileType($sFileName) {
		$sType = \strtolower(\pathinfo($sFileName, PATHINFO_EXTENSION));
		$aTypes = [
			"csv"	=> ["Csv", "text/csv"],
			"html"	=> ["Html", "text/html"],
			"ods"	=> ["Ods", "application/vnd.oasis.opendocument.spreadsheet"],
			"xls"	=> ["Xls", "application/vnd.ms-excel"],
			"xlsx"	=> ["Xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"],
			"xml"	=> ["Xml", "application/xml "]
		];

		return (isset($aTypes[$sType])) ? $aTypes[$sType] : $aTypes["xls"];
	}


	private function GetStyles($mRules) {
		if(\is_array($mRules)) {
			$aRules = $mRules;
		} else {
			$aTmp = self::call()->explodeTrim(";", $mRules);
			$aRules = [];
			foreach($aTmp as $sRule) {
				$aPair = self::call()->explodeTrim(":", $sRule);
				$aRules[$aPair[0]] = $aPair[1];
			}
		}

		$aCSS = [];
		foreach($aRules as $sKey => $mValue) {
			$aCSS[\strtolower($sKey)] = $mValue;
		}
		unset($aRules);

		$aStyles = ["alignment" => [], "font" => [], "borders" => [], "sizes" => []];

		// text-alignment
		if(isset($aCSS["text-align"])) {
			switch($aCSS["text-align"]) {
				case "left": $aStyles["alignment"]["horizontal"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT; break;
				case "center": $aStyles["alignment"]["horizontal"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER; break;
				case "right": $aStyles["alignment"]["horizontal"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT; break;
				case "justify": $aStyles["alignment"]["horizontal"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_JUSTIFY; break;
			}
		}

		if(isset($aCSS["vertical-align"])) {
			switch ($aCSS["vertical-align"]) {
				case "top": $aStyles["alignment"]["vertical"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP; break;
				case "middle": $aStyles["alignment"]["vertical"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER; break;
				case "bottom": $aStyles["alignment"]["vertical"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM; break;
				case "justify": $aStyles["alignment"]["vertical"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_JUSTIFY; break;
				case "general": $aStyles["alignment"]["vertical"] = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_JUSTIFY; break;

			}
		}

		// background-color
		if(isset($aCSS["background"])) { $aCSS["background-color"] = $aCSS["background"]; unset($aCSS["background"]); }
		if(isset($aCSS["background-color"])) {
			$aStyles["fill"] = [
				"type"  => PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				"color" => ["rgb" => \str_replace("#", "", $aCSS["background-color"])],
			];
		}

		// font-size
		if(isset($aCSS["font-size"])) { $aStyles["font"]["size"] = (float)$aCSS["font-size"]; }

		// font-weight
		if(isset($aCSS["font-weight"])) { $aStyles["font"][$aCSS["font-weight"]] = true; }

		// font-color
		if(isset($aCSS["color"])) { $aStyles["font"]["color"] = ["rgb" => \str_replace("#", "", $aCSS["color"])]; }

		// border
		if(isset($aCSS["border"])) {
			$aBorder = $this->StylesBorders($aCSS["border"]);
			$aStyles["borders"] = [
				"bottom" => $aBorder,
				"left"   => $aBorder,
				"top"    => $aBorder,
				"right"  => $aBorder,
			];
		}

		if(isset($aCSS["border-top"])) {
			$aBorder = $this->StylesBorders($aCSS["border-top"]);
			$aStyles["borders"]["top"] = $aBorder;
		}

		if(isset($aCSS["border-right"])) {
			$aBorder = $this->StylesBorders($aCSS["border-right"]);
			$aStyles["borders"]["right"] = $aBorder;
		}

		if(isset($aCSS["border-bottom"])) {
			$aBorder = $this->StylesBorders($aCSS["border-bottom"]);
			$aStyles["borders"]["bottom"] = $aBorder;
		}

		if(isset($aCSS["border-left"])) {
			$aBorder = $this->StylesBorders($aCSS["border-left"]);
			$aStyles["borders"]["left"] = $aBorder;
		}

		if(isset($aCSS["width"])) {
			$aStyles["sizes"]["width"] = (\trim(\strtolower($aCSS["width"]))=="auto") ? "auto" : \preg_replace("/[^0-9]/", "", $aCSS["width"]);
		}

		if(isset($aCSS["height"])) {
			$aStyles["sizes"]["height"] = (\trim(\strtolower($aCSS["height"]))=="auto") ? -1 : \preg_replace("/[^0-9]/", "", $aCSS["height"]);
		}

		if(!\count($aStyles["alignment"])) { unset($aStyles["alignment"]); }
		if(!\count($aStyles["font"])) { unset($aStyles["font"]); }
		if(!\count($aStyles["borders"])) { unset($aStyles["borders"]); }
		if(!\count($aStyles["sizes"])) { unset($aStyles["sizes"]); }

		return $aStyles;
	}

	private function StylesBorders($sBorder) {
		$aBorderParts = \explode(" ", $sBorder);
		$aBorder = [];
		foreach($aBorderParts as $sPart) {
			$sPart = \trim(\strtolower($sPart));
			switch($sPart) {
				case "none": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE; break;
				case "dashdot": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHDOT; break;
				case "dashdotdot": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHDOTDOT; break;
				case "dashed": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED; break;
				case "dotted": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOTTED; break;
				case "double": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE; break;
				case "hair": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR; break;
				case "medium": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM; break;
				case "mediumdashdot": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUMDASHDOT; break;
				case "mediumdashdotdot": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUMDASHDOTDOT; break;
				case "mediumdashed": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUMDASHED; break;
				case "slantdashdot": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_SLANTDASHDOT; break;
				case "thick": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK; break;
				case "thin": $aBorder["style"] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN; break;
				default: $aBorder["color"] = ["rgb" => \str_replace("#", "", $sPart)]; break;
			}
		}
		return $aBorder;
	}
}

?>