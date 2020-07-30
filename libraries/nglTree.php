<?php

namespace nogal;

/** 
Tree format
$b = array (
	1 => 
	array (
	  'id' => '1',
	  'parent' => '0',
	  'name' => 'caninos',
	  '_children' => 
	  array (
		3 => 
		array (
		  'id' => '3',
		  'parent' => '1',
		  'name' => 'perro',
		),
		4 => 
		array (
		  'id' => '4',
		  'parent' => '1',
		  'name' => 'lobo',
		),
		5 => 
		array (
		  'id' => '5',
		  'parent' => '1',
		  'name' => 'coyote',
		),
	  ),
	),
	2 => 
	array (
	  'id' => '2',
	  'parent' => '0',
	  'name' => 'felinos',
	  '_children' => 
	  array (
		6 => 
		array (
		  'id' => '6',
		  'parent' => '2',
		  'name' => 'gato',
		),
		7 => 
		array (
		  'id' => '7',
		  'parent' => '2',
		  'name' => 'león',
		),
		8 => 
		array (
		  'id' => '8',
		  'parent' => '2',
		  'name' => 'tigre',
		),
		9 => 
		array (
		  'id' => '9',
		  'parent' => '2',
		  'name' => 'pantera',
		),
	  ),
	),
);

Flat format
$a = [];
$a[] = array("id"=>"1", "parent"=>"0", "name"=>"caninos");
$a[] = array("id"=>"2", "parent"=>"0", "name"=>"felinos");
$a[] = array("id"=>"3", "parent"=>"1", "name"=>"perro");
$a[] = array("id"=>"4", "parent"=>"1", "name"=>"lobo");
$a[] = array("id"=>"5", "parent"=>"1", "name"=>"coyote");
$a[] = array("id"=>"6", "parent"=>"2", "name"=>"gato");
$a[] = array("id"=>"7", "parent"=>"2", "name"=>"león");
$a[] = array("id"=>"8", "parent"=>"2", "name"=>"tigre");

print_r($ngl("tree")->loadflat($a)->node(array("id"=>"9", "parent"=>"2", "name"=>"pantera"))->show("name"));

} **/
class nglTree extends nglBranch implements inglBranch {
	
	private $aFlat;
	private $aGrouped;

	final protected function __declareArguments__() {
		$vArguments							= array();
		$vArguments["source"]				= array('$aValue');
		$vArguments["id"]					= array('$mValue');
		$vArguments["colparent"]			= array('$mValue', "parent");
		$vArguments["colid"]				= array('$mValue', "id");
		$vArguments["children"]				= array('$mValue', "_children");
		$vArguments["column"]				= array('$mValue', "id");
		$vArguments["nodedata"]				= array('$mValue');
		$vArguments["separator"]			= array('$mValue', "/");

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes 						= array();
		$vAttributes["children"]			= null;
		$vAttributes["flat"]				= null;
		$vAttributes["id_column"]			= null;
		$vAttributes["parent_column"]		= null;
		$vAttributes["tree"]				= null;
		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
	}

	public function loadflat() {
		list($aSource,$mParentColumn,$mIdColumn,$mChildren) = $this->getarguments("source,colparent,colid,children", func_get_args());
		
		$this->attribute("children", $mChildren);
		$this->attribute("id_column", $mIdColumn);
		$this->attribute("parent_column", $mParentColumn);
		$this->Prepare($aSource);
		$this->attribute("flat", $this->aFlat);
		$this->Build();

		return $this;
	}

	public function loadtree() {
		list($aSource,$mParentColumn,$mIdColumn,$mChildren) = $this->getarguments("source,colparent,colid,children", func_get_args());

		if(!count($aSource)) { $aSource = array(); }
		$fBuilder = function($aTree) use (&$fBuilder, &$aFlat, $mChildren) {
			foreach($aTree as $aBranch) {
				$aChildren = null;
				if(isset($aBranch[$mChildren])) {
					$aChildren = $aBranch[$mChildren];
					unset($aBranch[$mChildren]);
				}
				
				$aFlat[] = $aBranch;
				if($aChildren!==null) { $fBuilder($aChildren); }
			}
		};
		$aFlat = array();		
		$fBuilder($aSource);

		$this->attribute("children", $mChildren);
		$this->attribute("id_column", $mIdColumn);
		$this->attribute("parent_column", $mParentColumn);
		$this->attribute("tree", $aSource);

		$this->Prepare($aFlat);
		$this->attribute("flat", $this->aFlat);

		return $this;
	}

	private function Prepare($aSource) {
		$mIdColumn =  $this->attribute("id_column");
		$mParentColumn =  $this->attribute("parent_column");
		
		$aFlat = $aGrouped = array();
		foreach($aSource as $aSubArray) {
			$aFlat[$aSubArray[$mIdColumn]] = $aSubArray;
			$aGrouped[$aSubArray[$mParentColumn]][$aSubArray[$mIdColumn]] = $aSubArray;
		}

		$this->aFlat = $aFlat;
		$this->aGrouped = $aGrouped;
	}

