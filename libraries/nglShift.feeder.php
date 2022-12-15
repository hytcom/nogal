<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# shift
https://hytcom.net/nogal/docs/objects/shift.md
*/
namespace nogal;
class nglShift extends nglTrunk {

	protected $class			= "nglShift";
	protected $me				= "shift";
	protected $object			= "shift";
	private $xpath				= null;
	private $vCSV;

	function __builder__() {
		$this->vCSV["source"]		= null;
		$this->vCSV["colnames"]		= null;
		$this->vCSV["use_colnames"]	= false;
		$this->vCSV["chk_splitter"]	= null;
		$this->vCSV["chk_enclosed"]	= null;
		$this->vCSV["chk_escaped"]	= null;
		$this->vCSV["chk_eol"]		= null;
		$this->vCSV["splitter"]		= null;
		$this->vCSV["enclosed"]		= null;
		$this->vCSV["escaped"]		= null;
		$this->vCSV["eol"]			= null;
		$this->vCSV["length"]		= null;
		$this->vCSV["pointer"]		= null;
	}

	public function cast($mValue, $sCastType="text") {
		$sType = \gettype($mValue);

		if(!\in_array($sType, ["array","boolean","double","integer","NULL","object","string"])) { return true; }
		if(\is_object($mValue)) { $mValue = $this->objToArray($mValue); }

		$sCastType = \strtolower($sCastType);
		if(!\is_array($mValue)) {
			return $this->CastValue($mValue, $sCastType);
		} else {
			$aCleanData = [];
			foreach($mValue as $mIndex => $mValue) {
				$aCleanData[$mIndex] = (\is_array($mValue)) ? $this->cast($mValue, $sCastType) : $this->CastValue($mValue, $sCastType);
			}
			return $aCleanData;
		}
	}

	private function CastValue($mValue, $sCastType) {
		switch($sCastType) {
			case "html":
				return \htmlspecialchars($mValue, ENT_QUOTES, \strtoupper(NGL_CHARSET));
				break;

			case "htmlall":
				return \htmlentities($mValue, ENT_QUOTES, \strtoupper(NGL_CHARSET));
				break;

			default:
				return $mValue;
				break;
		}
	}

	public function convert($mData, $sMethod=null, $vOptions=null) {
		if(empty($sMethod)) { $sMethod = "object-array"; }
		$sMethod = \strtolower($sMethod);
		$aMethod = \explode("-", $sMethod);

		// source
		switch($aMethod[0]) {
			case "csv":
				$aData = $this->csvToArray($mData, $vOptions);
				break;

			case "text":
			case "fixed":
				$aData = $this->fixedExplode($mData, $vOptions);
				break;

			case "ttable":
			case "texttable":
				$aData = $this->textTableToArray($mData, $vOptions);
				break;

			case "html":
				$aData = $this->htmlToArray($mData);
				break;

			case "json":
				$nBackTrack = \ini_get("pcre.backtrack_limit");
				\ini_set("pcre.backtrack_limit", ($nBackTrack+\strlen($mData)));
				$aData = $this->jsonDecode($mData);
				\ini_set("pcre.backtrack_limit", $nBackTrack);
				$aData = $this->objToArray($aData);
				break;

			case "object":
				if(\method_exists($mData, "getall")) {
					$aData = $mData->getall();
				} else {
					$aData = $this->objToArray($mData);
				}
				break;

			case "serialize":
				$aData = \unserialize($mData);
				if(!\is_array($aData)) {
					if(\is_object($aData)) {
						$aData = $this->objToArray($aData);
					}
				}
				break;

			case "xml":
				$aData = $this->xmlToArray($mData, $vOptions);
				$aData = \current($aData);
				break;

			case "yml":
			case "yaml":
				if(!\function_exists("yaml_parse")) { $this->__errorMode__("die"); self::errorMessage($this->object, 1001); }
				$mData = \preg_replace(["/^\t/is", "/\t+/"], "  ", $mData);
				$aData = \yaml_parse($mData);
				break;

			case "vector":
				$aData = [];
				$aData = $this->vectorToArray($mData);
				break;

			case "array":
			default:
				$aData = $mData;
		}

		if(!\is_array($aData)) { $aData = [$aData]; }

		// destine
		switch($aMethod[1]) {
			case "csv":
				return $this->csvEncode($aData, $vOptions);

			case "text":
			case "fixed":
				return $this->fixedImplode($aData, $vOptions);

			case "ttable":
			case "texttable":
				return $this->textTable($aData, $vOptions);

			case "html":
				return $this->html($aData, $vOptions);

			case "sql":
				return $this->sql($vOptions["table"], $aData, $vOptions);

			case "json":
				return $this->jsonEncode($aData, $vOptions);

			case "object":
				return $aData = $this->objToArray($aData);

			case "serialize":
				return \serialize($aData);

			case "xml":
				return $this->xmlEncode($aData, $vOptions);

			case "yml":
			case "yaml":
				if(!\function_exists("yaml_emit")) { $this->__errorMode__("die"); self::errorMessage($this->object, 1001); }
				return \yaml_emit($aData);

			case "array":
			default:
				return $aData;
		}

	}

	public function vectorToArray($aVector) {
		$aArray = [];
		foreach($aVector as $sKey => $mItem) {
			if(\is_array($mItem) && !self::call()->isarrayarray($mItem, "any")) { $mItem = $this->vectorToArray($mItem); }
			$aArray[] = ["key"=>$sKey, "value"=>$mItem];
		}
		return $aArray;
	}

