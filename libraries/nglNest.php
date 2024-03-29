<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# nest
https://hytcom.net/nogal/docs/objects/nest.md
*/
namespace nogal;

class nglNest extends nglBranch {

	private $owl;
	private $jsql;
	private $aTypes;
	private $aFields;
	private $aPresets;
	private $aPresetFields;
	private $aStarred;
	private $aLoadData;
	private $aLoadDataIndex;
	private $aNormalize;
	private $bUpdate;
	private $aAdd;
	private $bAlterField;
	private $aAlterTable;
	private $aAlterField;
	private $bRegenerate;
	private $aAutoNormalize;
	private $bAutoNormalize;

	final protected function __declareArguments__() {
		$vArguments							= [];
		$vArguments["about"]				= ['$mValue', null];
		$vArguments["after"]				= ['$mValue', true];
		$vArguments["canvas_height"]		= ['(int)$mValue', 900];
		$vArguments["canvas_width"]			= ['(int)$mValue', 1800];
		$vArguments["comment"]				= ['$mValue'];
		$vArguments["core"]					= ['self::call()->istrue($mValue)', false];
		$vArguments["db"]					= ['$this->SetBase($mValue)', null];
		$vArguments["der"]					= ['self::call()->istrue($mValue)', false];
		$vArguments["enclosed"]				= ['$mValue', '"'];
		$vArguments["entity"]				= ['$mValue', null];
		$vArguments["eol"]					= ['$mValue', "\r\n"];
		$vArguments["field"]				= ['$mValue', null];
		$vArguments["fields"]				= ['(array)$mValue', null];
		$vArguments["filepath"]				= ['$mValue', null];
		$vArguments["gui_part"]				= ['$mValue', "table"];
		$vArguments["label"]				= ['$mValue', null];
		$vArguments["left"]					= ['(int)$mValue', 0];
		$vArguments["nestdata"]				= ['$this->SetNestData($mValue)', null];
		$vArguments["newname"]				= ['$mValue', null];
		$vArguments["normalize_code"]		= ['$mValue', "Código"];
		$vArguments["normalize_name"]		= ['$mValue', "Nombre"];
		$vArguments["objcfg_val"]			= ['(int)$mValue', 0];
		$vArguments["objcfg_var"]			= ['$mValue', null];
		$vArguments["roles"]				= ['(int)$mValue', 0];
		$vArguments["run"]					= ['(boolean)$mValue', false];
		$vArguments["select"]				= ['$this->SetObject($mValue)', null];
		$vArguments["splitter"]				= ['$mValue', ";"];
		$vArguments["structure"]			= ['$mValue', null];
		$vArguments["title"]				= ['$mValue', null];
		$vArguments["top"]					= ['(int)$mValue', 0];
		$vArguments["type"]					= ['$mValue', "varchar"];
		$vArguments["using"]				= ['$mValue', null];

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes						= [];
		$vAttributes["object"]				= null;
		$vAttributes["objtype"]				= null;
		$vAttributes["sql"]					= null;
		return $vAttributes;
	}

	final protected function __declareVariables__() {
		// types
		$aTypes = [
			["label"=>"DATE", "value"=>"DATE"],
			["label"=>"DECIMAL", "value"=>"DECIMAL"],
			["label"=>"INTEGER", "value"=>"INTEGER"],
			["label"=>"TEXT", "value"=>"TEXT"],
			["label"=>"TIMESTAMP", "value"=>"TIMESTAMP"],
			["label"=>"VARCHAR", "value"=>"VARCHAR"]
		];

		// fields
		$aFields = [
			"varchar" => ["alias"=>"varchar", "type"=>"VARCHAR", "length"=>"255", "default"=>"NULL", "attrs"=>"", "index"=>"", "null"=>true],
			"text" => ["alias"=>"text", "type"=>"TEXT", "length"=>"", "default"=>"NULL", "attrs"=>"", "index"=>"", "null"=>true],
			"date" => ["alias"=>"date","type"=>"DATE","length"=>"","default"=>"NULL","attrs"=>"","index"=>"","null"=>true],
			"datetime" => ["alias"=>"datetime","type"=>"DATETIME","length"=>"","default"=>"NULL","attrs"=>"","index"=>"","null"=>true],
			"time" => ["alias"=>"time","type"=>"TIME","length"=>"","default"=>"NULL","attrs"=>"","index"=>"","null"=>true],
			"timestamp" => ["alias"=>"timestamp","type"=>"TIMESTAMP","length"=>"","default"=>"NULL","attrs"=>"","index"=>"","null"=>true],
			"int" => ["alias"=>"int","type"=>"INT","length"=>"","default"=>"NULL","attrs"=>"UNSIGNED","index"=>"","null"=>true],
			"code" => ["alias"=>"code", "type"=>"VARCHAR", "length"=>"32", "default"=>"NULL", "index"=>"UNIQUE", "null"=>true],
			"name" => ["alias"=>"name", "type"=>"VARCHAR", "length"=>"64", "default"=>"NONE", "attrs"=>"", "index"=>"", "null"=>false]
		];

		// presets
		$aPresets = ["basic" => ["code"=>"code", "name"=>"name"]];

		// preset fields
		$aPresetFields = [];

		// seteo final
		$this->aFields 			= $aFields;
		$this->aTypes 			= $aTypes;
		$this->aPresets			= $aPresets;
		$this->aPresetFields	= $aPresetFields;
		$this->bUpdate			= false;
		$this->bAlterField		= false;
		$this->bRegenerate		= false;
		$this->aAdd				= [];
		$this->aAlterTable		= [];
		$this->aAlterField		= [];
		$this->aLoadData		= [];
		$this->aLoadDataIndex	= [];
		$this->aNormalize		= [];
		$this->aStarred			= [];
		$this->aAutoNormalize	= [];
		$this->bAutoNormalize	= false;
	}

	final public function __init__() {
	}

	protected function SetNestData($sNestFile=null) {
		$sNestFile = self::call()->clearPath($sNestFile);
		$sNestFile = self::call()->sandboxPath($sNestFile);
		if(!\file_exists($sNestFile)) { $sNestFile = NGL_PATH_FRAMEWORK.NGL_DIR_SLASH."assets".NGL_DIR_SLASH."data".NGL_DIR_SLASH."nest.json"; }
		if(\file_exists($sNestFile)) {
			$sJsonNest = \file_get_contents($sNestFile);
			$aJsonNest = \json_decode($sJsonNest, true);
			if(\is_array($aJsonNest) && \count($aJsonNest)) {
				if(isset($aJsonNest["types"])) { $aTypes = $aJsonNest["types"]; }
				if(isset($aJsonNest["fields"])) { $aFields = \array_merge($this->aFields, $aJsonNest["fields"]); }
				if(isset($aJsonNest["presets"])) { $aPresets = \array_merge($this->aPresets, $aJsonNest["presets"]); }
				if(isset($aJsonNest["presetfields"])) { $aPresetFields = \array_merge($this->aPresetFields, $aJsonNest["presetfields"]); }
			}

			$this->aTypes 			= $aTypes;
			$this->aFields 			= $aFields;
			$this->aPresets			= $aPresets;
			$this->aPresetFields	= $aPresetFields;
		}
	}

