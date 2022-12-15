<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# unicode
https://hytcom.net/nogal/docs/objects/unicode.md
*/
namespace nogal;
class nglUnicode extends nglTrunk {

	protected $class		= "nglUnicode";
	protected $me			= "unicode";
	protected $object		= "unicode";
	private $bDisable 		= false;
	private $vUTF8Groups 	= [];

	public function __builder__() {
		$this->bDisable = (\strtoupper(NGL_CHARSET)=="UTF-8") ? false : true;

		$vUTF8Groups = [
			["SYM", "CONTROL", 0, 31],
			["SYM", "LATIN_BASIC_SYMBOLS", 32, 47],
			["NUM", "LATIN_BASIC_NUMBERS", 48, 57],
			["SYM", "LATIN_BASIC_SYMBOLS", 58, 64],
			["ABC", "LATIN_BASIC_UPPERCASE", 65, 90],
			["SYM", "LATIN_BASIC_SYMBOLS", 91, 96],
			["ABC", "LATIN_BASIC_LOWERCASE", 97, 122],
			["SYM", "LATIN_BASIC_SYMBOLS", 123, 127],
			["SYM", "LATIN1_CONTROL", 128, 159],
			["SYM", "LATIN1_SYMBOLS", 160, 191],
			["ABC", "LATIN1_SUPP", 192, 255],
			["ABC", "LATIN_EXT_A", 256, 383],
			["ABC", "LATIN_EXT_B", 384, 591],
			["ABC", "IPA", 592, 687],
			["SYM", "SPACING", 688, 767],
			["SYM", "DIACRITICAL_MARKS", 768, 879],
			["ABC", "GREEK", 880, 1023],
			["ABC", "CYRILLIC", 1024, 1279],
			["ABC", "CYRILLIC", 1280, 1327],
			["ABC", "ARMENIAN", 1328, 1423],
			["ABC", "HEBREW", 1424, 1535],
			["ABC", "ARABIC", 1536, 1791],
			["ABC", "SYRIAC", 1792, 1871],
			["ABC", "ARABIC", 1872, 1919],
			["ABC", "THAANA", 1920, 1983],
			["ABC", "NKO", 1984, 2047],
			["ABC", "SAMARITAN", 2048, 2111],
			["ABC", "MANDAIC", 2112, 2143],
			["ABC", "ARABIC", 2208, 2303],
			["ABU", "DEVANAGARI", 2304, 2431],
			["ABU", "BENGALI", 2432, 2559],
			["ABU", "GURMUKHI", 2560, 2687],
			["ABU", "GUJARATI", 2688, 2815],
			["ABU", "ORIYA", 2816, 2943],
			["ABU", "TAMIL", 2944, 3071],
			["ABU", "TELUGU", 3072, 3199],
			["ABU", "KANNADA", 3200, 3327],
			["ABU", "MALAYALAM", 3328, 3455],
			["ABU", "SINHALA", 3456, 3583],
			["ABU", "THAI", 3584, 3711],
			["ABU", "LAO", 3712, 3839],
			["ABU", "TIBETAN", 3840, 4095],
			["ABU", "MYANMAR", 4096, 4255],
			["ABU", "GEORGIAN", 4256, 4351],
			["ABU", "HANGUL_JAMO", 4352, 4607],
			["ABU", "ETHIOPIC", 4608, 4991],
			["ABU", "ETHIOPIC", 4992, 5023],
			["SYL", "CHEROKEE", 5024, 5119],
			["ABU", "ABORIGINAL", 5120, 5759],
			["ABC", "OGHAM", 5760, 5791],
			["ABC", "RUNIC", 5792, 5887],
			["ABU", "TAGALOG", 5888, 5919],
			["ABU", "HANUNOO", 5920, 5951],
			["ABU", "BUHID", 5952, 5983],
			["ABU", "TAGBANWA", 5984, 6015],
			["ABU", "KHMER", 6016, 6143],
			["ABU", "MONGOLIAN", 6144, 6319],
			["ABU", "ABORIGINAL", 6320, 6399],
			["ABU", "LIMBU", 6400, 6479],
			["ABU", "TAI", 6480, 6527],
			["ABC", "TAI", 6528, 6623],
			["SYM", "KHMER", 6624, 6655],
			["ABU", "BUGINESE", 6656, 6687],
			["ABU", "TAI", 6688, 6831],
			["SYM", "DIACRITICAL_MARKS", 6832, 6911],
			["ABU", "BALINESE", 6912, 7039],
			["ABU", "SUNDANESE", 7040, 7103],
			["ABU", "BATAK", 7104, 7167],
			["ABU", "LEPCHA", 7168, 7247],
			["ABC", "OL_CHIKI", 7248, 7295],
			["ABU", "SUNDANESE", 7360, 7375],
			["SYM", "VEDIC", 7376, 7423],
			["SYM", "PHONETIC", 7424, 7551],
			["SYM", "PHONETIC", 7552, 7615],
			["SYM", "DIACRITICAL_MARKS", 7616, 7679],
			["SYM", "LATIN_EXT", 7680, 7935],
			["SYM", "GREEK", 7936, 8191],
			["SYM", "PUNCTUATION", 8192, 8303],
			["SYM", "SUP_SUB_SCRIPTS", 8304, 8351],
			["SYM", "CURRENCY", 8352, 8399],
			["SYM", "DIACRITICAL_MARKS", 8400, 8447],
			["SYM", "LETTERLIKE", 8448, 8527],
			["SYM", "NUMBER", 8528, 8591],
			["SYM", "ARROWS", 8592, 8703],
			["SYM", "MATHEMATICAL", 8704, 8959],
			["SYM", "TECHNICAL", 8960, 9215],
			["SYM", "CONTROL_PICTURES", 9216, 9279],
			["SYM", "OPTICAL", 9280, 9311],
			["SYM", "ALPHANUMERICS", 9312, 9471],
			["SYM", "BOX_DRAWINGS", 9472, 9599],
			["SYM", "BLOCK_ELEMENTS", 9600, 9631],
			["SYM", "GEOMETRIC_SHAPES", 9632, 9727],
			["SYM", "MISCELLANEOUS", 9728, 9983],
			["SYM", "DINGBATS", 9984, 10175],
			["SYM", "MATHEMATICAL", 10176, 10223],
			["SYM", "ARROWS", 10224, 10239],
			["SYM", "BRAILLE", 10240, 10495],
			["SYM", "ARROWS", 10496, 10623],
			["SYM", "MATHEMATICAL", 10624, 10751],
			["SYM", "MATHEMATICAL", 10752, 11007],
			["SYM", "MISCELLANEOUS", 11008, 11263],
			["ABC", "GLAGOLITIC", 11264, 11359],
			["ABC", "LATIN_EXT_C", 11360, 11391],
			["ABC", "COPTIC", 11392, 11519],
			["ABC", "GEORGIAN", 11520, 11567],
			["ABY", "TIFINAGH", 11568, 11647],
			["ABU", "ETHIOPIC", 11648, 11743],
			["ABC", "CYRILLIC", 11744, 11775],
			["SYM", "PUNCTUATION", 11776, 11903],
			["SYM", "CJK", 11904, 12031],
			["SYM", "KANGXI", 12032, 12255],
			["SYM", "IDEOGRAPHIC", 12272, 12287],
			["SYM", "CJK", 12288, 12351],
			["SYL", "HIRAGANA", 12352, 12447],
			["SYL", "KATAKANA", 12448, 12543],
			["SYL", "BOPOMOFO", 12544, 12591],
			["SYL", "HANGUL", 12592, 12687],
			["SYL", "KANBUN", 12688, 12703],
			["SYL", "BOPOMOFO", 12704, 12735],
			["SYL", "CJK", 12736, 12783],
			["SYL", "KATAKANA", 12784, 12799],
			["SYL", "CJK", 12800, 13055],
			["SYL", "CJK", 13056, 13311],
			["SYL", "IDEOGRAPHIC", 13312, 19893],
			["SYL", "YIJING", 19904, 19967],
			["SYL", "IDEOGRAPHIC", 19968, 40908],
			["SYL", "YI", 40960, 42127],
			["SYL", "YI", 42128, 42191],
			["SYM", "LISU", 42192, 42239],
			["SYL", "VAI", 42240, 42559],
			["ABC", "CYRILLIC", 42560, 42655],
			["SYL", "BAMUM", 42656, 42751],
			["SYM", "TONE_LETTERS", 42752, 42783],
			["ABC", "LATIN_EXT_D", 42784, 43007],
			["ABU", "SYLOTI", 43008, 43055],
			["NUM", "INDIC_NUMBER", 43056, 43071],
			["ABU", "PHAGS_PA", 43072, 43135],
			["ABC", "SAURASHTRA", 43136, 43231],
			["ABU", "DEVANAGARI", 43232, 43263],
			["ABU", "KAYAH", 43264, 43311],
			["ABU", "REJANG", 43312, 43359],
			["ABC", "HANGUL", 43360, 43391],
			["ABU", "JAVANESE", 43392, 43487],
			["SYM", "MYANMAR", 43488, 43519],
			["ABU", "CHAM", 43520, 43615],
			["ABU", "MYANMAR", 43616, 43647],
			["ABU", "TAI", 43648, 43743],
			["ABU", "MEETEI", 43744, 43775],
			["ABU", "ETHIOPIC", 43776, 43823],
			["SYM", "LATIN_EXT", 43824, 43887],
			["SYM", "MEETEI", 43968, 44031],
			["ABC", "HANGUL", 44032, 55203],
			["SYM", "HANGUL", 55216, 55295],
			["SYM", "SURROGATES", 55296, 56191],
			["SYM", "SURROGATES", 56192, 56319],
			["SYM", "SURROGATES", 56320, 57343],
			["SYM", "USE_AREA", 57344, 63743],
			["SYM", "IDEOGRAPHIC", 63744, 64255],
			["SYM", "ALPHABETIC_FORMS", 64256, 64335],
			["SYM", "ARABIC", 64336, 65023],
			["SYM", "SELECTORS", 65024, 65039],
			["SYM", "VERTICAL_FORMS", 65040, 65055],
			["SYM", "HALF_MARKS", 65056, 65071],
			["SYM", "CJK", 65072, 65103],
			["SYM", "VARIANTS", 65104, 65135],
			["SYM", "ARABIC", 65136, 65279],
			["SYM", "HALFWIDTH_FULLWIDTH", 65280, 65519],
			["SYM", "SPECIALS", 65520, 65535],
			["SYM", "LINEAR_B", 65536, 65663],
			["SYM", "LINEAR_B", 65664, 65791],
			["SYM", "NUMBERS", 65792, 65855],
			["SYM", "NUMBERS", 65856, 65935],
			["SYM", "ANCIENT", 65936, 65999],
			["SYM", "PHAISTOS", 66000, 66047],
			["SYM", "LYCIAN", 66176, 66207],
			["SYM", "CARIAN", 66208, 66271],
			["SYM", "NUMBERS", 66272, 66303],
			["SYM", "ITALIC", 66304, 66351],
			["SYM", "GOTHIC", 66352, 66383],
			["SYM", "PERMIC", 66384, 66431],
			["SYM", "UGARITIC", 66432, 66463],
			["SYM", "PERSIAN", 66464, 66527],
			["SYM", "DESERET", 66560, 66639],
			["SYM", "SHAVIAN", 66640, 66687],
			["SYM", "OSMANYA", 66688, 66735],
			["SYM", "ELBASAN", 66816, 66863],
			["SYM", "ALBANIAN", 66864, 66927],
			["SYM", "LINEAR_A", 67072, 67455],
			["SYM", "CYPRIOT", 67584, 67647],
			["SYM", "ARAMAIC", 67648, 67679],
			["SYM", "PALMYRENE", 67680, 67711],
			["SYM", "NABATAEAN", 67712, 67759],
			["SYM", "PHOENICIAN", 67840, 67871],
			["SYM", "LYDIAN", 67872, 67903],
			["SYM", "MEROITIC", 67968, 67999],
			["SYM", "MEROITIC", 68000, 68095],
			["SYM", "KHAROSHTHI", 68096, 68191],
			["SYM", "ARABIAN", 68192, 68223],
			["SYM", "ARABIAN", 68224, 68255],
			["SYM", "MANICHAEAN", 68288, 68351],
			["SYM", "AVESTAN", 68352, 68415],
			["SYM", "PARTHIAN", 68416, 68447],
			["SYM", "PAHLAVI", 68448, 68479],
			["SYM", "PAHLAVI", 68480, 68527],
			["SYM", "TURKIC", 68608, 68687],
			["SYM", "RUMI", 69216, 69247],
			["SYM", "BRAHMI", 69632, 69759],
			["SYM", "KAITHI", 69760, 69839],
			["SYM", "SORA", 69840, 69887],
			["SYM", "CHAKMA", 69888, 69967],
			["SYM", "MAHAJANI", 69968, 70015],
			["SYM", "SHARADA", 70016, 70111],
			["SYM", "NUMBERS", 70112, 70143],
			["SYM", "KHOJKI", 70144, 70223],
			["SYM", "KHUDAWADI", 70320, 70399],
			["SYM", "GRANTHA", 70400, 70527],
			["SYM", "TIRHUTA", 70784, 70879],
			["SYM", "SIDDHAM", 71040, 71167],
			["SYM", "MODI", 71168, 71263],
			["SYM", "TAKRI", 71296, 71375],
			["SYM", "WARANG", 71840, 71935],
			["SYM", "PAU_CIN_HAU", 72384, 72447],
			["SYM", "CUNEIFORM", 73728, 74751],
			["SYM", "NUMBERS", 74752, 74879],
			["SYM", "EGYPTIAN", 77824, 78895],
			["SYM", "BAMUM", 92160, 92735],
			["SYM", "MRO", 92736, 92783],
			["SYM", "BASSA_VAH", 92880, 92927],
			["SYM", "PAHAWH_HMONG", 92928, 93071],
			["SYM", "MIAO", 93952, 94111],
			["SYM", "KANA", 110592, 110847],
			["SYM", "DUPLOYAN", 113664, 113823],
			["SYM", "SHORTHAND", 113824, 113839],
			["SYM", "MUSICAL", 118784, 119039],
			["SYM", "MUSICAL", 119040, 119295],
			["SYM", "MUSICAL", 119296, 119375],
			["SYM", "TAI", 119552, 119647],
			["SYM", "MATHEMATICAL", 119648, 119679],
			["SYM", "MATHEMATICAL", 119808, 120831],
			["SYM", "MENDE_KIKAKUI", 124928, 125151],
			["SYM", "MATHEMATICAL", 126464, 126719],
			["SYM", "MAHJONG", 126976, 127023],
			["SYM", "DOMINO", 127024, 127135],
			["SYM", "CARDS", 127136, 127231],
			["SYM", "ALPHANUMERIC", 127232, 127487],
			["SYM", "IDEOGRAPHIC", 127488, 127743],
			["SYM", "MISCELLANEOUS", 127744, 128511],
			["SYM", "EMOTICONS", 128512, 128591],
			["SYM", "DINGBATS", 128592, 128639],
			["SYM", "TRANSPORT_MAP", 128640, 128767],
			["SYM", "ALCHEMICAL", 128768, 128895],
			["SYM", "GEOMETRIC_SHAPES", 128896, 129023],
			["SYM", "ARROWS", 129024, 129279]
		];
		$this->vUTF8Groups = $vUTF8Groups;
	}