	public function csvEncode($aData, $vOptions) {
		$aColnames	 	= (isset($vOptions["colnames"])) ? $vOptions["colnames"] : null;
		$sJoiner 		= (isset($vOptions["joiner"])) ? $vOptions["joiner"] : ",";
		$sEnclosed	 	= (isset($vOptions["enclose"])) ? $vOptions["enclose"] : '"';
		$sEscaped	 	= (isset($vOptions["escape"])) ? $vOptions["escape"] : "\\";
		$sEOL			= (isset($vOptions["eol"])) ? $vOptions["eol"] : "\r\n";
		$sArrayArray	= (isset($vOptions["arrayarray"])) ? $vOptions["arrayarray"] : "any";

		$sCSV = "";
		if(!\is_array($aData)) { return ""; }

		if($aColnames) {
			if(!\is_array($aColnames) && self::call()->isTrue($aColnames)) { $aColnames = \array_keys(\array_shift($aData)); }
			foreach($aColnames as $mColumnKey => $sColumn) {
				$sColumn = \str_replace($sEnclosed, $sEscaped.$sEnclosed, $sColumn);
				$sColumn = \str_replace($sJoiner, $sEscaped.$sJoiner, $sColumn);
				$aColnames[$mColumnKey] = $sEnclosed.$sColumn.$sEnclosed;
			}

			$sCSV .= \implode($sJoiner, $aColnames).$sEOL;
		}

		if(self::call()->isarrayarray($aData, $sArrayArray)) {
			\reset($aData);
			foreach($aData as $mLineKey => $aLine) {
				foreach($aLine as $mColumnKey => $sColumn) {
					$sColumn = \str_replace($sEnclosed, $sEscaped.$sEnclosed, $sColumn);
					$sColumn = \str_replace($sJoiner, $sEscaped.$sJoiner, $sColumn);
					$aLine[$mColumnKey] = $sEnclosed.$sColumn.$sEnclosed;
				}
				$aData[$mLineKey] = \implode($sJoiner, $aLine);
			}

			$sCSV .= \implode($sEOL, $aData);
		} else {
			foreach($aData as $mColumnKey => $sColumn) {
				$sColumn = \str_replace($sEnclosed, $sEscaped.$sEnclosed, $sColumn);
				$sColumn = \str_replace($sJoiner, $sEscaped.$sJoiner, $sColumn);
				$aData[$mColumnKey] = $sEnclosed.$sColumn.$sEnclosed;
			}

			$sCSV .= \implode($sJoiner, $aData);
		}

		return $sCSV;
	}

	private function CSVParseLine($sSplitter, $sEnclosed, $sEscaped, $sEOL) {
		$aLine			= [];
		$sData			= "";
		$bEnclosed		= false;

		$x = -1;
		$sChar = "\x0B";
		while(1) {
			$sLastChar = $sChar;
			$this->vCSV["pointer"]++;
			if($this->vCSV["length"]<=$this->vCSV["pointer"]) { break; }
			$sChar = $this->vCSV["source"][$this->vCSV["pointer"]];

			$this->vCSV["chk_enclosed"] = ($this->vCSV["enclosed"]) ? $sChar : self::call()->strBoxAppend($this->vCSV["chk_enclosed"], $sChar);
			if($this->vCSV["chk_enclosed"]===$sEnclosed) {
				if($bEnclosed) {
					$bEnclosed = false;
					$sChar = "";
				} else {
					$bEnclosed = true;
					continue;
				}
			}

			$this->vCSV["chk_splitter"] = ($this->vCSV["splitter"]) ? $sChar : self::call()->strBoxAppend($this->vCSV["chk_splitter"], $sChar);
			$this->vCSV["chk_escaped"]	= ($this->vCSV["chk_enclosed"]) ? $sLastChar : self::call()->strBoxAppend($this->vCSV["chk_escaped"], $sLastChar);
			$this->vCSV["chk_eol"]		= ($this->vCSV["eol"]) ? $sChar : self::call()->strBoxAppend($this->vCSV["chk_eol"], $sChar);

			if($this->vCSV["chk_splitter"]===$sSplitter && $this->vCSV["chk_escaped"]!==$sEscaped && !$bEnclosed) {
				if($this->vCSV["use_colnames"] && \count($this->vCSV["colnames"])) {
					if(isset($this->vCSV["colnames"][\count($aLine)])) {
						$aLine[$this->vCSV["colnames"][\count($aLine)]] = $sData;
					}
				} else {
					$aLine[] = $sData;
				}
				$sData = "";
			} else {
				$sData .= $sChar;
			}

			if($this->vCSV["chk_eol"]===$sEOL) {
				$sData = \substr($sData, 0, \strlen($sEOL)*-1);
				if($this->vCSV["use_colnames"] && \count($this->vCSV["colnames"])) {
					if(isset($this->vCSV["colnames"][\count($aLine)])) {
						$aLine[$this->vCSV["colnames"][\count($aLine)]] = $sData;
					}
				} else {
					$aLine[] = $sData;
				}
				return $aLine;
			}
		}

		if($sData!=="") {
			$aLine[] = $sData;
			return $aLine;
		}

		return null;
	}