	public function add() {
		if($this->attribute("objtype")!="table") {
			self::errorMessage($this->object, 1003);
			return false;
		}

		list($sField,$mType,$sAfter) = $this->getarguments("field,type,after", \func_get_args());
		$sObject = $this->attribute("object");

		if(\is_array($sField) && \count($sField)) {
			foreach($sField as $aField) {
				\call_user_func_array([$this, "add"], $aField);
			}
			return $this;
		}

		// $bNew = (!isset($this->owl["tables"][$sObject][$sField]));
		$sField = $this->FormatName($sField);
		if($this->bAlterField===true && \is_array($mType)) {
			$sOldField = $sField;
			if(isset($mType["name"])) { $sField = $this->FormatName($mType["name"]); }
			if(!isset($mType["label"])) { $mType["label"] = $sField; }

			$aOldType = $this->owl["def"][$sObject][$sOldField];
			$mType["oldname"] = $sOldField;
			$mType["oldindex"] = !empty($aOldType["index"]) ? $aOldType["index"] : "";

			if(!(!empty($mType["type"]) || isset($mType["alias"]))) {
				$mType = \array_merge($aOldType, $mType);
				if(isset($this->owl["joins"][$sObject])) {
					foreach($this->owl["joins"][$sObject] as &$sJoinField) {
						$aJoinField = \explode(":", $sJoinField);
						if($aJoinField[0]==$sOldField) { $sJoinField = $sField.":".$aJoinField[1]; }
					}
					unset($sJoinField);
				}
			} else {
				$this->DefJoins($sObject, $sField);
				if(!empty($mType["type"]) && $mType["type"][0]=="@") { // @tabla OR @tabla-padre cuando es pid
					$this->DefJoins($sObject, $sField, $mType["type"], $mType["label"]);
					if(isset($mType["index"])) { $sIndexType = \strtoupper($mType["index"]); }
					$mType = \array_merge($mType, $this->aFields["fk"]);
					if(isset($sIndexType)) { $mType["index"] = $sIndexType; }
				}
			}

			if($sAfter===true) {
				foreach($this->owl["tables"][$sObject] as $sFieldName => $v) {
					if($sFieldName==$sOldField) { break; }
					$sAfter = $sFieldName;
				}
			}

			unset($this->owl["tables"][$sObject][$sOldField], $this->owl["def"][$sObject][$sOldField]);
		}

		$sLabel = (isset($mType["label"])) ? $mType["label"] : $sField;
		if(\strpos($sField, ":")!==false) {
			$aField = \explode(":", $sField, 2);
			$sField = $aField[0];
			$sLabel = $aField[1];
		}

		if($sField=="id" || $sField=="imya" || $sField=="state") { return $this; }

		if($sField===null || $sObject===null) {
			self::errorMessage($this->object, 1005);
			return false;
		} else if(isset($this->owl["tables"][$sObject][$sField]) && $this->bAlterField===false) {
			self::errorMessage($this->object, 1006, $sField);
			return false;
		}

		if(\is_array($mType)) {
			if(!isset($mType["name"])) { $mType["name"] = $sField; }
			if(!empty($mType["type"]) && $mType["type"][0]=="@") { // @tabla OR @tabla-padre cuando es pid
				if(!isset($mType["label"])) { $mType["label"] = $mType["name"]; }
				$this->DefJoins($sObject, $sField, $mType["type"], $mType["label"]);
				$aType = $this->aFields["fk"];
			} else {
				if(!\array_key_exists("default", $mType)) { $mType["default"] = null; }
				if(\strtolower($mType["default"])=="now" || \strtolower($mType["default"])=="current_timestamp") {
					$mType["default"] = "CURRENT_TIMESTAMP()";
				} else if(\strtolower($mType["default"])=="null" || $mType["default"]===null) {
					$mType["default"] = "";
					$mType["null"] = true;
				} else {
					$mType["default"] = \addslashes($mType["default"]);
				}

				if(isset($mType["label"])) { $sLabel = $mType["label"]; }
				if(isset($mType["alias"], $this->aFields[\strtolower($mType["alias"])])) {
					$aType = \array_merge($this->aFields[\strtolower($mType["alias"])], $mType);
				} else {
					$aType = \array_merge($this->aFields["varchar"], $mType);
				}
			}
		} else if(\is_string($mType)) {
			if(isset($this->aFields[$mType])) {
				$aType = $this->aFields[\strtolower($mType)];
			} else if(!empty($mType) && $mType[0]=="@") { // @tabla OR @tabla-padre cuando es pid
				$this->DefJoins($sObject, $sField, $mType);
				$aType = $this->aFields["fk"];
			} else {
				$aType = $this->aFields["varchar"];
			}
		} else {
			$aType = $this->aFields["varchar"];
		}

		if(!isset($aType["label"])) { $aType["label"] = $sLabel; }
		if(!empty($mType["comment"])) { $aType["comment"] = $mType["comment"]; }
		if(!empty($aType["attrs"]) && $aType["attrs"]=="--") { $aType["attrs"] = ""; }
		if(!empty($aType["index"]) && $aType["index"]=="--") { $aType["index"] = ""; }
		if(!empty($aType["oldindex"]) && $aType["oldindex"]=="--") { $aType["oldindex"] = ""; }

		if(!empty($aType["default"]) && $aType["default"]==NGL_NULL && (\strtolower($aType["type"])=="enum" || \strtolower($aType["type"])=="enum")) {
			$aType["default"] = \explode("','", $aType["length"]);
			$aType["default"] = "'".\substr($aType["default"][0], 1)."'";
		}

		if($sAfter===true) {
			$this->owl["tables"][$sObject][$sField] = $sField;
			$this->owl["def"][$sObject][$sField] = $aType;
		} else {
			self::call()->arrayInsert($this->owl["tables"][$sObject], $sAfter, [$sField=>$sField]);
			self::call()->arrayInsert($this->owl["def"][$sObject], $sAfter, [$sField=>$aType]);
		}

		if($this->bUpdate) {
			if(!isset($this->aAlterField[$sObject])) { $this->aAlterField[$sObject] = []; }
			if(!isset($this->aAlterField[$sObject][$sField])) { $this->aAlterField[$sObject][$sField] = []; }
			$this->aAlterField[$sObject][$sField][] = ($this->bAlterField===false) ? $sAfter : (($sField==$sOldField) ? "@MODIFY" : "@CHANGE");

			if(isset($sOldField) && $sField!=$sOldField && isset($this->owl["nest"]["objects"][$sObject]["starred"][$sOldField])) {
				unset($this->owl["nest"]["objects"][$sObject]["starred"][$sOldField]);
				$this->owl["nest"]["objects"][$sObject]["starred"][$sField] = $sField;
				$this->aStarred[$sObject] = true;
			}
		}

		return $this;
	}

	public function alter() {
		list($sField,$mType) = $this->getarguments("field,type", \func_get_args());

		$this->bAlterField = true;
		$sField = $this->FormatName($sField);
		if(\is_string($mType)) {
			if(!empty($mType) && $mType[0]!="@") {
				$mType = ["name"=>$mType];
			} else {
				$mType = ["name"=>$sField, "type"=>$mType];
			}
		}

		$return = \call_user_func_array([$this, "add"], [$sField, $mType]);
		$this->bAlterField = false;
		return $return;
	}

	public function check() {
		list($sObject) = $this->getarguments("entity", \func_get_args());
		return (isset($this->owl["tables"][$sObject]) || isset($this->owl["views"][$sObject]) || isset($this->owl["foreigns"][$sObject]));
	}

	public function chtitle() {
		list($sTitle) = $this->getarguments("title", \func_get_args());
		$sObject = $this->attribute("object");

		if($sObject==null) {
			self::errorMessage($this->object, 1001);
			return false;
		} else if(!isset($this->owl["tables"][$sObject])) {
			self::errorMessage($this->object, 1004, $sObject);
			return false;
		}
		if($sTitle!==null) { $this->owl["titles"][$sObject] = $sTitle; }

		return $this;
	}

	public function comment() {
		list($sComment) = $this->getarguments("comment", \func_get_args());
		$sObject = $this->attribute("object");
		$this->owl["nest"]["objects"][$sObject]["comment"] = $sComment;
		if(!\array_key_exists($sObject, $this->aAlterTable)) { $this->aAlterTable[$sObject] = []; }
		$this->aAlterTable[$sObject]["comment"] = $sComment;
		return $this;
	}

	public function create() {
		list($sObject, $sTitle, $aFields, $sComment) = $this->getarguments("entity,title,fields,comment", \func_get_args());

		if($sObject==null) {
			self::errorMessage($this->object, 1001);
			return false;
		} else if(isset($this->owl["tables"][$sObject])) {
			if(isset($this->aAutoNormalize[$sObject])) {
				unset($this->aAutoNormalize[$sObject], $this->owl["tables"][$sObject]);
			} else {
				self::errorMessage($this->object, 1002, $sObject);
				return false;
			}
		} else if(isset($this->aAutoNormalize[$sObject])) {
			unset($this->aAutoNormalize[$sObject]);
		}

		$sObject = $this->FormatName($sObject);
		$this->owl["tables"][$sObject] = [];
		$this->owl["titles"][$sObject] = ($sTitle!==null) ? $sTitle : $sObject;
		$this->owl["nest"]["objects"][$sObject]	= ["left"=>0, "top"=>0, "comment"=>"$sComment"];
		$this->owl["def"][$sObject] = [];
		$this->SetObject($sObject);

		if(\is_array($aFields) && \count($aFields)) {
			foreach($aFields as $sField => $mType) {
				if(\is_int($sField)) {
					$sField = $mType;
					$sType = null;
				}
				$this->add($sField, $mType);
			}
		}

		if($this->bUpdate) { $this->aAdd[$sObject] = true; }
		return $this;
	}

	public function describe() {
		list($sAbout) = $this->getarguments("about", \func_get_args());
		$sAbout = \strtolower($sAbout);
		$sObject = $this->attribute("object");
		if($sObject===null) { return $this->describeall(); }

		if($sAbout=="fields") { return \array_values($this->owl["tables"][$sObject]); }
		if($sAbout=="structure") { return $this->owl["def"][$sObject]; }

		$aFrom = $aRelations = [];
		$aSelect = [$sObject.".id AS '__id__'", $sObject.".imya AS '__imya__'"];
		if(isset($this->owl["parents"][$sObject])) {
			$sParent = $this->owl["parents"][$sObject];
			$aRelations[] =  "TABLE ".$sObject." CHILD OF ".$sParent." [__parent]";
			$aSelect = \array_merge($aSelect, $this->DescribeColumns($sObject, $sObject), $this->DescribeColumns($sParent, $sParent));
			$aFrom[] = $sObject;
			$aFrom[] = "LEFT JOIN ".$sParent." ".$sParent." ON ".$sParent.".id = ".$sObject.".pid";
			if(isset($this->owl["joins"][$sParent])) {
				foreach($this->owl["joins"][$sParent] as $sTable) {
					$aTable = \explode(":", $sTable);
					$sAlias = $sParent."_".$aTable[1];
					$aRelations[] = "-- PARENT JOINED TO ".$aTable[1]." AS '".$sAlias."' [__parent_".$aTable[1]."] USING ".$aTable[0]." FIELD";

					$aSelect = \array_merge($aSelect, $this->DescribeColumns($aTable[1], $sAlias));
					$aFrom[] = "LEFT JOIN ".$aTable[1]." ".$sAlias." ON ".$sAlias.".id = ".$sParent.".".$aTable[0];
				}
			}
		} else {
			$aRelations[] = "MAIN TABLE ".$sObject;
			$aSelect = $this->DescribeColumns($sObject, $sObject);
			$aFrom[] = $sObject;
		}

		if(isset($this->owl["children"][$sObject])) {
			foreach($this->owl["children"][$sObject] as $sChildren) {
				$aRelations[] = "-- PARENT OF ".$sChildren;

				$aSelect = \array_merge($aSelect, $this->DescribeColumns($sChildren, $sChildren));
				$aFrom[] = "LEFT JOIN ".$sChildren." ON ".$sChildren.".pid = ".$sObject.".id";

				if(isset($this->owl["joins"][$sChildren])) {
					foreach($this->owl["joins"][$sChildren] as $sTable) {
						$aTable = \explode(":", $sTable);
						$sAlias = $sObject."_".$aTable[1];
						$aRelations[] = "------ JOINED TO ".$aTable[1]." AS '".$sAlias."' USING ".$aTable[0]." FIELD";

						$aSelect = \array_merge($aSelect, $this->DescribeColumns($aTable[1], $sAlias));
						$aFrom[] = "LEFT JOIN ".$aTable[1]." ".$sAlias." ON ".$sAlias.".id = ".$sChildren.".".$aTable[0];
					}
				}
			}
		}

		if(isset($this->owl["joins"][$sObject])) {
			foreach($this->owl["joins"][$sObject] as $sTable) {
				$aTable = \explode(":", $sTable);
				$sAlias = $sObject."_".$aTable[1];
				$aRelations[] = "---- JOINED TO ".$aTable[1]." AS '".$sAlias."' USING ".$aTable[0]." FIELD";

				$aSelect = \array_merge($aSelect, $this->DescribeColumns($aTable[1], $sAlias));
				$sJoinField = (isset($aTable[2])) ? $aTable[2] : "id";
				$aFrom[] = "LEFT JOIN ".$aTable[1]." ".$sAlias." ON ".$sAlias.".".$sJoinField." = ".$sObject.".".$aTable[0];
			}
		}

		$sRelations = \implode("\n", $aRelations);
		if($sAbout=="relations") { return $sRelations; }

		$sView = "CREATE VIEW view_".$sObject." AS (\n";
		$sView .= "\tSELECT\n\t\t".\implode(",\n\t\t", $aSelect)."\n";
		$sView .= "\tFROM\n\t\t".\implode("\n\t\t", $aFrom)."\n";
		$sView .= ");";
		if($sAbout=="view") { return $sView; }

		$bView = (!isset($this->owl["tables"][$sObject]) && isset($this->owl["views"][$sObject]) && !isset($this->owl["foreigns"][$sObject])) ? true : false;
		$aDescribe = [
			"title" => (!$bView) ? (isset($this->owl["titles"][$sObject]) ? $this->owl["titles"][$sObject] : $sObject) : $this->owl["views"][$sObject]["title"],
			"fields" => (!$bView) ? $this->owl["tables"][$sObject] : \array_combine(\array_keys($this->owl["views"][$sObject]["fields"]), \array_keys($this->owl["views"][$sObject]["fields"])),
			"relationship" => "\n\n".$sRelations."\n\n",
			"view" => "\n\n".$sView."\n\n",
			"structure" => (isset($this->owl["def"][$sObject])) ? $this->owl["def"][$sObject] : $this->owl["views"][$sObject]["fields"],
			"foreignkeys" => (isset($this->owl["foreignkeys"][$sObject]) ? $this->owl["foreignkeys"][$sObject] : null),
			"parent" => (isset($this->owl["parents"][$sObject]) ? $this->owl["parents"][$sObject] : null),
			"children" => (isset($this->owl["children"][$sObject]) ? $this->owl["children"][$sObject] : null),
			"joins" => (isset($this->owl["joins"][$sObject]) ? $this->owl["joins"][$sObject] : null),
			"validator" => (isset($this->owl["validator"][$sObject]) ? $this->owl["validator"][$sObject] : null)
		];

		if(isset($aDescribe[$sAbout])) { return $aDescribe[$sAbout]; }
		return $aDescribe;
	}