	private function Build() {
		$mIndex = $this->attribute("id_column");
		$mChildren =  $this->attribute("children");

		$aGrouped = $this->aGrouped;
		$fBuilder = function($aSiblings) use (&$fBuilder, $aGrouped, $mIndex, $mChildren) {
			if(count($aSiblings)) {
				foreach($aSiblings as $mKey => $aSibling) {
					$mCurrent = $aSibling[$mIndex];
					if(isset($aGrouped[$mCurrent])) {
						$aSibling[$mChildren] = $fBuilder($aGrouped[$mCurrent]);
					}
					$aSiblings[$mKey] = $aSibling;
				}
			}

			return $aSiblings;
		};

		reset($aGrouped);
		$aTree = (count($aGrouped)) ? $fBuilder(current($aGrouped)) : array();

		$this->attribute("tree", $aTree);
		return $aTree;
	}
	
	private function NextId() {
		$aIndex = array_keys($this->aFlat);
		sort($aIndex, SORT_NATURAL);
		$mLast = $aIndex[count($aIndex)-1];
		return (is_numeric($mLast)) ? $mLast+1 : $mLast."0";
	}

	public function tree() {
		return $this->attribute("tree");
	}

	public function flat() {
		return $this->attribute("flat");
	}

	public function parent() {
		list($mId) = $this->getarguments("id", func_get_args());

		if(isset($this->aFlat[$mId])) {
			$mParent = $this->aFlat[$mId][$this->attribute("parent_column")];
			return (isset($this->aFlat[$mParent])) ? $this->aFlat[$mParent] : 0;
		}
		
		return null;
	}
	
	public function trace() {
		list($mId) = $this->getarguments("id", func_get_args());
		
		$mIndex =  $this->attribute("id_column");
		$aTrace = array();
		while($aParent=$this->parent($mId)) {
			$mId = $aParent[$mIndex];
			$aTrace[] = $aParent;
		}
		
		return array_reverse($aTrace);
	}
	
	public function children() {
		list($mId) = $this->getarguments("id", func_get_args());
		
		$aChildren = $this->attribute("tree");
		if(!$mId) { return $aChildren; }

		$mIndex =  $this->attribute("id_column");
		$mChildren =  $this->attribute("children");

		$aTrace = $this->trace($mId);
		if(count($aTrace)) {
			$aFirst = array_shift($aTrace);
			$aChildren = $aChildren[$aFirst[$mIndex]];

			if(count($aTrace)) {
				foreach($aTrace as $aItem) {
					$aChildren = $aChildren[$mChildren][$aItem[$mIndex]];
				}
			}
		}

		$aChildren = (!isset($aFirst)) ? $aChildren[$mId] : $aChildren[$mChildren][$mId];
		$aChildren = (isset($aChildren[$mChildren])) ? $aChildren[$mChildren] : null;
		return $aChildren;
	}
	
	/* si existe lo modifica, sino lo agrega */
	public function node() {
		list($aNode) = $this->getarguments("nodedata", func_get_args());
		
		$mIdColumn = $this->attribute("id_column");
		$mParentColumn = $this->attribute("parent_column");

		if(!isset($aNode[$mIdColumn])) { $aNode[$mIdColumn] = $this->NextId(); }
		if(!isset($aNode[$mParentColumn])) {
			$aNode[$mParentColumn] = 0;
		} else {
			$aParentTrace = $this->trace($aNode[$mParentColumn]);
			foreach($aParentTrace as $aTrace) {
				if($aTrace[$mIdColumn]==$aNode[$mIdColumn]) {
					$aNode[$mParentColumn] = $aTrace[$mParentColumn];
					break;
				}
			}
		}

		$this->aFlat[$aNode[$mIdColumn]] = $aNode;

		$this->Prepare($this->aFlat);
		$this->attribute("flat", $this->aFlat);
		$this->Build();
		
		return $this;
	}

	public function show() {
		list($sColumn) = $this->getarguments("column", func_get_args());
		
		$aTree = $this->attribute("tree");
		$mChildren = $this->attribute("children");

		$aPrint = self::call()->treeWalk($aTree, function($aNode, $nLevel, $bFirst, $bLast) use ($sColumn, $mChildren) {
				$sOutput  = "";
				$sOutput .= ($nLevel) ? str_repeat("│   ", $nLevel) : "";
				$sOutput .= ($bLast) ? "└─── " : "├─── ";
				$sOutput .= $aNode[$sColumn];
				$sOutput .= "\n";
				return $sOutput;
			}
		);

		return implode($aPrint);
	}
	
	public function paths() {
		list($sColumn,$sSeparator) = $this->getarguments("column,separator", func_get_args());
		
		$aTree = $this->attribute("tree");
		$mIdColumn = $this->attribute("id_column");
		$mChildren = $this->attribute("children");

		$aPaths = $aPath = array();
		$fBuilder = function($aTree) use (&$fBuilder, &$aPaths, &$aPath, $mChildren, $sColumn, $mIdColumn, $sSeparator) {
			foreach($aTree as $aBranch) {
				array_push($aPath, $aBranch[$sColumn]);
				$aPaths[$aBranch[$mIdColumn]] = implode($sSeparator, $aPath);
				if(isset($aBranch[$mChildren])) {
					$fBuilder($aBranch[$mChildren]);
				} else {
					array_pop($aPath);
				}
			}
			array_pop($aPath);
		};
		$fBuilder($aTree);
		
		natsort($aPaths);
		return $aPaths;
	}
}

?>