	public function csvToArray($sSource, $vOptions=[]) {
		$bColnames		= (isset($vOptions["use_colnames"])) ? self::call()->isTrue($vOptions["use_colnames"]) : false;
		$aColnames		= (isset($vOptions["colnames"])) ? $vOptions["colnames"] : [];
		$sSplitter	 	= (isset($vOptions["splitter"])) ? $vOptions["splitter"] : ",";
		$sEnclosed		= (isset($vOptions["enclosed"])) ? $vOptions["enclosed"] : "\"";
		$sEscaped	 	= (isset($vOptions["escaped"])) ? $vOptions["escaped"] : "\\";
		$sEOL			= (isset($vOptions["eol"])) ? $vOptions["eol"] : "\n";

		if(\is_array($aColnames) && \count($aColnames)) { $bColnames = true; }

		$this->vCSV["use_colnames"]		= $bColnames;
		$this->vCSV["colnames"]			= $aColnames;
		$this->vCSV["chk_splitter"]		= \str_pad("", \strlen($sSplitter), "\x0B");
		$this->vCSV["chk_enclosed"]		= \str_pad("", \strlen($sEnclosed), "\x0B");
		$this->vCSV["chk_escaped"]		= \str_pad("", \strlen($sEscaped), "\x0B");
		$this->vCSV["chk_eol"]			= \str_pad("", \strlen($sEOL), "\x0B");
		$this->vCSV["splitter"]			= ($this->vCSV["chk_splitter"]==="\x0B");
		$this->vCSV["enclosed"]			= ($this->vCSV["chk_enclosed"]==="\x0B");
		$this->vCSV["escaped"]			= ($this->vCSV["chk_escaped"]==="\x0B");
		$this->vCSV["eol"]				= ($this->vCSV["chk_eol"]==="\x0B");
		$this->vCSV["source"] 			= $sSource;
		$this->vCSV["length"] 			= \strlen($sSource);
		$this->vCSV["pointer"] 			= -1;

		$aCSV = [];
		while(1) {
			$aLine = null;
			if($this->vCSV["use_colnames"] && !\count($this->vCSV["colnames"])) {
				$aLine = $this->CSVParseLine($sSplitter, $sEnclosed, $sEscaped, $sEOL);
				$this->vCSV["colnames"] = $aLine;
				continue;
			}

			$aLine = $this->CSVParseLine($sSplitter, $sEnclosed, $sEscaped, $sEOL);
			if(!$aLine) { break; }
			$aCSV[] = $aLine;
		}

		return $aCSV;
	}

	public function fixedExplode($sString, $vOptions=null) {
		if(!isset($vOptions["positions"]) || !\is_array($vOptions["positions"]) || !\count($vOptions["positions"])) {
			return [$sString];
		}

		$sEOL = (isset($vOptions["eol"])) ? $vOptions["eol"] : false;
		$aString = ($sEOL) ? self::call()->strToArray($sString, $sEOL) : array($sString);

		$aExplode = [];
		$bTrim = (isset($vOptions["trim"]) && $vOptions["trim"]);
		foreach($aString as $sLine) {
			$nLen = 0;
			$aLine = [];
			foreach($vOptions["positions"] as $nIndex) {
				$aLine[] = ($bTrim) ? \trim(\substr($sLine, $nLen, $nIndex)) : \substr($sLine, $nLen, $nIndex);
				$nLen += $nIndex;
			}

			$aExplode[] = $aLine;
		}

		return ($sEOL) ? $aExplode : $aExplode[0];
	}

	public function fixedImplode($aString, $vOptions=null) {
		$sFill			= (isset($vOptions["fill"])) ? $vOptions["fill"] : " ";
		$sJoiner		= (isset($vOptions["joiner"])) ? $vOptions["joiner"] : null;
		$nJoiner		= (isset($vOptions["joiner"])) ? \strlen($sJoiner) : 0;
		$sEOL			= (isset($vOptions["eol"])) ? $vOptions["eol"] : "\n";
		$sArrayArray	= (isset($vOptions["arrayarray"])) ? $vOptions["arrayarray"] : "any";

		$bRecursive = self::call()->isarrayarray($aString, $sArrayArray);

		if(!isset($vOptions["positions"]) || !\is_array($vOptions["positions"]) || !\count($vOptions["positions"])) {
			if($bRecursive) {
				$aCurrent = \current($aString);
				\reset($aString);
			} else {
				$aCurrent = $aString;
			}

			$aPositions = [];
			foreach($aCurrent as $mValue) {
				$aPositions[] = self::call("unicode")->strlen($mValue);
			}

			$vOptions["positions"] = $aPositions;
		}

		$sString = "";
		if($bRecursive) {
			foreach($aString as $aLine) {
				$sString .= $this->fixedImplode($aLine, $vOptions).$sEOL;
			}
		} else {
			$aString = \array_values($aString);
			$nPositions = \count($vOptions["positions"]);
			for($x=0;$x<$nPositions;$x++) {
				$nLen = (isset($vOptions["positions"][$x])) ? $vOptions["positions"][$x] : $nPositions;
				$sValue = \substr($aString[$x],0,$nLen);
				$sValue = \str_pad($sValue, $nLen, $sFill);
				if($sJoiner!==null) { $sValue = \substr($sValue, 0, $nJoiner*-1).$sJoiner; }
				$sString .= $sValue;
			}
		}

		return $sString;
	}