	public function describeall() {
		list($bDer) = $this->getarguments("der", \func_get_args());
		return ($bDer) ? $this->Structure() : $this->owl;
	}

	public function drop() {
		if($this->attribute("objtype")!="table") {
			self::errorMessage($this->object, 1003);
			return false;
		}

		$sObject = $this->attribute("object");
		foreach($this->owl["children"] as $sChildren => $aChildren) {
			if(isset($aChildren[$sObject])) { unset($this->owl["children"][$sChildren][$sObject]); }
			if(!\count($this->owl["children"][$sChildren])) { unset($this->owl["children"][$sChildren]); }
		}

		foreach($this->owl["joins"] as $sJoin => $aJoin) {
			if($sJoin==$sObject) { unset($this->owl["joins"][$sObject]); continue; }
			foreach($aJoin as $nJoin => $sJoinText) {
				if(\strpos($sJoinText, ":".$sObject.":")) { unset($this->owl["joins"][$sJoin][$nJoin]); }
			}
			if(!\count($this->owl["joins"][$sJoin])) { unset($this->owl["joins"][$sJoin]); }
		}

		unset(
			$this->owl["tables"][$sObject],
			$this->owl["nest"]["objects"][$sObject],
			$this->owl["titles"][$sObject],
			$this->owl["def"][$sObject],
			$this->owl["foreignkeys"][$sObject],
			$this->owl["children"][$sObject],
			$this->owl["validator"][$sObject]
		);
		foreach($this->owl["parents"] as $sChild => $sParent) {
			if($sObject==$sParent) { unset($this->owl["parents"][$sChild]); }
		}

		$sNewName = "dropped_".$sObject."_".\date("YmdHis")."_".self::call()->unique(8);
		if(!\array_key_exists($sObject, $this->aAlterTable)) { $this->aAlterTable[$sObject] = []; }
		$this->aAlterTable[$sObject]["rename"] = $sNewName;
		$this->attribute("object", null);
		return $this;
	}

	public function createCode() {
		if($this->attribute("objtype")!="table") {
			self::errorMessage($this->object, 1003);
			return false;
		}

		$sObject = $this->attribute("object");
		if(isset($this->owl["def"][$sObject])) {
			return $this->CreateTableStructure($sObject, $this->owl["def"][$sObject], true);
		}

		return false;
	}

	public function createNestCode() {
		if($this->attribute("objtype")!="table") {
			self::errorMessage($this->object, 1003);
			return false;
		}

		$sObject = $this->attribute("object");
		if(isset($this->owl["def"][$sObject])) {
			$aTableFields = $this->owl["def"][$sObject];
			if(isset($this->owl["parents"][$sObject])) { $aTableFields["pid"] = array("name"=>"pid", "type"=>"@".$this->owl["parents"][$sObject]); }
			if(isset($this->owl["joins"][$sObject])) {
				foreach($this->owl["joins"][$sObject] as $sJoin) {
					$aJoin = \explode(":", $sJoin);
					$aTableFields[$aJoin[0]] = array("name"=>$aJoin[0], "type" => "@".$aJoin[1]);
				}
			}

			$sNestCode = '-$: create ["'.$sObject.'","'.$this->owl["titles"][$sObject].'"]'."\n";
			foreach($aTableFields as $sField => $aField) {
				$sNestCode .= '-$: add ["'.$sField.'", '.\json_encode($aField).']'."\n";
			}

			return $sNestCode;
		}
	}

	public function createNestCodeFromTable() {
		list($sObject) = $this->getarguments("entity", \func_get_args());
		if($aDescribe = $this->db->describe($sObject)) {
			$sHash = self::call()->unique(8);

			if(!\array_key_exists($sObject, $this->owl["tables"])) {
				$sNestCode = '-$: create ["'.$sObject.'","'.$sObject.'"]'."\n";
			} else {
				$sNestCode = '-$: select "'.$sObject.'"'."\n";
				$aCurrent = $this->owl["tables"][$sObject];
				unset($aCurrent["id"], $aCurrent["imya"], $aCurrent["state"]);
			}

			foreach($aDescribe as $aField) {
				$sField = \strtolower($aField["name"]);

				if($sField=="id" || $sField=="imya" || $sField=="state") {
					if(isset($aCurrent)) { continue; }
					$sField .= "_".$sHash;
				}
				$aField["type"] = \strtoupper($aField["type"]);
				$aField["default"] = (!empty($aField["default"])) ? $aField["default"] : "NONE";
				$aField["attrs"] = (!empty($aField["attributes"])) ? \strtoupper($aField["attributes"]) : "";
				$aField["index"] = (!empty($aField["index"])) ? \strtoupper($aField["index"]) : "";
				$aField["null"] = $aField["nullable"]=="YES" ? true : false;
				unset($aField["attributes"], $aField["nullable"], $aField["extra"]);

				if(isset($aCurrent) && \array_key_exists($sField, $aCurrent)) {
					unset($aCurrent[$sField]);
					if(
						(\array_key_exists("type", $this->owl["def"][$sObject][$sField]) && $this->owl["def"][$sObject][$sField]["type"] != $aField["type"]) ||
						(\array_key_exists("length", $this->owl["def"][$sObject][$sField]) && $this->owl["def"][$sObject][$sField]["length"] != $aField["length"]) ||
						(\array_key_exists("default", $this->owl["def"][$sObject][$sField]) && $this->owl["def"][$sObject][$sField]["default"] != $aField["default"]) ||
						(\array_key_exists("index", $this->owl["def"][$sObject][$sField]) && $this->owl["def"][$sObject][$sField]["index"] != $aField["index"]) ||
						(\array_key_exists("attrs", $this->owl["def"][$sObject][$sField]) && $this->owl["def"][$sObject][$sField]["attrs"] != $aField["attrs"]) ||
						(\array_key_exists("null", $this->owl["def"][$sObject][$sField]) && $this->owl["def"][$sObject][$sField]["null"] != $aField["null"]) ||
						(\array_key_exists("comment", $this->owl["def"][$sObject][$sField]) && $this->owl["def"][$sObject][$sField]["comment"] != $aField["comment"])
					) {
						$sNestCode .= '-$: alter ["'.$sField.'", '.\json_encode($aField).']'."\n";
					}
				} else {
					$sNestCode .= '-$: add ["'.$sField.'", '.\json_encode($aField).']'."\n";
				}
			}

			if(isset($aCurrent) && \count($aCurrent)) {
				foreach($aCurrent as $sField) {
					$sNestCode .= '-$: rem "'.$sField.'"'."\n";
				}
			}

			return $sNestCode;
		}

		return "";
	}

	public function regenerate() {
		$this->bRegenerate = true;
		$bCore = $this->core;
		$this->args("core", true);
		$sCode = $this->generate(false);
		$this->bRegenerate = false;
		$this->args("core", $bCore);
		return $sCode;
	}

