<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# tree
https://hytcom.net/nogal/docs/objects/tree.md
*/
namespace nogal;
class nglTree extends nglBranch implements inglBranch {

	private $aFlat;
	private $aGrouped;

	final protected function __declareArguments__() {
		$vArguments							= [];
		$vArguments["branchdata"]			= ['$mValue'];
		$vArguments["children"]				= ['$mValue', "_children"];
		$vArguments["colid"]				= ['$mValue', "id"];
		$vArguments["colparent"]			= ['$mValue', "parent"];
		$vArguments["column"]				= ['$mValue', "id"];
		$vArguments["id"]					= ['$mValue'];
		$vArguments["separator"]			= ['$mValue', "/"];
		$vArguments["source"]				= ['$aValue'];
		$vArguments["source_type"]			= ['\strtoupper($mValue)', "ARRAY", ["ARRAY","JSON","OBJECT","JSON-FILE","YAML","YAML-FILE","B-ARRAY","B-JSON","B-OBJECT","B-JSON-FILE","B-YAML","B-YAML-FILE"]];

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes 						= [];
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

	public function load() {
		list($mSource,$mParentColumn,$mIdColumn,$mChildren) = $this->getarguments("source,colparent,colid,children", \func_get_args());

		$bByBranchs = false;
		$sType = \strtoupper($this->source_type);
		if($sType[0].$sType[1]=="B-") {
			$sType = \substr($sType, 2);
			$bByBranchs = true;
		}
		switch($sType) {
			case "JSON-FILE":
			case "YAML-FILE":
				$mSource = self::call("file")->load($mSource)->read();

			case "JSON":
			case "YAML":
				$mSource = self::call("shift")->convert($mSource, \strtolower($sType)."-array");
		}

		if($bByBranchs) {
			$this->LoadFlat($mSource, $mParentColumn, $mIdColumn, $mChildren);
		} else {
			$this->LoadTree($mSource, $mParentColumn, $mIdColumn, $mChildren);
		}

		return $this;
	}

	public function tree() {
		return $this->attribute("tree");
	}

	public function flat() {
		return $this->attribute("flat");
	}

	public function get() {
		list($nId) = $this->getarguments("id", \func_get_args());
		if(isset($this->aFlat[$nId])) {
			return $this->aFlat[$nId];
		}
		return null;
	}

	public function parent() {
		list($nId) = $this->getarguments("id", \func_get_args());

		if(isset($this->aFlat[$nId])) {
			$mParent = $this->aFlat[$nId][$this->attribute("parent_column")];
			return (isset($this->aFlat[$mParent])) ? $this->aFlat[$mParent] : 0;
		}

		return null;
	}

	public function trace() {
		list($nId) = $this->getarguments("id", \func_get_args());
		$mIndex =  $this->attribute("parent_column");
		$aTrace = [];
		while($aParent=$this->get($nId)) {
			$nId = $aParent[$mIndex];
			$aTrace[] = $aParent;
		}
		return \array_reverse($aTrace);
	}

	public function children() {
		list($nId) = $this->getarguments("id", \func_get_args());
		$aChildren = $this->attribute("tree");
		if(!$nId) { return $aChildren; }
		$mIndex =  $this->attribute("id_column");
		$mChildren =  $this->attribute("children");
		$aTrace = $this->trace($nId);

		if(\is_array($aTrace) && \count($aTrace)) {
			foreach($aTrace as $aItem) {
				if(empty($aChildren[$aItem[$mIndex]][$mChildren])) { return []; }
				$aChildren = $aChildren[$aItem[$mIndex]][$mChildren];
			}
		}
		return $aChildren;
	}

	public function childrenChain() {
		list($nId,$sSeparator) = $this->getarguments("id,separator", \func_get_args());
		$aChildren = $this->children($nId);
		if(\is_array($aChildren) && \count($aChildren)) {
			$mIndex =  $this->attribute("id_column");
			$mChildren =  $this->attribute("children");
			$aChain = [];
			$this->ChildrenChainer($aChain, $aChildren, $mIndex, $mChildren);
			return ($sSeparator===null) ? $aChain : \implode($sSeparator, $aChain);
		}
		return [];
	}

	public function branch() {
		list($aBranch) = $this->getarguments("branchdata", \func_get_args());

		$mIdColumn = $this->attribute("id_column");
		$mParentColumn = $this->attribute("parent_column");

		if(!isset($aBranch[$mIdColumn])) { $aBranch[$mIdColumn] = $this->NextId(); }
		if(!isset($aBranch[$mParentColumn])) {
			$aBranch[$mParentColumn] = 0;
		} else {
			$aParentTrace = $this->trace($aBranch[$mParentColumn]);
			foreach($aParentTrace as $aTrace) {
				if($aTrace[$mIdColumn]==$aBranch[$mIdColumn]) {
					$aBranch[$mParentColumn] = $aTrace[$mParentColumn];
					break;
				}
			}
		}

		$this->aFlat[$aBranch[$mIdColumn]] = $aBranch;

		$this->Prepare($this->aFlat);
		$this->attribute("flat", $this->aFlat);
		$this->Build();

		return $this;
	}

	public function parentsChain() {
		list($nId,$sColumn,$sSeparator) = $this->getarguments("id,column,separator", \func_get_args());
		$aPaths = $aPath = [];
		foreach($this->trace($nId) as $aBranch) {
			$aPath[] = $aBranch[$sColumn];
		}
		return ($sSeparator===null) ? $aPath : \implode($sSeparator, $aPath);
	}

	public function branches() {
		list($sColumn,$sSeparator) = $this->getarguments("column,separator", \func_get_args());

		$aTree = $this->attribute("tree");
		$mIdColumn = $this->attribute("id_column");
		$mChildren = $this->attribute("children");

		$aPaths = $aPath = [];
		$fBuilder = function($aTree) use (&$fBuilder, &$aPaths, &$aPath, $mChildren, $sColumn, $mIdColumn, $sSeparator) {
			foreach($aTree as $aBranch) {
				\array_push($aPath, $aBranch[$sColumn]);
				$aPaths[$aBranch[$mIdColumn]] = \implode($sSeparator, $aPath);
				if(isset($aBranch[$mChildren])) {
					$fBuilder($aBranch[$mChildren]);
				} else {
					\array_pop($aPath);
				}
			}
			\array_pop($aPath);
		};
		$fBuilder($aTree);

		\natsort($aPaths);
		return $aPaths;
	}

	public function show() {
		list($sColumn) = $this->getarguments("column", \func_get_args());

		$aTree = $this->attribute("tree");
		$mChildren = $this->attribute("children");

		$aPrint = self::call()->treeWalk($aTree, function($aBranch, $nLevel, $bFirst, $bLast) use ($sColumn, $mChildren) {
				$sOutput  = "";
				$sOutput .= ($nLevel) ? \str_repeat("│   ", $nLevel) : "";
				$sOutput .= ($bLast) ? "└─── " : "├─── ";
				$sOutput .= $aBranch[$sColumn];
				$sOutput .= "\n";
				return $sOutput;
			}
		);

		return \implode($aPrint);
	}

	private function LoadFlat($aSource, $mParentColumn, $mIdColumn, $mChildren) {
		$this->attribute("children", $mChildren);
		$this->attribute("id_column", $mIdColumn);
		$this->attribute("parent_column", $mParentColumn);
		$this->Prepare($aSource);
		$this->attribute("flat", $this->aFlat);
		$this->Build();
		return $this;
	}

	private function LoadTree($aSource, $mParentColumn, $mIdColumn, $mChildren) {
		if(\is_array($aSource) && !\count($aSource)) { $aSource = []; }
		$fBuilder = function($aTree) use (&$fBuilder, &$aFlat, $mChildren) {
			if(\is_array($aTree) && \count($aTree)) {
				foreach($aTree as $aBranch) {
					$aChildren = null;
					if(isset($aBranch[$mChildren])) {
						$aChildren = $aBranch[$mChildren];
						unset($aBranch[$mChildren]);
					}

					$aFlat[] = $aBranch;
					if($aChildren!==null) { $fBuilder($aChildren); }
				}
			}
		};
		$aFlat = [];
		$fBuilder($aSource);

		$this->attribute("children", $mChildren);
		$this->attribute("id_column", $mIdColumn);
		$this->attribute("parent_column", $mParentColumn);
		$this->attribute("tree", $aSource);
		$this->Prepare($aFlat);
		$this->attribute("flat", $this->aFlat);

		return $this;
	}

	private function Build() {
		$mIndex = $this->attribute("id_column");
		$mChildren =  $this->attribute("children");

		$aGrouped = $this->aGrouped;
		$fBuilder = function($aSiblings) use (&$fBuilder, $aGrouped, $mIndex, $mChildren) {
			if(\is_array($aSiblings) && \count($aSiblings)) {
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

		\reset($aGrouped);
		$aTree = (\count($aGrouped)) ? $fBuilder(\current($aGrouped)) : [];

		$this->attribute("tree", $aTree);
		return $aTree;
	}

	private function ChildrenChainer(&$aChain, $aData, $mIndex, $mChildren) {
		foreach($aData as $aChild) {
			$aChain[] = $aChild[$mIndex];
			if(!empty($aChild[$mChildren])) {
				$this->ChildrenChainer($aChain, $aChild[$mChildren], $mIndex, $mChildren);
			}
		}
		return $aChain;
	}

	private function NextId() {
		$aIndex = \array_keys($this->aFlat);
		\sort($aIndex, SORT_NATURAL);
		$nLast = \count($aIndex)-1;
		if($nLast<0) { $nLast = 0; }
		$mLast = (!empty($aIndex[$nLast])) ? $aIndex[$nLast] : 0;
		return (\is_numeric($mLast)) ? $mLast+1 : $mLast."0";
	}

	private function Prepare($aSource) {
		$mIdColumn =  $this->attribute("id_column");
		$mParentColumn =  $this->attribute("parent_column");
		$mChildren =  $this->attribute("children_column");

		$aFlat = $aGrouped = [];
		foreach($aSource as $aSubArray) {
			$aFlat[$aSubArray[$mIdColumn]] = $aSubArray;
			if(\array_key_exists($mIdColumn, $aSubArray) && \array_key_exists($mParentColumn, $aSubArray)) {
				if(empty($aGrouped[$aSubArray[$mParentColumn]])) { $aGrouped[$aSubArray[$mParentColumn]] = []; }
				$aGrouped[$aSubArray[$mParentColumn]][$aSubArray[$mIdColumn]] = $aSubArray;
			}
		}

		$this->aFlat = $aFlat;
		$this->aGrouped = $aGrouped;
	}
}

?>