	public function jsObject($aData, $sArrayArray="any") {
		$aValues = [];
		if(\is_array($aData) && \count($aData) && self::call()->isarrayarray($aData, $sArrayArray)) {
			$aFirst = \current($aData);
			$aColnames = array_keys($aFirst);
			$aRegexs = self::call("sysvar")->REGEX;
			$aTypes = [];
			$x = 0;
			foreach($aFirst as $mVal) {
				if(\is_numeric($mVal)) {
					$aTypes[] = ["type"=>"number", "label"=>$aColnames[$x]];
				} else if(\is_bool($mVal) || \in_array(\strtolower($mVal), ["false", "true"])) {
					$aTypes[] = ["type"=>"boolean", "label"=>$aColnames[$x]];
				} else if(\preg_match("/".$aRegexs["datetime"]."/i", $mVal)) {
					$aTypes[] = ["type"=>"datetime", "label"=>$aColnames[$x]];
				} else if(\preg_match("/".$aRegexs["date"]."/i", $mVal)) {
					$aTypes[] = ["type"=>"date", "label"=>$aColnames[$x]];
				} else {
					$aTypes[] = ["type"=>"string", "label"=>$aColnames[$x]];
				}
				$x++;
			}

			foreach($aData as $aRow) {
				$x = 0;
				$aNewData = [];
				foreach(\array_values($aRow) as $mVal) {
					if($aTypes[$x]["type"]=="date") {
						$aDate = \explode("-", $mVal);
						$aNewData[] = (isset($aDate[2])) ? "new Date(".$aDate[0].",".$aDate[1].",".$aDate[2].")" : "null";
					} else if($aTypes[$x]["type"]=="datetime") {
						$aDateTime = \explode(" ", $mVal);
						$aDate = \explode("-", $aDateTime[0]);
						$aTime = \explode(":", $aDateTime[1]);
						$aNewData[] = (isset($aDate[2], $aTime[2])) ? "new Date(".$aDate[0].",".$aDate[1].",".$aDate[2].",".$aTime[0].",".$aTime[1].",".$aTime[2].")" : "null";;
					} else if($aTypes[$x]["type"]=="boolean") {
						$aNewData[] = self::call()->isTrue($mVal) ? "true" : "false";
					} else if($mVal===null || \strtolower($mVal)=="null") {
						$aNewData[] = "null";
					} else if($aTypes[$x]["type"]=="number") {
						$aNewData[] = self::call()->isInteger($mVal) ? "parseInt(".(int)$mVal.")" : "parseFloat(".$mVal.")";
					} else {
						$aNewData[] = $mVal;
					}
					$x++;
				}
				$aValues[] = $aNewData;
			}
		}

		foreach($aTypes as $k => $sType) {

		}

		$sTypes = \json_encode($aTypes);
		$sValues = \json_encode($aValues, JSON_NUMERIC_CHECK);
		$sValues = preg_replace("/\"(new Date\([0-9,]+\)|parseInt\([0-9]+\)|parseFloat\([0-9\.]+\)|null|true|false)\"/is", "\\1", $sValues);
		return ["columns"=>$sTypes, "data"=>$sValues];
	}

	public function sql($sTable, $aData, $vOptions=[]) {
		$sColQuote	= (isset($vOptions["sql_colquote"])) ? $vOptions["sql_colquote"] : "";
		$sQuote	= (isset($vOptions["sql_quote"])) ? $vOptions["sql_quote"] : '"';
		$nRowByInsert = (isset($vOptions["sql_inserts"])) ? $vOptions["sql_inserts"] : 1;

		$sSQL = "";
		$y = 0;
		$x = 0;
		foreach($aData as $aRow) {
			if($x==0) {
				$sSQL .= "INSERT INTO ".$sColQuote.$sTable.$sColQuote." (".$sColQuote.\implode($sColQuote.",".$sColQuote, \array_keys($aRow)).$sColQuote.") VALUES ";
			}

			$aValues[] = "(".$sQuote.\implode($sQuote.",".$sQuote, $aRow).$sQuote.")";
			if(++$x==$nRowByInsert) {
				$sSQL .= \implode(", ", $aValues).";\n";
				$aValues = [];
				$x = 0;
			}
		}
		if(\count($aValues)) { $sSQL .= \implode(",", $aValues).";\n"; }

		return $sSQL;
	}

	public function html($aData=null, $vOptions=[]) {
		$bHTMLEntities	= (isset($vOptions["entities"])) ? $vOptions["entities"] : true;
		$sFormat		= (isset($vOptions["format"])) ? $vOptions["format"] : "table";
		$sClassName		= (isset($vOptions["class"])) ? $vOptions["class"] : "class";
		$sClasses		= (isset($vOptions["classes"])) ? $vOptions["classes"] : "";
		$sArrayArray	= (isset($vOptions["arrayarray"])) ? $vOptions["arrayarray"] : "any";
		$nBorder		= (isset($vOptions["border"])) ? $vOptions["border"] : 0;

		$sFormat = \strtolower($sFormat);
		switch($sFormat) {
			case "div":
				$sTagTable 	= "div";
				$sTagRow 	= "div";
				$sTagHeader	= "div";
				$sTagCell 	= "div";
				break;

			case "list":
				$sTagTable 	= "ul";
				$sTagRow 	= "li";
				$sTagHeader	= "span";
				$sTagCell 	= "span";
				break;

			case "table":
			default:
				$sTagTable 	= "table";
				$sTagRow 	= "tr";
				$sTagHeader	= "th";
				$sTagCell	= "td";
				break;
		}

		$sHTML = "<".$sTagTable." border=\"".$nBorder."\" class=\"".$sClassName." ".$sClasses."\">\n";

		// contenido del bloque
		if(self::call()->isarrayarray($aData, $sArrayArray)) {
			// cabeceras
			$aHeaders = [];
			foreach($aData as $aRow) {
				if(\is_array($aRow) && (\count($aRow) > \count($aHeaders))) { $aHeaders = $aRow; }
			}

			$aColumns = \array_keys($aHeaders);
			$nColumns = \count($aColumns);
			$sHTML .= "\t<".$sTagRow." class=\"".$sClassName."-head\">\n";
			foreach($aColumns as $sColumn) {
				$sHTML .= "\t\t<".$sTagHeader." class=\"".$sClassName."-head-cell\">".$sColumn."</".$sTagHeader.">\n";
			}
			$sHTML .= "\t</".$sTagRow.">\n";

			// datos
			foreach($aData as $mRow) {
				$sHTML .= "\t<".$sTagRow." class=\"".$sClassName."-row\">\n";
				if(is_array($mRow)) {
					foreach($mRow as $r => $mValue) {
						if(\is_array($mValue)) {
							$sHTML .= "\t\t<".$sTagCell." class=\"".$sClassName."-cell\">".$this->html($mValue, $vOptions)."</".$sTagCell.">\n";
						} else {
							$sHTML .= "\t\t<".$sTagCell." class=\"".$sClassName."-cell\">".$this->HTMLPrint($mValue, $bHTMLEntities)."</".$sTagCell.">\n";
						}
					}
				} else {
					$sHTML .= "\t\t<".$sTagCell." class=\"".$sClassName."-cell\">".$this->HTMLPrint($mRow, $bHTMLEntities)."</".$sTagCell.">\n";
				}
				$sHTML .= "\t</".$sTagRow.">\n";
			}
		} else {
			if(\is_array($aData) && \count($aData)) {
				foreach($aData as $sField => $mValue) {
					$sHTML .= "\t<".$sTagRow." class=\"".$sClassName."-head\">\n";
					$sHTML .= "\t\t<".$sTagHeader." class=\"".$sClassName."-head-cell\">".$sField."</".$sTagHeader.">\n";
					if(\is_array($mValue)) {
						$sHTML .= "\t\t<".$sTagCell." class=\"".$sClassName."-cell\">".$this->html($mValue, $vOptions)."</".$sTagCell.">\n";
					} else {
						$sHTML .= "\t\t<".$sTagCell." class=\"".$sClassName."-cell\">".$this->HTMLPrint($mValue, $bHTMLEntities)."</".$sTagCell.">\n";
					}
					$sHTML .= "\t</".$sTagRow.">\n";
				}
			} else {
				$sHTML .= "\t<".$sTagRow." class=\"".$sClassName."-row\">\n";
				$sHTML .= "\t\t<".$sTagCell." class=\"".$sClassName."-cell\">NULL</".$sTagCell.">\n";
				$sHTML .= "\t</".$sTagRow.">\n";
			}
		}

		// cierre del bloque
		$sHTML .= "</".$sTagTable.">\n";

		return $sHTML;
	}