	public function generate() {
		list($bRun) = $this->getarguments("run", \func_get_args());
		if($this->db===null) { self::errorMessage($this->object, 1009); }

		$aDbConfig = [];
		$aDbConfig["debug"] = $this->db->debug;
		$aDbConfig["check_colnames"] = $this->db->check_colnames;
		$this->db->debug = true;
		$this->db->check_colnames = false;
		$this->db->connect();

		// automaticas
		if(\is_array($this->aAutoNormalize) && \count($this->aAutoNormalize)) {
			$this->bAutoNormalize = true;
			foreach($this->aAutoNormalize as $aToNorm) {
				$this->normalize(...$aToNorm);
			}
			$this->bAutoNormalize = false;
		}

		// owl data
		$aOWL = $this->owl;

		// fix campos
		$aBasic = ["id"=>"id", "imya"=>"imya", "state"=>"state"];
		foreach($aOWL["tables"] as $sTable => &$aTable) {
			$aTable = \array_merge($aBasic, $aTable);
			foreach($aOWL["def"][$sTable] as $sField => $aField) {
				$aTable[$sField] = (isset($aField["label"])) ? $aField["label"] : $sField;
			}
		}
		unset($aTable);
		\ksort($aOWL["tables"]);

		// fix joins
		if(isset($aOWL["joins"])) {
			foreach($aOWL["joins"] as $sTable => $aTable) {
				$aTableJoins = [];
				foreach($aTable as $sIndex => $sField) {
					if(!\is_array($sField)) { $sIndex = \substr($sField, 0, \strpos($sField, ":")); }
					if(!isset($aTableJoins[$sIndex])) {
						$aTableJoins[$sIndex] = $sField;
					} else {
						if(!\is_array($aTableJoins[$sIndex])) { $aTableJoins[$sIndex] = [$aTableJoins[$sIndex]]; }
						$aTableJoins[$sIndex][] = $sField;
					}
				}
				$aOWL["joins"][$sTable] = $aTableJoins;
				asort($aOWL["joins"][$sTable]);
			}
		}

		// fix parents/children
		if(isset($aOWL["children"])) {
			$aNewChildren = [];
			foreach($aOWL["children"] as $sParent => $aChildren) {
				$aNewChildren[$sParent] = [];
				foreach($aChildren as $mKey => $sChildren) {
					$aNewChildren[$sParent][$sChildren] = $sChildren;
					$aOWL["parents"][$sChildren] = $sParent;
				}
			}
			$aOWL["children"] = $aNewChildren;
		}

		foreach(\array_keys($aOWL["tables"]) as $sTable) {
			if(!isset($aOWL["titles"][$sTable])) { $aOWL["titles"][$sTable] = $sTable; }
		}

		$aJSON = $aOWL;
		foreach($aJSON["def"] as &$aJsonTable) {
			foreach($aJsonTable as &$aJsonField) {
				if(!\array_key_exists("comment", $aJsonField)) { $aJsonField["comment"] = ""; }
				unset($aJsonField["oldname"], $aJsonField["oldindex"]);
			}
		}
		unset($aJsonTable, $aJsonField);

		// sort
		\ksort($aJSON["tables"]);
		\ksort($aJSON["nest"]["objects"]);
		\ksort($aJSON["titles"]);
		\ksort($aJSON["foreigns"]);
		\ksort($aJSON["views"]);
		\ksort($aJSON["def"]);
		\ksort($aJSON["parents"]);
		\ksort($aJSON["children"]);
		\ksort($aJSON["joins"]);
		\ksort($aJSON["validator"]);

		$sJSON = self::call("shift")->convert($aJSON, "array-json");

		$sJSONCompact = self::call("shift")->jsonformat($sJSON, true);
		if($this->bUpdate==false || $this->core) {
			$sSQLStructure = $this->CreateStructure();
		} else {
			$sSQLStructure = "";
		}

		// RENAME / DROP TABLE
		if(\is_array($this->aAlterTable) && \count($this->aAlterTable)) {
			foreach($this->aAlterTable as $sTable =>$aTable) {
				if(isset($aTable["comment"])) { $sSQLStructure .= $this->jsql->query(["query"=>"comment", "table"=>$sTable, "comment"=>$aTable["comment"]])."\n"; }
				if(isset($aTable["rename"])) { $sSQLStructure .= $this->jsql->query(["query"=>"rename", "table"=>$sTable, "newname"=>$aTable["rename"]])."\n"; }
			}
		}

		if($this->bRegenerate) { $this->aStarred = []; }
		foreach($aOWL["def"] as $sTable => $aTable) {
			$sSQLStructure .= $this->CreateTableStructure($sTable, $aTable, $this->bRegenerate);
			if($this->bRegenerate) {
				if(\array_key_exists("starred", $this->owl["nest"]["objects"][$sTable])) {
					$this->aStarred[$sTable] = true;
				}
			}
		}

		if(\is_array($this->aStarred) && \count($this->aStarred)) {
			// TODO: indices de texto completo, y borrado de indice. llevar a la clase del controlador
			/*
			$sSQLStructure .= "-- STARRED FIELDS --\n";
			foreach($this->aStarred as $sStarred => $w) {
				$aFullTextFields = \array_map([$db,"quote"], $this->owl["nest"]["objects"][$sStarred]["starred"]);
				$sFullTextFields = \implode(",", $aFullTextFields);
				$sSQLStructure .= "CALL func.drop_index(DATABASE(), '".$sStarred."', 'globalsearch');\n";
				$sSQLStructure .= "ALTER TABLE ".$this->db->quote($sStarred)." ADD FULLTEXT INDEX ".$this->db->quote("globalsearch")." (".$sFullTextFields.");\n\n";
			}
			*/
		}

		if(!$this->bRegenerate) {
			if(\is_array($this->aNormalize) && \count($this->aNormalize)) {
				$sSQLStructure .= "\n-- -----------------------------------------------------------------------------\n\n";
				$sSQLStructure .= "-- NORMALIZE --\n";
				foreach($this->aNormalize as $sNewObject => $aNormalize) {
					$sSQLStructure .= "--".$aNormalize[0].".".$aNormalize[1]." TO ".$sNewObject." --\n";
				}
			}

			if(\is_array($this->aLoadDataIndex) && \count($this->aLoadDataIndex)) {
				$sSQLStructure .= "\n-- -----------------------------------------------------------------------------\n\n";
				$sSQLStructure .= "-- LOAD DATA FROM FILES --\n";
				foreach($this->aLoadDataIndex as $sLoadDataIndexName => $sLoadDataIndexFile) {
					$sSQLStructure .= "-- ".$sLoadDataIndexFile." INTO ".$sLoadDataIndexName." --\n";
				}
			}
		}

		// ESTRUCTURA
		$sJSONCompact = $this->db->escape($sJSONCompact);
		$sOwlBackup = "owl_".\date("YmdHis");
		$sSQL = "\n-- -----------------------------------------------------------------------------\n\n";
		$sSQL .= "-- SAVE OWL STRUCTURE ON __ngl_sentences__ --\n";
		$sSQL .= "INSERT INTO ".$this->db->quote("__ngl_sentences__")." SELECT '".$sOwlBackup."' AS ".$this->db->quote("name").", 'structure' AS ".$this->db->quote("type").", ".$this->db->quote("sentence").", ".$this->db->quote("dependencies").", ".$this->db->quote("notes")." FROM ".$this->db->quote("__ngl_sentences__")." WHERE ".$this->db->quote("name")." = 'owl';\n";
		$sSQL .= $this->db->replace("__ngl_sentences__", ["name"=>"owl", "type"=>"structure", "sentence"=>$sJSONCompact]).";\n";
		$sSQL .= "\n\n";

		$sSQL .= "-- EMPTIES THE TABLE __ngl_owl_structure__ --\n";
		$sSQL .= "TRUNCATE TABLE ".$this->db->quote("__ngl_owl_structure__").";\n\n";

		// COLUMNAS
		$sSQL .= "-- BEGIN COLUMNS --\n";
		if(isset($aOWL["tables"])) {
			foreach($aOWL["tables"] as $sTable => $aColumns) {
				$sColumns = '["'.\implode('","', \array_keys($aColumns)).'"]';
				$sColumns = $this->db->escape($sColumns);
				$sCode = \substr(self::call()->strimya($sTable), 0, 12);
				$nRoles = (\array_key_exists("roles", $this->owl["nest"]["objects"][$sTable])) ? $this->owl["nest"]["objects"][$sTable]["roles"] : "0";
				$sSQL .= $this->db->replace("__ngl_owl_structure__", ["name"=>$sTable, "code"=>$sCode, "roles"=>$nRoles, "columns"=>$sColumns]).";\n";
			}
		}
		if(isset($aOWL["foreigns"])) {
			foreach($aOWL["foreigns"] as $sTable => $aView) {
				$aColumns = \array_keys($aView["fields"]);
				$sColumns = '["'.\implode('","', $aColumns).'"]';
				$sColumns = $this->db->escape($sColumns);
				$sCode = \substr(self::call()->strimya($sTable), 0, 12);
				$sSQL .= $this->db->replace("__ngl_owl_structure__", ["name"=>$sTable, "code"=>$sCode, "columns"=>$sColumns]).";\n";
			}
		}
		if(isset($aOWL["views"])) {
			foreach($aOWL["views"] as $sTable => $aView) {
				$aColumns = \array_keys($aView["fields"]);
				$sColumns = '["'.\implode('","', $aColumns).'"]';
				$sColumns = $this->db->escape($sColumns);
				$sCode = \substr(self::call()->strimya($sTable), 0, 12);
				$sSQL .= $this->db->replace("__ngl_owl_structure__", ["name"=>$sTable, "code"=>$sCode, "columns"=>$sColumns]).";\n";
			}
		}
		$sSQL .= "-- END COLUMNS --\n\n";

		// FOREIGNKEYS
		if(isset($aOWL["foreignkeys"])) {
			$sSQL .= "-- BEGIN FOREIGNKEYS --\n";
			foreach($aOWL["foreignkeys"] as $sTable => $aKeys) {
				$aForeignkeys = ["fields"=>[], "tables"=>[]];
				foreach($aKeys as $sRef) {
					$sRef = \str_replace(".", ":", $sRef);
					$aRef = \explode(":", $sRef, 3);
					$aForeignkeys["fields"][$aRef[0]] = $aRef[0];

					if(!isset($aForeignkeys["tables"][$aRef[1]])) { $aForeignkeys["tables"][$aRef[1]] = []; }
					$aForeignkeys["tables"][$aRef[1]][] = $aRef[2];
				}
				$aForeignkeys["fields"] = \array_values($aForeignkeys["fields"]);
				$sForeignkeys = \json_encode($aForeignkeys);

				$sForeignkeys = $this->db->escape($sForeignkeys);
				$sSQL .= $this->db->update("__ngl_owl_structure__", ["foreignkey"=>$sForeignkeys], "name='".$sTable."'").";\n";
			}
			$sSQL .= "-- END FOREIGNKEYS --\n\n";
		}

		// JOINS
		$aJoins = [];
		if(isset($aOWL["joins"])) {
			foreach($aOWL["joins"] as $sTable => $aReferences) {
				if(!isset($aJoins[$sTable])) { $aJoins[$sTable] = ["joins"=>[], "children"=>[]]; }
				$aFlatReferences = $this->FlatJoins($aReferences);
				foreach($aFlatReferences as $sRef) {
					$aLabels = ["using","name","field"];
					$aCross = \explode(":", $sRef, 3);
					if(!isset($aCross[2])) { unset($aLabels[2]); }
					$aJoins[$sTable]["joins"][] = \array_combine($aLabels, $aCross);
				}
			}
		}

		if(isset($aOWL["children"])) {
			foreach($aOWL["children"] as $sTable => $aChildren) {
				if(!isset($aJoins[$sTable])) { $aJoins[$sTable] = ["joins"=>[], "children"=>[], "parent"=>""]; }
				foreach($aChildren as $sChildren) {
					$aJoins[$sTable]["children"][] = ["name" => $sChildren];
					if(!isset($aJoins[$sChildren])) { $aJoins[$sChildren] = ["joins"=>[], "children"=>[], "parent"=>""]; }
					$aJoins[$sChildren]["parent"] = $sTable;
				}
			}
		}

		if(\is_array($aJoins) && \count($aJoins)){
			$sSQL .= "-- BEGIN JOINS-CHILDREN --\n";
			foreach($aJoins as $sTable => $aJoin) {
				if(!\count($aJoin["joins"])) { unset($aJoin["joins"]); }
				if(!\count($aJoin["children"])) { unset($aJoin["children"]); }
				$sJoins = \json_encode($aJoin);
				$sJoins = $this->db->escape($sJoins);
				$sSQL .= $this->db->update("__ngl_owl_structure__", ["relationship"=>$sJoins], "name='".$sTable."'").";\n";
			}
			$sSQL .= "-- END JOINS-CHILDREN --\n\n";
		}

		// VALIDATOR
		if(isset($aOWL["validator"])) {
			$sSQL .= "-- BEGIN VALIDATOR --\n";
			foreach($aOWL["validator"] as $sTable => $aFields) {
				$aValidator = [];
				foreach($aFields as $sField => $aRules) {
					$aField = [];
					$aField["type"] = $aRules["type"];

					foreach($aRules["options"] as $sOption => $mValue) {
						if($mValue==="") { unset($aRules["options"][$sOption]); }
					}
					if(\is_array($aRules["options"]) && \count($aRules["options"])) { $aField["options"] = $aRules["options"]; }

					if(isset($aRules["rule"])) {
						foreach($aRules["rule"] as $sRule => $mValue) {
							if($mValue!=="") { $aField[$sRule] = $mValue; }
						}
					}

					$aValidator[$sField] = $aField;
				}

				$sValidator = \json_encode($aValidator);
				$sValidator = $this->db->escape($sValidator);
				$sSQL .= $this->db->update("__ngl_owl_structure__", ["validate_insert"=>$sValidator], "name='".$sTable."'").";\n";
				$sSQL .= $this->db->update("__ngl_owl_structure__", ["validate_update"=>$sValidator], "name='".$sTable."'").";\n";
			}
			$sSQL .= "-- END VALIDATOR --\n";
		}

		$this->db->debug = $aDbConfig["debug"];
		$this->db->check_colnames = $aDbConfig["check_colnames"];
		$this->attribute("sql", $sSQLStructure."\n\n".$sSQL);

		if($bRun) {
			$this->db->mquery($this->attribute("sql"));

			// carga de datos del createfromfile
			if(\is_array($this->aLoadData) && \count($this->aLoadData)) {
				$owl = self::call("owl")->connect($this->db);
				foreach($this->aLoadData as $sObjectToLoad => $aLoadData) {
					$owl->select($sObjectToLoad);
					$nCols = \count($aLoadData["fields"]);
					foreach($aLoadData["data"] as $aRow) {
						$aRow = \array_slice($aRow, 0, $nCols);
						$sRow = \trim(\implode("", $aRow));
						if($sRow=="") { break; }
						$owl->insert(\array_combine($aLoadData["fields"], $aRow));
					}
				}
			}

			// normalizaciones
			if(\is_array($this->aNormalize) && \count($this->aNormalize)) {
				$owl = self::call("owl")->connect($this->db);
				$sSQL .= "-- BEGIN NORMALIZATION --\n";
				foreach($this->aNormalize as $sNewObject => $aNormalize) {
					if(isset($this->aAutoNormalize[$sNewObject])) { continue; }
					$vals = $this->db->query("SELECT DISTINCT ".$this->db->quote($aNormalize[1]."_".$aNormalize[2])." FROM ".$this->db->quote($aNormalize[0])." ORDER BY 1");
					if($vals->rows()) {
						$owl->select($sNewObject);
						while($sVal = $vals->get($aNormalize[1]."_".$aNormalize[2])) {
							$nId = $owl->insert(["nombre"=>$sVal]);
							$sSQLUpdate = "UPDATE ".$this->db->quote($aNormalize[0])." SET ".$this->db->quote($aNormalize[1])." = '".$nId."' WHERE ".$this->db->quote($aNormalize[1]."_".$aNormalize[2])." = '".$sVal."'";
							$this->db->query($sSQLUpdate);
							$sSQL .= $sSQLUpdate."\n";
						}
					}
				}
				$sSQL .= "-- END NORMALIZATION --\n";
			}

			return "ok";
		} else {
			return $this->attribute("sql");
		}
	}