	public function chr($nCode) {
		if($this->bDisable) {
			$sChr = \chr($nCode);
		} else {
			if($nCode <= 0x7F) {
				$sChr = \chr($nCode);
			} else if ($nCode <= 0x7FF) {
				$sChr = \chr(0xC0 | $nCode >> 6).\chr(0x80 | $nCode & 0x3F);
			} else if ($nCode <= 0xFFFF) {
				$sChr = \chr(0xE0 | $nCode >> 12).\chr(0x80 | $nCode >> 6 & 0x3F).\chr(0x80 | $nCode & 0x3F);
			} else if($nCode <= 0x10FFFF) {
				$sChr = \chr(0xF0 | $nCode >> 18).\chr(0x80 | $nCode >> 12 & 0x3F).\chr(0x80 | $nCode >> 6 & 0x3F).\chr(0x80 | $nCode & 0x3F);
			} else {
				$sChr = false;
			}
		}

		return $sChr;
	}

	public function escape($sText) {
		$sUnicode = "";
		$sChar = "";
		$bUTF8 = false;
		$nText = strlen($sText);
		for($x=0; $x<$nText; $x++) {
			if((\ord($sText[$x])&0xC0)!=0x80) {
				if(\strlen($sChar)) {
					$nChar = $this->ord($sChar);
					$nChar = self::call()->dec2hex($nChar);
					$sUnicode .= (!$bUTF8) ? $sChar : "\\u".\str_pad($nChar, 4, "0", STR_PAD_LEFT);
					$sChar = "";
					$bUTF8 = false;
				}
			} else {
				$bUTF8 = true;
			}

			$sChar .= $sText[$x];
		}

		$nChar = $this->ord($sChar);
		$nChar = self::call()->dec2hex($nChar);
		$sUnicode .= (!$bUTF8) ? $sChar : "\\u".\str_pad($nChar, 4, "0", STR_PAD_LEFT);

		return $sUnicode;
	}