	private function HTMLPrint($sCode, $bHTMLEntities) {
		if($sCode===null) { return "NULL"; }
		if($sCode===false) { return "FALSE"; }
		if($sCode===true) { return "TRUE"; }
		$sCode = \trim($sCode);
		if($sCode=="") { return "&nbsp;"; }
		return $bHTMLEntities ? \htmlentities($sCode) : $sCode;
	}

	public function htmlToArray($sHTML) {
		$doc = new \DOMDocument;
		$doc->loadHTML($sHTML);
		$this->xpath = new \DOMXPath($doc);
		$tables =  $this->xpath->query("body/table");
		$aTables = [];
		foreach($tables as $table) {
			$aTables[] = $this->HTMLTableParser($table);
		}

		return (\count($aTables)==1) ? \current($aTables) : $aTables;
	}

	private function HTMLTableParser($table) {
		$aHeaders = null;
		$thead = $this->xpath->query("thead", $table);
		if($thead->length) {
			$headers = $this->xpath->query("tr/th", $thead->item(0));
			if($headers->length) {
				$aHeaders = [];
				foreach($headers as $header) {
					$aHeaders[] = \trim($header->nodeValue);
				}
			}
		}

		$tbody = $this->xpath->query("tbody", $table);
		if($tbody->length) { $table = $tbody->item(0); }

		$aTable = [];
		foreach($this->xpath->query("tr", $table) as $row) {
			$aRow = [];
			foreach($this->xpath->query("td", $row) as $sColnameey => $cell) {
				$sIndex = ($aHeaders && isset($aHeaders[$sColnameey])) ? $aHeaders[$sColnameey] : $sColnameey;
				$subtables = $this->xpath->query("table", $cell);
				if($subtables->length>0) {
					$aSubtables = [];
					foreach($subtables as $subtable) {
						$aSubtables[] = $this->HTMLTableParser($subtable);
					}
					$aRow[$sIndex] = $aSubtables;
				} else {
					$aRow[$sIndex] = \trim($cell->nodeValue);
				}
			}
			$aTable[] = $aRow;
		}

		return $aTable;
	}

	private function JSONChar($sChar, $bUTF8=false, $bConverSpaces=false, $bAddSlashes=false) {
		if($bConverSpaces) {
			$nOrd = \ord($sChar);
			switch(true) {
				case $nOrd == 0x08:
					return "\\b";
				case $nOrd == 0x09:
					return "\\t";
				case $nOrd == 0x0A:
					return "\\n";
				case $nOrd == 0x0C:
					return "\\f";
				case $nOrd == 0x0D:
					return "\\r";
			}
		}

		if($bAddSlashes) {
			$nOrd = \ord($sChar);
			switch(true) {
				case $nOrd == 0x22:
				//case $nOrd == 0x27:
				case $nOrd == 0x2F:
				case $nOrd == 0x5C: /* double quote, slash, slosh */
					return "\\".$sChar;
			}
		}

		if(!$bUTF8) {
			return $sChar;
		} else {
			return "\\u".\str_pad(\dechex(self::call("unicode")->ord($sChar)), 4, "0", STR_PAD_LEFT);
		}
	}