	// using es el campo del la tabla principal
	// field es el campo de la view o foreign table
	public function join() {
		list($sUsing,$sWith,$sField) = $this->getarguments("using,entity,field", \func_get_args());
		$sObject = $this->attribute("object");
		$sField = $this->FormatName($sField);
		$sWith = $this->FormatName($sWith);
		$sUsing = $this->FormatName($sUsing);
		if($sField===null || $sObject===null || $sUsing===null) {
			self::errorMessage($this->object, 1005);
			return false;
		} else if(!isset($this->owl["tables"][$sObject]) && !isset($this->owl["views"][$sWith]) && !isset($this->owl["foreigns"][$sObject])) {
			self::errorMessage($this->object, 1007, $sField);
			return false;
		}

		if(!isset($this->owl["joins"][$sObject])) { $this->owl["joins"][$sObject] = []; }
		$this->owl["joins"][$sObject][] = $sUsing.":".$sWith.":".$sField;

		// foreigns join
		if(isset($this->owl["foreigns"][$sWith])) {
			if(!isset($this->owl["foreigns"][$sWith]["joins"])) { $this->owl["foreigns"][$sWith]["joins"] = []; }
			$this->owl["foreigns"][$sWith]["joins"][$sObject] = [$sUsing, $sField];
		}

		// view join
		if(isset($this->owl["views"][$sWith])) {
			if(!isset($this->owl["views"][$sWith]["joins"])) { $this->owl["views"][$sWith]["joins"] = []; }
			$this->owl["views"][$sWith]["joins"][$sObject] = [$sUsing, $sField];
		}

		return $this;
	}

	public function load() {
		list($mStructure,$db) = $this->getarguments("structure,db", \func_get_args());

		if($db===null) { $db = self::call("mysql"); }
		if(!$db->connect()) {
			self::errorMessage($this->object, 1009);
			return false;
		}
		$this->args(["db"=>$db]);

		if(\is_array($mStructure)) {
			$aStructure = $mStructure;
		} else if(\is_string($mStructure)) {
			if(\count($this->db->tables("__ngl_sentences__"))) {
				if(\strtolower(\substr($mStructure,0,3))=="owl") {
					$mStructure = \preg_replace("/[^owl_0-9]/", "", $mStructure);
					$sentence = $this->jsql->query([
						"query" => "select",
						"columns" => [["sentence"]],
						"tables" =>["__ngl_sentences__"],
						"where" => [["[name]", "eq", $mStructure]]
					], true);
					if($sentence && $sentence->rows()) {
						$mStructure = $sentence->get("sentence");
						$aStructure = \json_decode($mStructure, true);
					} else if($mStructure!="owl") {
						$sentence = $this->jsql->query([
							"query" => "select",
							"columns" => [["sentence"]],
							"tables" =>["__ngl_sentences__"],
							"where" => [["[name]", "eq", "owl"]]
						], true);
						if($sentence && $sentence->rows()) {
							$mStructure = $sentence->get("sentence");
							$aStructure = \json_decode($mStructure, true);
						}
					}
				}
			}
		}

		$aDefault = [
			"tables" => [],
			"nest" => ["canvas"=>["width"=>"1024", "height"=>"768"], "objects"=>[]],
			"titles" => [],
			"foreigns" => [],
			"views" => [],
			"def" => [],
			"foreignkeys" => [],
			"parents" => [],
			"children" => [],
			"joins" => [],
			"validator" => []
		];

		if(!isset($aStructure)) {
			$aStructure = $aDefault;
		} else {
			$aStructure = \array_merge($aDefault, $aStructure);
			if(isset($aStructure["bee"])) {
				$aStructure["nest"] = array("canvas"=>[], "objects" => $aStructure["bee"]);
				unset($aStructure["bee"]);
			}
			$this->bUpdate = true;
		}

		// sort
		\ksort($aStructure["tables"]);
		\ksort($aStructure["nest"]["objects"]);
		\ksort($aStructure["titles"]);
		\ksort($aStructure["foreigns"]);
		\ksort($aStructure["views"]);
		\ksort($aStructure["def"]);
		\ksort($aStructure["parents"]);
		\ksort($aStructure["children"]);
		\ksort($aStructure["joins"]);
		\ksort($aStructure["validator"]);

		$this->owl = $aStructure;
		if(!\count($aStructure["nest"]["canvas"])) {
			$this->canvas();
		}

		return $this;
	}

	public function move() {
		list($sObject,$sField,$sAfter) = $this->getarguments("entity,field,after", \func_get_args());
		$sObject = $this->FormatName($sObject);
		$aFields = $this->owl["tables"][$sObject];
		if(!isset($aFields[$sAfter])) { $sAfter = true; }
		$aNewOrder = [];
		foreach($aFields as $sFieldKey => $sLabel) {
			if($sFieldKey!==$sField) { $aNewOrder[$sFieldKey] = $sLabel; }
			if($sFieldKey===$sAfter) { $aNewOrder[$sField] = $aFields[$sField]; }
		}
		if($sAfter===true) { $aNewOrder[$sField] = $aFields[$sField]; }

		$this->owl["tables"][$sObject] = $aNewOrder;
		return $this;
	}