	public function explode($sSplitter, $sString, $nLimit=null) {
		if($this->bDisable) {
			return ($nLimit===null) ? \explode($sSplitter, $sString) : \explode($sSplitter, $sString, $nLimit);
		} else {
			if($nLimit!==null && !$nLimit) { return array($sString); }
			$sChar = $sPart = "";
			$nString = \strlen($sString);
			for($x=0; $x<$nString; $x++) {
				if((\ord($sString[$x])&0xC0)!=0x80) {
					if($sChar!=$sSplitter) { $sPart .= $sChar; }
					if($sPart!="") {
						if($sChar==$sSplitter) {
							$aExplode[] = $sPart;
							$sChar = $sPart = "";
						} else {
							$sChar = "";
						}
					}
					$sChar .= $sString[$x];
				} else {
					$sChar .= $sString[$x];
				}

				if($sChar==$sSplitter && $sPart=="") {
					$aExplode[] = "";
					$sChar = "";
				}
			}

			if($sChar!=$sSplitter) { $sPart .= $sChar; }
			$aExplode[] = $sPart;
			if($sChar==$sSplitter) { $aExplode[] = ""; }

			if($nLimit!==null) {
				if($nLimit>0) {
					if($nLimit>=\count($aExplode)) { return $aExplode; }
					$aSlice = \array_slice($aExplode, 0, $nLimit-1);
					$aEnd = \array_slice($aExplode, $nLimit-1);
					$aSlice[] = \implode($sSplitter, $aEnd);
					return $aSlice;
				} else {
					return \array_slice($aExplode, 0, $nLimit);
				}
			}
		}

		return $aExplode;
	}