	public function jsonDecode($sString) {
		$sString = $this->JSONReduceString($sString);

		switch(\strtolower($sString)) {
			case "true":
				return true;

			case "false":
				return false;

			case "null":
				return null;

			default:
				$sString = $this->JSONReduceString($sString);

				switch(\strtolower($sString)) {
					case "true":
						return true;

					case "false":
						return false;

					case "null":
						return null;

					default:
						$aDecoded = [];
						if(\is_numeric($sString)) {
							return ((float)$sString==(integer)$sString) ? (integer)$sString : (float)$sString;
						} else if(\preg_match("/^(\"|').*(\\1)$/s", $sString, $aDecoded) && $aDecoded[1]==$aDecoded[2]) {
							$sQuote = \substr($sString, 0, 1);
							$sString = \substr($sString, 1, -1);
							$sString = self::call("unicode")->unescape($sString);
							$nString = \strlen($sString);

							$sUTF8 = "";
							for($x=0; $x<$nString; $x++) {
								$sSub2Chars = \substr($sString, $x, 2);
								switch(true) {
									case $sSub2Chars == "\\b":
										$sUTF8 .= \chr(0x08);
										$x++;
										break;
									case $sSub2Chars == "\\t":
										$sUTF8 .= \chr(0x09);
										$x++;
										break;
									case $sSub2Chars == "\\n":
										$sUTF8 .= \chr(0x0A);
										$x++;
										break;
									case $sSub2Chars == "\\f":
										$sUTF8 .= \chr(0x0C);
										$x++;
										break;
									case $sSub2Chars == "\\r":
										$sUTF8 .= \chr(0x0D);
										$x++;
										break;

									case $sSub2Chars == "\\\"":
									case $sSub2Chars == "\\'":
									case $sSub2Chars == "\\\\":
									case $sSub2Chars == "\\/":
										if(($sQuote=='"' && $sSub2Chars!="\\'") || ($sQuote=="'" && $sSub2Chars!='\\"')) {
											$sUTF8 .= $sString[++$x];
										}
										break;

									default:
										$sUTF8 .= $sString[$x];
										break;
								}
							}

							return $sUTF8;

						} else if(\preg_match("/^\[.*\]$/s", $sString) || \preg_match("/^\{.*\}$/s", $sString)) {
							if(!empty($sString) && $sString[0]=="[") {
								$aStakeState = [3];
								$aDecoded = [];
							} else {
								$aStakeState = [4];
								$aDecoded = [];
							}

							$aStakeState[] = [0=>1, 1=>0, 2=>false];

							$sString = \substr($sString, 1, -1);
							$sString = $this->JSONReduceString($sString);

							if($sString=="") {
								return $aDecoded;
							}

							$nString = \strlen($sString);
							for($x=0; $x <= $nString; ++$x) {
								$aStakeTop = \end($aStakeState);
								$sSub2Chars = \substr($sString, $x, 2);
								if(($x==$nString) || (($sString[$x]==",") && ($aStakeTop[0]==1))) {
									$sSlice = \substr($sString, $aStakeTop[1], ($x - $aStakeTop[1]));
									$aStakeState[] = [0=>1, 1=>($x + 1), 2=>false];

									if(\reset($aStakeState)==3) {
										$aDecoded[] = $this->jsonDecode($sSlice);
									} else if(\reset($aStakeState)==4) {
										$aParts = [];
										if(\preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $sSlice, $aParts)) {
											$mKey = $this->jsonDecode($aParts[1]);
											$mValue = $this->jsonDecode($aParts[2]);
											$aDecoded[$mKey] = $mValue;
										} else if(\preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $sSlice, $aParts)) {
											$mKey = $aParts[1];
											$mValue = $this->jsonDecode($aParts[2]);
											$aDecoded[$mKey] = $mValue;
										}
									}
								} else if((($sString[$x]=='"') || ($sString[$x]=="'")) && ($aStakeTop[0]!=2)) {
									$aStakeState[] = [0=>2, 1=>$x, 2=>$sString[$x]];
								} else if(($sString[$x] == $aStakeTop[2]) && ($aStakeTop[0] == 2) && ((\strlen(\substr($sString, 0, $x)) - \strlen(\rtrim(\substr($sString, 0, $x), "\\"))) % 2 != 1)) {
									\array_pop($aStakeState);
								} else if(($sString[$x]=="[") && \in_array($aStakeTop[0], [1, 3, 4])) {
									$aStakeState[] = [0=>3, 1=>$x, 2=>false];
								} else if(($sString[$x]=="]") && ($aStakeTop[0]==3)) {
									\array_pop($aStakeState);
								} else if(($sString[$x]=="{") && \in_array($aStakeTop[0], [1, 3, 4])) {
									$aStakeState[] = [0=>4, 1=>$x, 2=>false];
								} else if(($sString[$x]=="}") && ($aStakeTop[0]==4)) {
									\array_pop($aStakeState);
								} else if(($sSub2Chars=="/*") && \in_array($aStakeTop[0], [1, 3, 4])) {
									$aStakeState[] = [0=>5, 1=>$x, 2=>false];
									$x++;
								} else if(($sSub2Chars=="*/") && ($aStakeTop[0]==5)) {
									\array_pop($aStakeState);
									$x++;

									for($y=$aStakeTop[1]; $y<=$x; ++$y) {
										$sString = \substr_replace($sString, " ", $y, 1);
									}
								}
							}

							if(\reset($aStakeState)==3 || \reset($aStakeState)==4) {
								return $aDecoded;
							}
						}
				}
		}
	}

	public function jsonEncode($mValue, $vOptions=null) {
		$bConverUTF8 = (isset($vOptions["convert_unicode"])) ? self::call()->isTrue($vOptions["convert_unicode"]) : true;
		$bConverSpaces = (isset($vOptions["convert_spaces"])) ? self::call()->isTrue($vOptions["convert_spaces"]) : true;
		$bAddSlashes = (isset($vOptions["add_slashes"])) ? self::call()->isTrue($vOptions["add_slashes"]) : true;

		switch(\gettype($mValue)) {
			case "boolean":
				return ($mValue) ? "true" : "false";

			case "NULL":
				return "null";

			case "integer":
				return (int)$mValue;

			case "double":
			case "float":
				return (float)$mValue;

			case "string":
				$sASCII = "";
				$sChar = "";
				$bUTF8 = false;
				$nValue = \strlen($mValue);
				for($x=0; $x<$nValue; $x++) {
					if((\ord($mValue[$x])&0xC0)!=0x80) {
						if(\strlen($sChar)) {
							if(!$bConverUTF8) { $bUTF8 = false; }
							$sASCII .= $this->JSONChar($sChar, $bUTF8, $bConverSpaces, $bAddSlashes);
							$sChar = "";
							$bUTF8 = false;
						}
					} else {
						$bUTF8 = true;
					}

					$sChar .= $mValue[$x];
				}

				if(!$bConverUTF8) { $bUTF8 = false; }
				$sASCII .= $this->JSONChar($sChar, $bUTF8, $bConverSpaces, $bAddSlashes);
				return '"'.$sASCII.'"';

			case "array":
				if(\is_array($mValue) && \count($mValue) && (\array_keys($mValue)!==\range(0, \count($mValue)-1))) {
					$aProperties = \array_map([$this, "JSONNameValuePair"], \array_keys($mValue), \array_values($mValue));
					$sProperties = "{".\implode(",", $aProperties)."}";
					return $sProperties;
				}

				if(!\is_array($vOptions)) { $vOptions = []; }
				$aElements = \array_map([$this, "jsonEncode"], $mValue, $vOptions);
				return "[".\implode(",", $aElements)."]";

			case "object":
				$mValues = \get_object_vars($mValue);

				$aProperties = \array_map([$this, "JSONNameValuePair"], \array_keys($mValues), \array_values($mValues));
				return "{".\implode(",", $aProperties)."}";
		}
	}

	public function jsonFormat($sJson, $bCompress=false, $bHTML=false, $sTab="\t", $sEOL="\n") {
		$sJson = \preg_replace("/[\n\r\t]/is", "", $sJson);
		if($bCompress) { return $sJson; }

		$sResult = "";
		for($x=$y=$z=0;isset($sJson[$x]);$x++) {
			if($sJson[$x]=='"' && ($x>0 ? $sJson[$x-1] : "")!="\\" && $y=!$y) {
				if(!$y && \strchr(" \t\n\r", $sJson[$x])){ continue; }
			}

			if(\strchr("}]", $sJson[$x]) && !$y && $z--) {
				if(!\strchr("{[", $sJson[$x-1])) { $sResult .= $sEOL.\str_repeat($sTab, $z); }
			}

			$sResult .= ($bHTML) ? \htmlentities($sJson[$x]) : $sJson[$x];
			if(\strchr(",{[", $sJson[$x]) && !$y) {
				$z += (\strchr("{[", $sJson[$x])===false) ? 0 : 1;
				if(!\strchr("}]", $sJson[$x+1])) { $sResult .= $sEOL.\str_repeat($sTab, $z); }
			}
		}

		return $sResult;
	}

	private function JSONNameValuePair($sName, $mValue) {
		$sName = $this->jsonEncode(\strval($sName));
		$sEncoded = $this->jsonEncode($mValue);
		return $sName.":".$sEncoded;
	}

	private function JSONReduceString($sString) {
		$sString = \preg_replace(["/^\s*\/\/(.+)$/m", "/^\s*\/\*(.+)\*\//Us", "/\/\*(.+)\*\/\s*$/Us"], "", $sString);
		return \trim($sString);
	}

	public function objToArray($mObject) {
		if(\is_object($mObject)) { $mObject = \get_object_vars($mObject); }

		$aArray = [];
		if(\is_array($mObject) && \count($mObject)) {
			foreach($mObject as $mKey => $mValue) {
				$mValue = (\is_array($mValue) || \is_object($mValue)) ? $this->objToArray($mValue) : $mValue;
				$aArray[$mKey] = $mValue;
			}
		}

		return $aArray;
	}

	public function objFromArray($aArray) {
		$oObject = new \stdClass();
		if(\is_array($aArray) && \count($aArray)) {
			foreach($aArray as $mKey => $mValue) {
				$oObject->{$mKey} = (\is_array($mValue) || \is_object($mValue)) ? $this->objFromArray($mValue) : $mValue;
			}
		}

		return $oObject;
	}

	private function XMLChildren($vXML, $bAttributes=false, &$x) {
		$aWithChildren = [];
		$vChildren = [];

		while($x++ < \count($vXML)-1) {
			$vNode = [];
			$sTagName = \strtolower($vXML[$x]["tag"]);

			switch($vXML[$x]["type"]) {
				case "complete":
					if($bAttributes) {
						$vNode["node"] = (isset($vXML[$x]["value"])) ? $vXML[$x]["value"] : [];
						$vNode["attributes"] = (isset($vXML[$x]["attributes"])) ? $vXML[$x]["attributes"] : [];
					} else {
						$vNode = (isset($vXML[$x]["value"])) ? $vXML[$x]["value"] : [];
					}

					// siempre usa el indice 0
					if(isset($vChildren[$sTagName])) {
						if(!\is_array($vChildren[$sTagName])) {
							$vChildren[$sTagName] = [$vChildren[$sTagName]];
						} else {
							if(\is_array($vChildren[$sTagName]) && \count($vChildren[$sTagName])==2 && isset($vChildren[$sTagName]["attributes"])) {
								$vChildren[$sTagName] = [$vChildren[$sTagName]];
							}
						}

						$vChildren[$sTagName][] = $vNode;
					} else {
						$vChildren[$sTagName] = $vNode;
					}


					break;

				case "open":
					if(isset($vXML[$x]["attributes"])) {
						$vNode["attributes"] = $vXML[$x]["attributes"];
						$vNode["node"] = $this->XMLChildren($vXML, $bAttributes, $x);
					} else {
						$vNode = $this->XMLChildren($vXML, $bAttributes, $x);
					}

					$vChildren[$sTagName][] = $vNode;

					break;

				case "close":
					return $vChildren;
			}
		}

		return $vChildren;
	}

	public function xmlEncode($aData, $vOptions=null) {
		$sTagName = (isset($vOptions["tag"])) ? $vOptions["tag"] : "";
		$nLevel = (isset($vOptions["level"])) ? $vOptions["level"] : -1;

		if(!empty($sTagName)) { $nLevel++; }

		$sTab = ($nLevel>=0) ? \str_repeat("\t", $nLevel) : "";

		$sXML = ($sTagName) ? $sTab."<".$sTagName." level=\"".$nLevel."\">\n" : "";
		foreach($aData as $mKey => $mValue) {
			if(\is_array($mValue)) {
				$sTag = (!\is_numeric($mKey)) ? $mKey : "row";
				$sXML .= $this->xmlEncode($mValue, ["tag"=>$sTag, "level"=>$nLevel]);
			} else {
				$sTag = (!\is_numeric($mKey)) ? $mKey : $sTagName.":".$mKey;
				$sXML .= $sTab."\t<".$sTag.">".((\is_numeric($mValue) || $mValue==="") ? $mValue : "<![CDATA[".$mValue."]]>")."</".$sTag.">\n";
			}
		}
		$sXML .= ($sTagName) ? $sTab."</".$sTagName.">\n" : "";

		if($nLevel>0) { $nLevel--; }
		unset($aData);

		return $sXML;
	}

	public function xmlToArray($sXML, $vOptions=null) {
		$bAttributes = (isset($vOptions["xml_attributes"])) ? self::call()->isTrue($vOptions["xml_attributes"]) : false;

		$hXML = \xml_parser_create();
		\xml_parser_set_option($hXML, XML_OPTION_CASE_FOLDING, 0);
		\xml_parser_set_option($hXML, XML_OPTION_SKIP_WHITE, 0);
		\xml_parse_into_struct($hXML, $sXML, $aValues, $aTags);
		\xml_parser_free($hXML);

		$x = -1;
		$aArray = $this->XMLChildren($aValues, $bAttributes, $x);

		return $aArray;
	}

	public function textTable($aData, $vOptions=[]) {
		$sArrayArray	= (isset($vOptions["arrayarray"])) ? $vOptions["arrayarray"] : "any";
		$sHeaderAlign	= (isset($vOptions["tthalign"])) ? \strtolower($vOptions["tthalign"]) : "center";
		$sBodyAlign		= (isset($vOptions["ttdalign"])) ? \strtolower($vOptions["ttdalign"]) : "left";
		$nHeaderAlign	= ($sHeaderAlign=="left") ? STR_PAD_RIGHT : ($sHeaderAlign=="right" ? STR_PAD_LEFT : STR_PAD_BOTH);
		$nBodyAlign		= ($sBodyAlign=="left") ? STR_PAD_RIGHT : ($sBodyAlign=="right" ? STR_PAD_LEFT : STR_PAD_BOTH);

		if(!self::call()->isarrayarray($aData, $sArrayArray)) { return ""; }

		$aCells = \array_fill_keys(\array_keys(\current($aData)), 0);
		$nCells = \count($aCells);
		foreach($aCells AS $sColname => $nLength) {
			$aCells[$sColname] = self::call("unicode")->strlen(\trim($sColname, "\t"));
		}
		foreach($aData as $i => $aRow) {
			foreach($aRow as $sColname=>$mCell) {
				if(\is_object($mCell)) {
					$sCell = "OBJECT: ".\json_encode($mCell);
				} else if(\is_array($mCell)) {
					$sCell = "ARRAY: ".\json_encode($mCell);
				} else {
					$sCell = \str_replace("\t", "\\t", $mCell);
				}
				$aCells[$sColname] = \max($aCells[$sColname], self::call("unicode")->strlen($sCell));
				$aData[$i][$sColname] = $sCell;
			}
		}

		$sBar = "+";
		$sHeader = "|";
		foreach($aCells as $sColname => $nLength) {
			$sBar .= \str_pad("", $nLength + 2, "-")."+";
			$sHeader .= " ".self::call("unicode")->strpad($sColname, $nLength, " ", $nHeaderAlign) . " |";
		}

		$aCells = \array_values($aCells);
		$sTable = "";
		$sTable .= $sBar."\n";
		$sTable .= $sHeader."\n";
		$sTable .= $sBar."\n";
		foreach($aData as $aRow) {
			$sTable .= "|";
			if(\is_array($aRow) && \count($aRow) < $nCells) { $aRow = \array_pad($aRow, $nCells, "NULL"); }
			$x = 0;
			foreach($aRow as $sCell) {
				$sCell = \trim($sCell, "\t");
				$sCell = \preg_replace('/[\x00-\x1F\x80-\xFF]+/', "?", $sCell);
				if($sCell===null) {
					$sCell = "NULL";
				} else if($sCell===false) {
					$sCell = "FALSE";
				} else if($sCell===true) {
					$sCell = "TRUE";
				}
				$sTable .= " ".self::call("unicode")->strpad($sCell, $aCells[$x], " ", $nBodyAlign) . " |";
				$x++;
			}
			$sTable .= "\n";
		}
		$sTable .= $sBar."\n";

		return $sTable;
	}

	public function textTableToArray($sTable, $vOptions=[]) {
		$bMultiline = (isset($vOptions["multiline"])) ? $vOptions["multiline"] : false;
		$aTable = \explode("\n", $sTable);
		\array_shift($aTable);
		$aHeaders = self::call()->explodeTrim("|", \array_shift($aTable));
		$aHeaders = \array_slice($aHeaders, 1, -1);

		if($bMultiline) {
			\array_shift($aTable);
			$aMultilineEmpty = $aMultiline = \array_fill_keys(\array_keys($aHeaders), "");
		} else {
			$aTable = \array_slice($aTable, 1, -1);
		}

		$aReturn = [];
		if(\is_array($aTable) && \count($aTable)) {
			foreach($aTable as $sRow) {
				$sRow = \trim($sRow);
				if($bMultiline) {
					if(!empty($sRow) && $sRow[0]=="+") {
						$aReturn[] = \array_combine($aHeaders, $aMultiline);
						$aMultiline = $aMultilineEmpty;
						continue;
					}
					$aMultiline = self::call()->arrayConcat([$aMultiline, \array_slice(self::call()->explodeTrim("|", $sRow), 1, -1)], " ");
				} else {
					if(!empty($sRow) && $sRow[0]=="+") { continue; }
					$aReturn[] = \array_combine($aHeaders, \array_slice(self::call()->explodeTrim("|", $sRow), 1, -1));
				}
			}
		}

		return $aReturn;
	}
}

?>