	public function position() {
		list($sObject,$nLeft,$nTop) = $this->getarguments("entity,left,top", \func_get_args());
		$sObject = $this->FormatName($sObject);
		if(isset($this->owl["tables"][$sObject]) || isset($this->owl["views"][$sObject]) || isset($this->owl["foreigns"][$sObject])) {
			if(!isset($this->owl["nest"]["objects"][$sObject])) { $this->owl["nest"]["objects"][$sObject] = []; }
			$this->owl["nest"]["objects"][$sObject]["left"] = $nLeft;
			$this->owl["nest"]["objects"][$sObject]["top"] = $nTop;
		}
		return $this;
	}

	public function star() {
		list($sField) = $this->getarguments("field", \func_get_args());
		$sObject = $this->attribute("object");

		$sField = $this->FormatName($sField);
		if(isset($this->owl["tables"][$sObject]) || isset($this->owl["views"][$sObject]) || isset($this->owl["foreigns"][$sObject])) {
			if(!isset($this->owl["nest"]["objects"][$sObject]["starred"])) { $this->owl["nest"]["objects"][$sObject]["starred"] = []; }
			if(isset($this->owl["nest"]["objects"][$sObject]["starred"][$sField])) {
				unset($this->owl["nest"]["objects"][$sObject]["starred"][$sField]);
				$this->aStarred[$sObject] = true;
			} else {
				$this->owl["nest"]["objects"][$sObject]["starred"][$sField] = $sField;
				$this->aStarred[$sObject] = true;
			}
		}
		return $this;
	}

	public function gui() {
		list($sGUI,$sField) = $this->getarguments("gui_part,field", \func_get_args());
		$sObject = $this->attribute("object");

		$sField = $this->FormatName($sField);
		if(isset($this->owl["tables"][$sObject])) {
			if(!isset($this->owl["nest"]["objects"][$sObject]["gui"])) { $this->owl["nest"]["objects"][$sObject]["gui"] = array("table", "form"); }
			if(isset($this->owl["nest"]["objects"][$sObject]["gui"][$sGUI][$sField])) {
				unset($this->owl["nest"]["objects"][$sObject]["gui"][$sGUI][$sField]);
			} else {
				$this->owl["nest"]["objects"][$sObject]["gui"][$sGUI][$sField] = $sField;
			}
		}
		return $this;
	}

	public function objectvar() {
		list($sObject,$sVariable,$mValue) = $this->getarguments("entity,objcfg_var,objcfg_val", \func_get_args());
		$sObject = $this->FormatName($sObject);
		if(isset($this->owl["tables"][$sObject]) || isset($this->owl["views"][$sObject]) || isset($this->owl["foreigns"][$sObject])) {
			if(!isset($this->owl["nest"]["objects"][$sObject])) { $this->owl["nest"]["objects"][$sObject] = []; }
			$this->owl["nest"]["objects"][$sObject][$sVariable] = $mValue;
		}
		return $this;
	}

	public function canvas() {
		list($nWidth,$nHeight) = $this->getarguments("canvas_width,canvas_height", \func_get_args());
		if(!isset($this->owl["nest"]["canvas"])) { $this->owl["nest"]["canvas"] = []; }
		$this->owl["nest"]["canvas"]["width"] = $nWidth;
		$this->owl["nest"]["canvas"]["height"] = $nHeight;
		return $this;
	}

	public function preset() {
		list($sEntity, $sNewName, $sTitle) = $this->getarguments("entity,newname,title", \func_get_args());
		$sEntity = $this->FormatName($sEntity);
		if($sNewName===null) { $sNewName = $sEntity; }
		if(isset($this->aPresets[$sEntity])) {
			if($sTitle===null) { $sTitle = $sNewName; }
			return $this->create($sNewName, $sTitle, $this->aPresets[$sEntity]);
		}

		return false;
	}

	public function presets() {
		$aPresets = $this->aPresets;
		ksort($aPresets);
		return $aPresets;
	}

	public function rem() {
		if($this->attribute("objtype")!="table") {
			self::errorMessage($this->object, 1003);
			return false;
		}

		list($mField) = $this->getarguments("field", \func_get_args());
		if(\is_array($mField)) {
			foreach($mField as $sField) {
				$this->rem($sField);
			}
			return $this;
		}

		$sObject = $this->attribute("object");
		if($mField===null || $sObject===null) {
			self::errorMessage($this->object, 1005, $sObject);
			return false;
		} else if(!isset($this->owl["tables"][$sObject][$mField])) {
			self::errorMessage($this->object, 1008, $sObject.".".$mField);
			return false;
		}

		$mField = $this->FormatName($mField);
		unset($this->owl["tables"][$sObject][$mField], $this->owl["def"][$sObject][$mField]);

		if(isset($this->owl["parents"][$sObject])) {
			unset($this->owl["children"][$this->owl["parents"][$sObject]][$sObject]);
			if(!\count($this->owl["children"][$this->owl["parents"][$sObject]])) {
				unset($this->owl["children"][$this->owl["parents"][$sObject]]);
			}
			unset($this->owl["parents"][$sObject]);
		}

		if(isset($this->owl["joins"][$sObject])) {
			foreach($this->owl["joins"][$sObject] as $sIndex=>$sJoin) {
				if(\strpos($sJoin, $mField.":")===0) {
					unset($this->owl["joins"][$sObject][$sIndex]);
				}
			}

			if(!\count($this->owl["joins"][$sObject])) { unset($this->owl["joins"][$sObject]); }
		}

		if(!isset($this->aAlterField[$sObject])) { $this->aAlterField[$sObject] = []; }
		if(!isset($this->aAlterField[$sObject][$mField])) { $this->aAlterField[$sObject][$mField] = []; }
		$this->aAlterField[$sObject][$mField][] = "@DROP";

		if(isset($this->owl["nest"]["objects"][$sObject]["starred"][$mField])) {
			unset($this->owl["nest"]["objects"][$sObject]["starred"][$mField]);
		}

		return $this;
	}

	public function rename() {
		list($sNewName, $sTitle) = $this->getarguments("newname,title", \func_get_args());

		$sObject = $this->attribute("object");
		$sNewName = $this->FormatName($sNewName);
		if($sObject==null || $sNewName==null) {
			self::errorMessage($this->object, 1001);
			return false;
		} else if(!isset($this->owl["tables"][$sObject])) {
			self::errorMessage($this->object, 1004, $sObject);
			return false;
		} else if(isset($this->owl["tables"][$sNewName]) && $sObject!=$sNewName) {
			self::errorMessage($this->object, 1002, $sNewName);
			return false;
		}

		$this->owl["tables"][$sNewName]							= $this->owl["tables"][$sObject];
		$this->owl["titles"][$sNewName]							= ($sTitle!==null) ? $sTitle : $this->owl["titles"][$sObject];
		if($sObject==$sNewName) { return $this; }

		$this->owl["def"][$sNewName]							= $this->owl["def"][$sObject];
		if(isset($this->owl["nest"]["objects"][$sObject])) {	$this->owl["nest"]["objects"][$sNewName]	= $this->owl["nest"]["objects"][$sObject]; }
		if(isset($this->owl["foreignkeys"][$sObject])) { 		$this->owl["foreignkeys"][$sNewName]		= $this->owl["foreignkeys"][$sObject]; }
		if(isset($this->owl["foreignkeys"][$sObject])) { 		$this->owl["foreignkeys"][$sNewName]		= $this->owl["foreignkeys"][$sObject]; }
		if(isset($this->owl["parents"][$sObject])) { 			$this->owl["parents"][$sNewName]			= $this->owl["parents"][$sObject]; }
		if(isset($this->owl["children"][$sObject])) { 			$this->owl["children"][$sNewName]			= $this->owl["children"][$sObject]; }
		if(isset($this->owl["joins"][$sObject])) { 				$this->owl["joins"][$sNewName]				= $this->owl["joins"][$sObject]; }
		if(isset($this->owl["validator"][$sObject])) { 			$this->owl["validator"][$sNewName]			= $this->owl["validator"][$sObject]; }
		if(isset($this->aAdd[$sObject])) {						$this->aAdd[$sNewName]						= true; }
		unset(
			$this->owl["tables"][$sObject],
			$this->owl["nest"]["objects"][$sObject],
			$this->owl["titles"][$sObject],
			$this->owl["def"][$sObject],
			$this->owl["foreignkeys"][$sObject],
			$this->owl["parents"][$sObject],
			$this->owl["children"][$sObject],
			$this->owl["joins"][$sObject],
			$this->owl["validator"][$sObject],
			$this->aAdd[$sObject]
		);

		foreach($this->owl["parents"] as $sChild => $sParent) {
			if($sObject==$sParent) {
				$this->owl["parents"][$sChild] = $sNewName;
			}
		}

		foreach($this->owl["joins"] as $sJoinWith => &$aJoins) {
			foreach($aJoins as $x => $mJoin) {
				if(!\is_array($mJoin)) { $mJoin = [$mJoin]; }
				foreach($mJoin as $y => $sJoin) {
					$aJoin = \explode(":", $sJoin);
					if($aJoin[1]==$sObject) {
						$aJoin[1] = $sNewName;
						$mJoin[$y] = \implode(":", $aJoin);
					}
				}
				$aJoins[$x] = (\count($mJoin)>1) ? $mJoin : \current($mJoin);

			}
			unset($mJoin);
		}
		unset($aJoins);

		foreach($this->owl["children"] as &$aChildren) {
			foreach($aChildren as $sChildName) {
				if($sChildName==$sObject) {
					$aChildren[$sNewName] = $sNewName;
					unset($aChildren[$sObject]);
				}
			}
		}

		if(!isset($this->aAdd[$sNewName])) {
			if(!\array_key_exists($sObject, $this->aAlterTable)) { $this->aAlterTable[$sObject] = []; }
			$this->aAlterTable[$sObject]["rename"] = $sNewName;
		}
		$this->SetObject($sNewName);
		return $this;
	}

	public function save() {
		return \json_encode($this->owl);
	}