	public function groups() {
		$vGroups = [];
		foreach($this->vUTF8Groups as $vGroup) {
			$vGroups[] = \array_combine(["type","group","from","to"], $vGroup);
		}

		return $vGroups;
	}

	public function info($sChar) {
		$vReturn = null;
		$nOrd = self::call("unicode")->ord($sChar);
		foreach($this->vUTF8Groups as $vRange) {
			if($nOrd>=$vRange[2] && $nOrd<=$vRange[3]) {
				$sHex = \dechex($nOrd);
				$sHex = \str_pad($sHex, 4, "0", STR_PAD_LEFT);
				$sHex = \strtoupper($sHex);

				$vReturn = [];
				$vReturn["char"]		= $sChar;
				$vReturn["type"]		= $vRange[0];
				$vReturn["group"]		= $vRange[1];
				$vReturn["bytes"]		= \strlen($sChar);
				$vReturn["decimal"]		= $nOrd;
				$vReturn["hexadecimal"]	= $sHex;
				$vReturn["html"]		= "&#".$nOrd.";";
				$vReturn["escaped"]		= "\\u".$sHex;
				break;
			}
		}

		return $vReturn;
	}

	public function ischr($sChar) {
		$nOrd = self::call("unicode")->ord($sChar);
		foreach($this->vUTF8Groups as $vRange) {
			if($nOrd>=$vRange[2] && $nOrd<=$vRange[3]) {
				return [$vRange[0], $vRange[1], $nOrd];
			}
		}

		return false;
	}