	public function twin() {
		list($sTwin, $sTitle) = $this->getarguments("newname,title", \func_get_args());
		$sObject = $this->attribute("object");
		$sTwin = $this->FormatName($sTwin);
		$this->create($sTwin, $sTitle, $this->owl["def"][$sObject]);
		if(isset($this->owl["foreignkeys"][$sObject])) { $this->owl["foreignkeys"][$sTwin] = $this->owl["foreignkeys"][$sObject]; }
		if(isset($this->owl["joins"][$sObject])) {
			$aJoins = [];
			foreach($this->owl["joins"][$sObject] as $sJoin) {
				$aJoin = \explode(":", $sJoin);
				if($sObject==$aJoin[1]) { $aJoin[1] = $sTwin; }
				$aJoin[2] = $sTwin."_".$aJoin[1];
				$aJoins[] = \implode(":", $aJoin);
			}
			$this->owl["joins"][$sTwin] = $aJoins;
		}
		if(isset($this->owl["validator"][$sObject])) { $this->owl["validator"][$sTwin] = $this->owl["validator"][$sObject]; }
		foreach($this->owl["children"] as $sParent => $aChildren) {
			if(isset($aChildren[$sObject])) {
				$aChildren[$sTwin] = $sTwin;
				$this->owl["children"][$sParent] = $aChildren;
			}
		}

		return $this;
	}

	public function unjoin() {
		list($sJoined) = $this->getarguments("entity", \func_get_args());

		$sObject = $this->attribute("object");
		$sJoined = $this->FormatName($sJoined);
		if($sObject==null || $sJoined==null) {
			self::errorMessage($this->object, 1001);
			return false;
		} else if(!isset($this->owl["tables"][$sObject])) {
			self::errorMessage($this->object, 1004, $sObject);
			return false;
		} else if(!isset($this->owl["views"][$sJoined]) && !isset($this->owl["foreigns"][$sJoined]) && !isset($this->owl["tables"][$sJoined])) {
			self::errorMessage($this->object, 1004, $sJoined);
			return false;
		}

		// padre-hijo
		foreach($this->owl["parents"] as $sParent => $sChild) {
			if($sChild==$sJoined && $sObject==$sParent) { unset($this->owl["parents"][$sParent]); }
		}
		unset($this->owl["children"][$sJoined][$sObject]);

		// join con tablas y views
		if(isset($this->owl["joins"][$sObject])) {
			$aFlatJoins = $this->FlatJoins($this->owl["joins"][$sObject]);
			foreach($aFlatJoins as $sJoin) {
				$aJoin = \explode(":", $sJoin);
				if($aJoin[1]==$sJoined) {
					if(\is_array($this->owl["joins"][$sObject][$aJoin[0]])) {
						$n = \array_search($sJoin, $this->owl["joins"][$sObject][$aJoin[0]]);
						unset($this->owl["joins"][$sObject][$aJoin[0]][$n]);
						if(!\count($this->owl["joins"][$sObject][$aJoin[0]])) {
							unset($this->owl["joins"][$sObject][$aJoin[0]]);
						}
					} else {
						unset($this->owl["joins"][$sObject][$aJoin[0]]);
					}
				}
			}
		}

		// views
		unset($this->owl["foreigns"][$sJoined]["joins"][$sObject]);
		unset($this->owl["views"][$sJoined]["joins"][$sObject]);

		return $this;
	}

	public function useroles() {
		list($nRoles) = $this->getarguments("roles", \func_get_args());
		$sObject = $this->attribute("object");
		$this->owl["nest"]["objects"][$sObject]["roles"] = $nRoles;
		return $this;
	}

	private function ViewFields() {
		list($sObject) = $this->getarguments("entity", \func_get_args());
		return $this->db->describeView($sObject);
	}

	private function ForeignTableFields() {
		list($sObject) = $this->getarguments("entity", \func_get_args());
		$sObject = $this->FormatName($sObject);
		$aFields = $this->db->describe($sObject);
		$aForeign = [];
		foreach($aFields as $aField) {
			$sType = \substr($aField["type"], 0, \strpos($aField["type"], ")"));
			$aType = \explode("(", $sType);
			$aForeign[$aField["name"]] = [
				"name" => $aField["name"],
				"label" => \ucfirst(\str_replace("_", " ", \strtolower($aField["name"]))),
				"type" => $aType[0],
				"length" => $aType[1]
			];
		}

		return $aForeign;
	}

	public function types() {
		$aTypes = $this->aTypes;
		\asort($aTypes);
		return $aTypes;
	}

	public function fields() {
		$aFields = $this->aFields;
		\asort($aFields);
		return $aFields;
	}

	public function createFromFile() {
		list($sFilePath, $sObject, $sTitle) = $this->getarguments("filepath,entity,title", \func_get_args());
		$sType = \strtolower(\pathinfo($sFilePath, PATHINFO_EXTENSION));

		switch($sType) {
			case "xlsx":
			case "xls":
				$nIni = 2;
				$xls = self::call("excel")->load($sFilePath);
				$xls->calculate(true);
				$aSource["title"] = $xls->getTitle();
				$xls->unmergeAll(true);
				$aGetColumns = $xls->row(1);
				$aData = $xls->getall();
				break;

			case "txt":
			case "csv":
				$nIni = 1;
				$csv = self::call("file")->load($sFilePath);
				$sData = $csv->read();
				$aData = self::call("shift")->convert($sData, "csv-array", ["enclosed"=>$this->enclosed, "splitter"=>$this->splitter, "eol"=>$this->eol]);
				$aGetColumns = $aData[0];
				break;
		}

		// columnas
		$aColumns = $aFields = [];
		foreach($aGetColumns as $sColumn) {
			$sFieldName = $this->FormatName($sColumn);
			if($sFieldName=="") { break; }
			$aFields[] = $sFieldName;
			$aColumns[$sFieldName] = ["label"=>$sColumn, "alias"=>"text", "null"=>true];
		}

		$this->aLoadData[$sObject] = ["data"=>$aData, "fields"=>$aFields];
		$this->aLoadDataIndex[$sObject] = $sFilePath;
		return $this->create($sObject, $sTitle, $aColumns);
	}

	public function createFromYaml() {
		list($sFilePath) = $this->getarguments("filepath", \func_get_args());
		$sType = \strtolower(\pathinfo($sFilePath, PATHINFO_EXTENSION));

		$yml = self::call("file")->load($sFilePath);
		$sData = $yml->read();
		$aData = self::call("shift")->convert($sData, "yml-array");

		foreach($aData as $sTitle => $mDefinition) {
			$sObject = $this->FormatName($sTitle);
			if(\is_string($mDefinition)) {
				if(!$this->preset($mDefinition, $sObject, $sTitle)) { return false; }
			} else {
				$aColumns = [];
				foreach($mDefinition as $sField) {
					if(\is_string($sField)) {
						$aField = \explode(":", $sField);
						$sField = $this->FormatName($aField[0]);
						if($aField[1][0]=="@") {
							$aField[1] = "@".$this->FormatName($aField[1]);
							$aField = \array_combine(["label", "type"], $aField);
						} else {
							$aField = \array_combine(["label", "alias"], $aField);
						}
						$aColumns[$sField] = $aField;
					}
				}

				if(!$this->create($sObject, $sTitle, $aColumns)) { return false; }
			}
		}

		return $this;
	}

	public function normalize() {
		list($sField, $sNewObject, $sTitle) = $this->getarguments("field,newname,title", \func_get_args());

		$sObject = $this->attribute("object");
		$sField = $this->FormatName($sField);
		$sNewObject = $this->FormatName($sNewObject);
		$aColumns = [
			"code" => ["alias"=>"code", "label"=>$this->normalize_code],
			"nombre" => ["alias"=>"name", "label"=>$this->normalize_name]
		];
		$create = $this->create($sNewObject, $sTitle, $aColumns);

		$sHash = self::call()->unique(8);
		$this->aNormalize[$sNewObject] = [$sObject, $sField, $sHash];
		if(!$this->bAutoNormalize) {
			$sLabel = $this->owl["tables"][$sObject][$sField];
			$this->select($sObject)
				->alter($sField, ["name"=>$sField."_".$sHash, "label"=>$sLabel."_source"])
				->add($sField, ["type"=>"@".$sNewObject, "label"=>$sLabel])
			;
		}
		$this->SetObject($sObject);
		return $create;
	}

	public function presetfields() {
		return $this->aPresetFields;
	}

	public function view() {
		list($sObject, $sTitle) = $this->getarguments("entity,title", \func_get_args());
		$sObject = $this->FormatName($sObject);
		if($sObject==null) {
			self::errorMessage($this->object, 1001);
			return false;
		} else if(isset($this->owl["views"][$sObject]) || isset($this->owl["foreigns"][$sObject])) {
			self::errorMessage($this->object, 1002, $sObject);
			return false;
		}

		if($sTitle===null) { $sTitle = $sObject; }
		$aFields = $this->ViewFields($sObject);
		$this->owl["views"][$sObject] = ["title"=>$sTitle, "fields"=>$aFields];
		$this->owl["nest"]["objects"][$sObject]	= ["left"=>0, "top"=>0];
		return $this;
	}

	public function foreign() {
		list($sObject, $sTitle) = $this->getarguments("entity,title", \func_get_args());
		$sObject = $this->FormatName($sObject);
		if($sObject==null) {
			self::errorMessage($this->object, 1001);
			return false;
		} else if(isset($this->owl["views"][$sObject]) || isset($this->owl["foreigns"][$sObject])) {
			self::errorMessage($this->object, 1002, $sObject);
			return false;
		}

		if($sTitle===null) { $sTitle = $sObject; }
		$aFields = $this->ForeignTableFields($sObject);
		$this->owl["foreigns"][$sObject] = ["title"=>$sTitle, "fields"=>$aFields];
		$this->owl["nest"]["objects"][$sObject]	= ["left"=>0, "top"=>0];
		return $this;
	}

	private function CreateStructure() {
		$sSQL = self::call("owl")->connect($this->db)->dbStructure();
		return $sSQL."\n\n-- PROJECT ENTITIES ------------------------------------------------------------\n";
	}

	private function CreateTableStructure($sTable, $aTableFields, $bCreate=false) {
		$aFields = $aAlter = $aIndex = $aIndexAlter = [];

		foreach($aTableFields as $sField => &$aField) {
			$aField["name"] = $sField;
		}

		if($this->bUpdate==false || isset($this->aAdd[$sTable]) || isset($this->aAlterField[$sTable]) || $bCreate) {
			if(\array_key_exists("pid", $aTableFields)) { $aTableFields["pid"] = ["name"=>"pid", "type"=>"INT", "null"=>"true", "index"=>"INDEX"]; }
			\array_unshift($aTableFields,
				["name"=>"id", "type"=>"INT", "index"=>"PRIMARY", "autoinc"=>"true"],
				["name"=>"imya", "type"=>"CHAR", "length"=>"32", "index"=>"UNIQUE"],
				["name"=>"state", "type"=>"ENUM", "length"=>"'0','1'", "default"=>"'1'", "null"=>true, "comment"=>"NULL=eliminado, 0=inactivo, 1=activo"]
			);
		}

		$sSQLStructure = "";
		if($this->bUpdate==false || isset($this->aAdd[$sTable]) || $bCreate) {
			$sSQLStructure .= "-- ".$sTable." --\n";
			$sSQLStructure .= $this->jsql->query([
				"query" => "create",
				"table" => $sTable,
				"drop" => true,
				"comment" => (!empty($this->owl["nest"]["objects"][$sTable]["comment"]) ? \addslashes($this->owl["nest"]["objects"][$sTable]["comment"]) : ""),
				"columns" => $aTableFields
			]);
			$sSQLStructure .= "\n";

			if(\is_array($aIndex) && \count($aIndex)) { $sSQLStructure .= \implode("\n", $aIndex)."\n"; }
			$sSQLStructure .= "\n";
		} else if(isset($this->aAlterField[$sTable])) {
			$aIndex = [];
			$sSQLStructure .= "-- ".$sTable." --\n";

			foreach($this->aAlterField[$sTable] as $sField => $aAfters) {
				foreach($aAfters as $sAfter) {
					if($sField=="pid" && $sAfter!=="@DROP") {
						$aIndex[] = $this->jsql->query(["query"=>"index", "table"=>$sTable, "column"=>"pid"]);
					}
					if($sAfter==="@DROP") {
						$sSQLStructure .= $this->jsql->query(["query"=>"coldrop", "table"=>$sTable, "column"=>$sField])."\n";
					} else if($sAfter==="@MODIFY") {
						$sSQLStructure .= $this->jsql->query(["query"=>"colmodify", "table"=>$sTable, "column"=>$aTableFields[$sField]])."\n";
					} else if($sAfter==="@CHANGE") {
						$sSQLStructure .= $this->jsql->query(["query"=>"colrename", "table"=>$sTable, "column"=>$aTableFields[$sField]])."\n";
					} else {
						if($sAfter!==true) { $aFields[$sField]["after"] = $sAfter; }
						$sSQLStructure .= $this->jsql->query(["query"=>"coladd", "table"=>$sTable, "column"=>$aTableFields[$sField]])."\n";
					}
				}

				if(isset($aTableFields[$sField]["oldname"]) && !empty($aTableFields[$sField]["oldindex"])) {
					$aIndex[] = $this->jsql->query(["query"=>"indexdrop", "table"=>$sTable, "column"=>$aTableFields[$sField]["oldname"]])."\n";
				}

				if(!empty($aIndexAlter[$sField])) {
					$aIndex[] = $aIndexAlter[$sField];
				}
			}

			if(\is_array($aIndex) && \count($aIndex)) {
				\rsort($aIndex);
				$sSQLStructure .= \implode("\n", $aIndex)."\n";
			}
			$sSQLStructure .= "\n";
		}

		if(isset($aField)) { unset($aField["oldname"], $aField["oldindex"]); }

		return $sSQLStructure;
	}

	private function DefJoins($sObject, $sField, $sType=null, $sLabel=null) {
		if($sType!==null) {
			$aJoin = \explode(":", \substr($sType,1));
			if($sField!="pid") {
				if(!isset($this->owl["tables"][$aJoin[0]])) {
					$sTitle = $this->FormatTitle($sLabel ? $sLabel : $sField);
					$sNewObject = $this->FormatName($aJoin[0]);
					if(!isset($this->aAutoNormalize[$sNewObject])) {
						$this->aAutoNormalize[$sNewObject] = [$sField, $sNewObject, $sTitle];
					}
				}
				if(!isset($this->owl["joins"][$sObject])) { $this->owl["joins"][$sObject] = []; }
				$this->owl["joins"][$sObject][] = $sField.":".$aJoin[0];
			} else {
				if(!isset($this->owl["children"][$aJoin[0]])) { $this->owl["children"][$aJoin[0]] = []; }
				if(isset($this->owl["parents"][$sObject])) {
					if(isset($this->owl["children"][$this->owl["parents"][$sObject]])) {
						unset($this->owl["children"][$this->owl["parents"][$sObject]][$sObject]);
					}
				}
				$this->owl["children"][$aJoin[0]][$sObject] = $sObject;
				$this->owl["parents"][$sObject] = $aJoin[0];
			}
		} else {
			if(isset($this->owl["joins"][$sObject])) {
				foreach($this->owl["joins"][$sObject] as $sIndex=>$sJoin) {
					if(\is_array($sJoin)) {
						foreach($sJoin as $nJoinMulti => $sJoinMulti) {
							if(\strpos($sJoinMulti, $sField.":")===0) { unset($this->owl["joins"][$sObject][$sIndex][$nJoinMulti]); }
							if(!\count($this->owl["joins"][$sObject][$sIndex])) {
								unset($this->owl["joins"][$sObject][$sIndex]);
							}
						}
					} else {
						if(\strpos($sJoin, $sField.":")===0) {
							unset($this->owl["joins"][$sObject][$sIndex]);
						}
					}
				}
				if(!\count($this->owl["joins"][$sObject])) { unset($this->owl["joins"][$sObject]); }
			}
		}
	}

	private function DescribeColumns($sTable, $sAlias) {
		$aColumns = (isset($this->owl["tables"][$sTable])) ? $this->owl["tables"][$sTable] : (isset($this->owl["views"][$sTable]) ? $this->owl["views"][$sTable]["fields"] : []);
		$aDescribe = [];
		foreach($aColumns as $sColumn => $sLabel) {
			$aDescribe[] = $this->db->quote($sAlias).".".$this->db->quote($sColumn)." AS '".$sAlias."_".$sColumn."'";
		}
		return $aDescribe;
	}

	private function FlatJoins($aJoins) {
		$aFlats = [];
		foreach($aJoins as $mRef) {
			if(\is_array($mRef)) {
				$aFlats = \array_merge($aFlats, $mRef);
			} else {
				$aFlats[] = $mRef;
			}
		}
		return $aFlats;
	}

	private function FormatName($sName) {
		$sName = \trim($sName);
		$sName = \strtolower($sName);
		$sName = self::call()->unaccented($sName);
		$sName = \str_replace(" ", "_", $sName);
		return \preg_replace("/[^a-z-0-9\_]/is", "", $sName);
	}

	private function FormatTitle($sName) {
		$sName = \trim($sName);
		$sName = \str_replace("_", " ", $sName);
		return \ucwords($sName);
	}

	protected function SetBase($db) {
		$this->jsql = $db->jsql();
		return $db;
	}

	protected function SetObject($sObject) {
		if(isset($this->owl["tables"][$sObject])) {
			$this->attribute("object", $sObject);
			$this->attribute("objtype", "table");
		} else if(isset($this->owl["foreigns"][$sObject])) {
			$this->attribute("object", $sObject);
			$this->attribute("objtype", "foreign");
		} else if(isset($this->owl["views"][$sObject])) {
			$this->attribute("object", $sObject);
			$this->attribute("objtype", "view");
		} else {
			self::errorMessage($this->object, 1004, $sObject);
			return false;
		}

		return $sObject;
	}

	private function Structure() {
		$aSentence = $this->owl;
		$aStructure = $aSentence["tables"];

		if(\is_array($aSentence) && isset($aSentence["titles"])) {
			$aStructure = [];
			foreach($aSentence["titles"] as $sTable => $sTitle) {
				$aColumns = [];
				foreach($aSentence["tables"][$sTable] as $sName => $sLabel) {
					$aColumns[$sName] = ["name" => $sName, "label" => $sLabel];
				}

				if(isset($aSentence["joins"][$sTable])) {
					foreach($aSentence["joins"][$sTable] as $sJoin) {
						$aJoin = \explode(":", $sJoin);
						if(isset($aColumns[$aJoin[0]])) {
							$aColumns[$aJoin[0]]["join"] = $aJoin[1];
						}
					}
				}

				if(isset($aSentence["children"][$sTable])) {
					$aColumns["id"]["children"] = [];
					foreach($aSentence["children"][$sTable] as $sChild) {
						$aColumns["id"]["children"][] = $sChild;
					}
				}

				$sParent = "";
				if(isset($aColumns["pid"]) && isset($aSentence["parents"][$sTable])) {
					$sParent = $aSentence["parents"][$sTable];
					$aColumns["pid"]["join"] = ["table"=>$aSentence["parents"][$sTable], "alias"=>$aSentence["parents"][$sTable]];
				}

				$aStructure[$sTable] = [
					"title"		=> $sTitle,
					"name"		=> $sTable,
					"parent"	=> $sParent,
					"columns"	=> $aColumns
				];
			}

			// joins inversos
			foreach($aSentence["joins"] as $sTable => $aJoins) {
				foreach($aJoins as $sJoin) {
					$aJoin = \explode(":", $sJoin);
					if(isset($aStructure[$aJoin[1]])) {
						if(!isset($aStructure[$aJoin[1]]["columns"]["id"]["rjoin"])) {
							$aStructure[$aJoin[1]]["columns"]["id"]["rjoin"] = [];
						}

						$aStructure[$aJoin[1]]["columns"]["id"]["rjoin"][$sTable] = ["table"=>$sTable, "using"=>$aJoin[0]];
					}
				}
			}
		}

		\ksort($aStructure, SORT_NATURAL);
		return $aStructure;
	}
}

?>