	public function ord($sChar) {
		if($this->bDisable) {
			$nOrd = \ord($sChar);
		} else {
			$nOrd = false;
			if($sChar!==null && $sChar!=="" && isset($sChar[0])) {
				$nOrd = \ord($sChar[0]);
				if($nOrd <= 0x7F) {
					$nOrd = $nOrd;
				} else if($nOrd < 0xC2) {
					$nOrd = false;
				} else if($nOrd <= 0xDF) {
					$nOrd = ($nOrd & 0x1F) << 6 | (\ord($sChar[1]) & 0x3F);
				} else if($nOrd <= 0xEF) {
					$nOrd = ($nOrd & 0x0F) << 12 | (\ord($sChar[1]) & 0x3F) << 6 | (\ord($sChar[2]) & 0x3F);
				} else if($nOrd <= 0xF4) {
					$nOrd = ($nOrd & 0x0F) << 18 | (\ord($sChar[1]) & 0x3F) << 12 | (\ord($sChar[2]) & 0x3F) << 6 | (\ord($sChar[3]) & 0x3F);
				}
			}
		}

		return $nOrd;
	}

	public function split($mSource) {
		if($this->bDisable) {
			if(\is_string($mSource)) {
				$aSplit = \str_split($mSource);
			} else {
				$aSplit = $mSource;
			}
		} else {
			if(\is_string($mSource)) {
				$sText = $mSource;
				$sChar = "";
				$aSplit = [];
				$bUnicode = false;
				$nText = \strlen($sText);
				for($x=0; $x<$nText; $x++) {
					if((\ord($sText[$x])&0xC0)!=0x80) {
						if(\strlen($sChar)) {
							$aSplit[] = ($bUnicode) ? $sChar : \utf8_encode($sChar);
							$sChar = "";
							$bUnicode = false;
						}
					} else {
						$bUnicode = true;
					}

					$sChar .= $sText[$x];
				}

				$aSplit[] = ($bUnicode) ? $sChar : \utf8_encode($sChar);
			} else {
				$aSplit = $mSource;
			}
		}

		return $aSplit;
	}

	public function strlen($sString) {
		if($this->bDisable) {
			$nCount = \strlen($sString);
		} else {
			$nCount = 0;
			$sString = (string)$sString;
			$nString = \strlen($sString);
			for($x=0; $x<$nString; $x++) {
				if((\ord($sString[$x])&0xC0)!=0x80) {
					$nCount++;
				}
			}
		}
		return $nCount;
	}

	public function substr($mSource, $nStart=0, $nLength=null) {
		if($this->bDisable) {
			$sSource = (\is_string($mSource)) ? $mSource : \implode($mSource);
			return (!$nLength) ? \substr($sSource, $nStart) : \substr($sSource, $nStart, $nLength);
		} else {
			$aSource = (\is_string($mSource)) ? $this->split($mSource) : $mSource;

			if(!$nLength) { $nLength = \count($aSource); }
			if($nLength) {
				$aSubString = \array_slice($aSource, $nStart, $nLength);
				$sSubString = \implode($aSubString);
				return $sSubString;
			}
		}

		return false;
	}

	public function strpad($sText, $nLength, $sString=" ", $nType=STR_PAD_RIGHT) {
		$nDiff = $this->strlen($sText) - \strlen($sText);
		return str_pad($sText, ($nLength-$nDiff), $sString, $nType);
	}

	public function str_split($sText, $nLength=1) {
		$aText = preg_split("//u", $sText, -1, PREG_SPLIT_NO_EMPTY);
		if($nLength > 1) {
			$aChunks = array_chunk($aText, $nLength);
			foreach($aChunks as $x => $sChunk) {
				$aChunks[$x] = \implode("", (array)$sChunk);
			}
			$aText = $aChunks;
		}

		return $aText;
	}

	public function unescape($sText) {
		return \preg_replace_callback("/\\\u([0-9A-F]{4})/i", [$this, "unescapeChar"], $sText);
	}

	private function unescapeChar($aChars) {
		$nChar = $aChars[1];
		$nChar = self::call()->hex2dec($nChar);
		return $this->chr($nChar);
	}
}

?>