<?php
/*
# nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom/nogal
___

# image
https://hytcom.net/nogal/docs/objects/image.md
*/
namespace nogal;
class nglImage extends nglBranch implements inglBranch {

	private $image;
	private $fOutput;
	private $sType;

	final protected function __declareArguments__() {
		$vArguments						= [];
		$vArguments["alpha"]			= ['self::call()->istrue($mValue)', false];
		$vArguments["canvas_color"]		= ['(string)$mValue', "#FFFFFF"];
		$vArguments["canvas_height"]	= ['(int)$mValue', 0];
		$vArguments["canvas_width"]		= ['(int)$mValue', 0];
		$vArguments["filepath"]			= ['$mValue', null];
		$vArguments["filter_name"]		= ['$mValue', null];
		$vArguments["filter_args"]		= ['$mValue', null];
		$vArguments["height"]			= ['(int)$mValue', 0];
		$vArguments["merge_image"]		= ['$mValue'];
		$vArguments["merge_alpha"]		= ['self::call()->istrue($mValue)', true];
		$vArguments["merge_position"]	= ['\strtolower($mValue)', "center center"];
		$vArguments["position"]			= ['\strtolower($mValue)', "center center"];
		$vArguments["quality"]			= ['(int)$mValue', 100];
		$vArguments["rc_find"]			= ['(string)$mValue', "#000000"];
		$vArguments["rc_replace"]		= ['(string)$mValue', "#FFFFFF"];
		$vArguments["rc_tolerance"]		= ['self::call()->istrue($mValue)', 0];
		$vArguments["text_content"]		= ['(string)$mValue'];
		$vArguments["text_angle"]		= ['(int)$mValue', 0];
		$vArguments["text_color"]		= ['(string)$mValue', "#000000"];
		$vArguments["text_font"]		= ['(string)$mValue', NGL_FONT];
		$vArguments["text_margin"]		= ['(string)$mValue', 0];
		$vArguments["text_position"]	= ['(string)$mValue', "center center"];
		$vArguments["text_size"]		= ['(int)$mValue', 10];
		$vArguments["type"]				= ['(string)$mValue', "jpeg"];
		$vArguments["width"]			= ['(int)$mValue', 0];

		return $vArguments;
	}

	final protected function __declareAttributes__() {
		$vAttributes					= [];
		$vAttributes["data"]	 		= null;
		$vAttributes["imageheight"]	 	= null;
		$vAttributes["image"]			= null;
		$vAttributes["info"]			= null;
		$vAttributes["mime"]	 		= null;
		$vAttributes["path"]	 		= null;
		$vAttributes["size"]			= null;
		$vAttributes["imagetype"]		= null;
		$vAttributes["imagewidth"]	 	= null;

		return $vAttributes;
	}

	final protected function __declareVariables__() {
	}

	final public function __init__() {
	}

	public function base64() {
		list($bAlpha) = $this->getarguments("alpha", \func_get_args());

		if($bAlpha) {
			\imagealphablending($this->image, true);
			\imagesavealpha($this->image, true);
		} else {
			\imagealphablending($this->image, false);
		}

		$fOutput = $this->fOutput;
		\ob_start();
		$fOutput ($this->image);
		$sSource = \ob_get_contents();
		\ob_end_clean();

		return "data:image/".\strtolower($this->sType).";base64,".\base64_encode($sSource);
	}

	private function CalculatePosition($sPosition, $nWidth, $nHeight, $nCanvasWidth, $nCanvasHeight) {
		$nTop = $nLeft = 0;
		if($nCanvasWidth!=$nWidth || $nCanvasHeight!=$nHeight) {
			if(\strstr($sPosition, ";")) {
				\sscanf($sPosition, "%d;%d", $nTop, $nLeft);
			} else if(\strstr($sPosition, ",")) {
				\sscanf($sPosition, "%d;%d", $nTop, $nLeft);
			} else {
				\sscanf($sPosition, "%s %s", $sTop, $sLeft);
				$sTop = \strtolower($sTop);
				$sLeft = \strtolower($sLeft);

				// top
				if($sTop=="center") {
					$nTop = ($nCanvasHeight-$nHeight)/2;
				} else if($sTop=="bottom") {
					$nTop = ($nCanvasHeight-$nHeight);
				}

				// left
				if($sLeft=="center") {
					$nLeft = ($nCanvasWidth-$nWidth)/2;
				} else if($sLeft=="right") {
					$nLeft = ($nCanvasWidth-$nWidth);
				}
			}
		}

		return array($nTop, $nLeft);
	}

	private function CalculateSizes($nArgWidth, $nArgHeight, $nArgCanvasWidth, $nArgCanvasHeight) {
		$nImgWidth	= $this->attribute("imagewidth");
		$nImgHeight	= $this->attribute("imageheight");

		// dimensiones de la imagen
		if($nArgWidth && !$nArgHeight) {
			$nWidth  	= $nArgWidth;
			$nHeight 	= $nImgHeight * $nWidth / $nImgWidth;
		} else if(!$nArgWidth && $nArgHeight) {
			$nHeight	= $nArgHeight;
			$nWidth 	= $nImgWidth * $nHeight / $nImgHeight;
		} else if($nArgWidth && $nArgHeight) {
			$nWidth		= $nArgWidth;
			$nHeight	= $nArgHeight;
		} else {
			$nWidth		= $nImgWidth;
			$nHeight	= $nImgHeight;
		}

		// dimensiones del lienzo
		$nCanvasWidth	= ($nArgCanvasWidth) ? $nArgCanvasWidth : $nWidth;
		$nCanvasHeight	= ($nArgCanvasHeight) ? $nArgCanvasHeight : $nHeight;

		$vSizes = array(
			\ceil($nWidth),
			\ceil($nHeight),
			\ceil($nCanvasWidth),
			\ceil($nCanvasHeight)
		);

		return $vSizes;
	}

	public function canvas() {
		list($nNewCanvasWidth,$nNewCanvasHeight,$sCanvasColor,$sPosition,$bAlpha) = $this->getarguments(
			"canvas_width,canvas_height,canvas_color,position,alpha", \func_get_args()
		);

		$nWidth	= $this->attribute("imagewidth");
		$nHeight = $this->attribute("imageheight");
		$this->CreateCopy($nWidth, $nHeight, $nNewCanvasWidth, $nNewCanvasHeight, $bAlpha, $sPosition, $sCanvasColor);

		return $this;
	}

	private function CreateCopy($nWidth, $nHeight, $nCanvasWidth, $nCanvasHeight, $bAlpha, $sPosition="center center", $sCanvasColor="#FFFFFF") {
		$nImageWidth	= $this->attribute("imagewidth");
		$nImageHeight	= $this->attribute("imageheight");

		// dimensiones
		list($nNewWidth, $nNewHeight, $nNewCanvasWidth, $nNewCanvasHeight) =
			$this->CalculateSizes($nWidth, $nHeight, $nCanvasWidth, $nCanvasHeight);

		// nueva imagen
		$hNewImage = \imagecreatetruecolor($nNewCanvasWidth, $nNewCanvasHeight);

		// posicion de la imagen en el lienzo
		$aPositions = $this->CalculatePosition($sPosition, $nNewWidth, $nNewHeight, $nNewCanvasWidth, $nNewCanvasHeight);
		$nTop = $aPositions[0];
		$nLeft = $aPositions[1];

		// color
		$vRGB = $this->GetTransparency($this->image);
		if($vRGB===false) {
			$sCanvasColor = \str_replace("#", "", $sCanvasColor);
			$vRGB = self::call()->colorRGB($sCanvasColor);
			$nColor = \imagecolorallocate($hNewImage, $vRGB["red"], $vRGB["green"], $vRGB["blue"]);
			\imagefill($hNewImage, 0, 0, $nColor);
		} else {
			$nColor = \imagecolorallocate($hNewImage, $vRGB["red"], $vRGB["green"], $vRGB["blue"]);
			\imagefill($hNewImage, 0, 0, $nColor);
			\imagecolortransparent($hNewImage, $nColor);
		}

		// alpha
		if($bAlpha) {
			\imagealphablending($hNewImage, true);
			\imagesavealpha($hNewImage, true);
		} else {
			\imagealphablending($hNewImage, false);
		}

		\imagecopyresampled($hNewImage, $this->image, $nLeft, $nTop, 0, 0, $nNewWidth, $nNewHeight, $nImageWidth, $nImageHeight);
		$nImgWidth = \imageSX($hNewImage);
		$nImgHeight = \imageSY($hNewImage);

		$this->image = $hNewImage;

		// imagepalettecopy($hNewImage, $this->image);
		$this->attribute("imagewidth", $nImgWidth);
		$this->attribute("imageheight", $nImgHeight);

		return true;
	}

	public function data() {
		$sMark = "APP13";
		$aInfo = $this->attribute("info");
		if(\is_array($aInfo) && \array_key_exists($sMark, $aInfo)) {
			if($aData = \iptcparse($aInfo[$sMark])) {
				$vIPTCCodes = [];
				$vIPTCCodes["2#000"] = "record_version";
				$vIPTCCodes["2#003"] = "object_type";
				$vIPTCCodes["2#004"] = "object_attribute";
				$vIPTCCodes["2#005"] = "object_name";
				$vIPTCCodes["2#007"] = "edit_status";
				$vIPTCCodes["2#008"] = "editorial_update";
				$vIPTCCodes["2#010"] = "urgency";
				$vIPTCCodes["2#012"] = "subject";
				$vIPTCCodes["2#015"] = "category";
				$vIPTCCodes["2#020"] = "supp_category";
				$vIPTCCodes["2#022"] = "fixture_id";
				$vIPTCCodes["2#025"] = "keywords";
				$vIPTCCodes["2#026"] = "location_code";
				$vIPTCCodes["2#027"] = "location_name";
				$vIPTCCodes["2#030"] = "release_date";
				$vIPTCCodes["2#035"] = "release_time";
				$vIPTCCodes["2#037"] = "expiration_date";
				$vIPTCCodes["2#038"] = "expiration_time";
				$vIPTCCodes["2#040"] = "special_instructions";
				$vIPTCCodes["2#042"] = "action_advised";
				$vIPTCCodes["2#045"] = "reference_service";
				$vIPTCCodes["2#047"] = "reference_date";
				$vIPTCCodes["2#050"] = "reference_number";
				$vIPTCCodes["2#055"] = "date_created";
				$vIPTCCodes["2#060"] = "time_created";
				$vIPTCCodes["2#062"] = "digitization_date";
				$vIPTCCodes["2#063"] = "digitization_time";
				$vIPTCCodes["2#065"] = "program";
				$vIPTCCodes["2#070"] = "program_version";
				$vIPTCCodes["2#075"] = "object_cycle";
				$vIPTCCodes["2#080"] = "byline";
				$vIPTCCodes["2#085"] = "byline_title";
				$vIPTCCodes["2#090"] = "city";
				$vIPTCCodes["2#092"] = "sub_location";
				$vIPTCCodes["2#095"] = "province_state";
				$vIPTCCodes["2#100"] = "country_code";
				$vIPTCCodes["2#101"] = "country_name";
				$vIPTCCodes["2#103"] = "transmission_reference";
				$vIPTCCodes["2#105"] = "headline";
				$vIPTCCodes["2#110"] = "credit";
				$vIPTCCodes["2#115"] = "source";
				$vIPTCCodes["2#116"] = "copyright";
				$vIPTCCodes["2#118"] = "contact";
				$vIPTCCodes["2#120"] = "caption";
				$vIPTCCodes["2#122"] = "writer";
				$vIPTCCodes["2#125"] = "rasterized_caption";
				$vIPTCCodes["2#130"] = "image_type";
				$vIPTCCodes["2#131"] = "image_orientation";
				$vIPTCCodes["2#135"] = "language";
				$vIPTCCodes["2#150"] = "audio_type";
				$vIPTCCodes["2#151"] = "audio_rate";
				$vIPTCCodes["2#152"] = "audio_resolution";
				$vIPTCCodes["2#153"] = "audio_duration";
				$vIPTCCodes["2#154"] = "audio_outcue";
				$vIPTCCodes["2#200"] = "preview_format";
				$vIPTCCodes["2#201"] = "preview_version";
				$vIPTCCodes["2#202"] = "preview";

				foreach($aData as $mKey => $aValue) {
					if(@\count($aValue)>1) {
						foreach($aValue as $sValue) {
							if(isset($vIPTCCodes[$mKey])) {
								$aResult[$vIPTCCodes[$mKey]][] = \trim($sValue);
							} else {
								$aResult[$mKey][] = \trim($sValue);
							}
						}
					} else {
						if(isset($vIPTCCodes[$mKey])) {
							$aResult[$vIPTCCodes[$mKey]] = \trim($aValue[0]);
						} else {
							$aResult[$mKey] = \trim($aValue[0]);
						}
					}
				}
				$aIPTCData = $aResult;
			} else {
				$aIPTCData = 0;
			}
		} else {
			$aIPTCData = 0;
		}

		$vImageData = [];
		$vImageData["EXIF"] = (\function_exists("exif_read_data")) ? \exif_read_data($this->attribute("path")) : "Undefined EXIF Functions";
		$vImageData["IPTC"] = $aIPTCData;

		$this->attribute("data", $vImageData);
		return $vImageData;
	}

	public function filter() {
		list($sFilter,$mValue) = $this->getarguments("filter_name,filter_args", \func_get_args());

		switch(\strtolower($sFilter)) {
			case "negative":
				\imagefilter($this->image, IMG_FILTER_NEGATE);
				break;

			case "grayscale":
				\imagefilter($this->image, IMG_FILTER_GRAYSCALE);
				break;

			case "sharpe":
				\imagefilter($this->image, IMG_FILTER_EDGEDETECT);
				break;

			case "emboss":
				\imagefilter($this->image, IMG_FILTER_EMBOSS);
				break;

			case "gaussian_blur":
				\imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
				break;

			case "blur":
				\imagefilter($this->image, IMG_FILTER_SELECTIVE_BLUR);
				break;

			case "sketch":
				\imagefilter($this->image, IMG_FILTER_MEAN_REMOVAL);
				break;

			case "brightness":
				\imagefilter($this->image, IMG_FILTER_BRIGHTNESS, (int)$mValue);
				break;

			case "contrast":
				\imagefilter($this->image, IMG_FILTER_CONTRAST, (int)$mValue);
				break;

			case "smooth":
				\imagefilter($this->image, IMG_FILTER_SMOOTH, (int)$mValue);
				break;

			case "pixelate":
				\imagefilter($this->image, IMG_FILTER_PIXELATE, (int)$mValue, true);
				break;

			case "colorize":
				$vRGB = self::call()->colorRGB($mValue);
				\imagefilter($this->image, IMG_FILTER_COLORIZE, $vRGB["red"], $vRGB["green"], $vRGB["blue"], $vRGB["alpha"]);
				break;
		}

		return $this;
	}

	private function GetTransparency($hSourceImage) {
		$nIndex = \imagecolortransparent($hSourceImage);
		if($nIndex >= 0) {
			$aColors = \imagecolorsforindex($hSourceImage, $nIndex);
			return $aColors;
		} else {
			return false;
		}
	}

	public function image() {
		list($bAlpha) = $this->getarguments("alpha", \func_get_args());

		if($bAlpha) {
			\imagealphablending($this->image, true);
			\imagesavealpha($this->image, true);
		} else {
			\imagealphablending($this->image, false);
		}

		return $this->image;
	}

	public function load() {
		list($mFile,$sType) = $this->getarguments("filepath,type", \func_get_args());

		$sType = \strtolower($sType);
		if($sType=="jpg") { $sType = "jpeg"; }

		$this->sType = $sType;
		if(empty($mFile)) {
			$this->fOutput = "Image".$sType;
			$image = \ImageCreate(1,1);
		} else if(\is_resource($mFile)) {
			$this->fOutput = "Image".$sType;
			$image = $mFile;
		} else {
			$sFileName = self::call()->clearPath($mFile);
			if(self::call()->isURL($sFileName)) {
				$file = self::call("file")->load($sFileName);
				$dst = self::call("file")->load($file->fileinfo()["basename"])->write($file->read());
				$sFileName = $dst->path;
			}

			if(isset($sFileName) && !empty($sFileName)) {
				$sFileName = self::call()->sandboxPath($sFileName);
				if(\file_exists($sFileName)) {
					if($vInfo = \getimagesize($sFileName, $aImageInfo)) {
						$aType = \explode("/", $vInfo["mime"]);
						$sType = $aType[1];

						$this->fOutput = "Image".$sType;
						$fCreate = "ImageCreateFrom".$aType[1];
						$image = $fCreate ($sFileName);

						$this->attribute("path", $sFileName);
						$this->attribute("info", $aImageInfo);
					} else {
						self::errorMessage($this->object, 1001);
						return false;
					}
				}
			}
		}

		if(isset($image)) {
			$this->attribute("mime", "image/".$sType);
			$this->attribute("imagetype", $sType);
			$this->attribute("imagewidth", \imageSX($image));
			$this->attribute("imageheight", \imageSY($image));
			$this->image = $image;
			return $this;
		}

		return false;
	}

	public function margin() {
		list($nMargin,$sCanvasColor) = $this->getarguments("margin,canvas_color", \func_get_args());

		$nWidth	= $this->attribute("imagewidth");
		$nHeight = $this->attribute("imageheight");
		$sPosition = "center center";
		$this->CreateCopy($nWidth, $nHeight, ($nWidth+$nMargin*2), ($nHeight+$nMargin*2), $this->alpha, $sPosition, $sCanvasColor);

		return $this;
	}

	public function padding() {
		list($nPadding,$sCanvasColor) = $this->getarguments("padding,canvas_color", \func_get_args());

		$nWidth	= $this->attribute("imagewidth");
		$nHeight = $this->attribute("imageheight");
		$sPosition = "center center";
		$this->CreateCopy(($nWidth-$nPadding*2), ($nHeight-$nPadding*2), $nWidth, $nHeight, $this->alpha, $sPosition, $sCanvasColor);

		return $this;
	}

	public function merge() {
		list($image,$sPosition,$bAlpha) = $this->getarguments("merge_image,merge_position,merge_alpha", \func_get_args());

		$nWidth 		= \imageSX($image);
		$nHeight 		= \imageSY($image);
		$nCanvasWidth	= $this->attribute("imagewidth");
		$nCanvasHeight	= $this->attribute("imageheight");
		$sPosition 		= \strtolower($sPosition);

		// posicion de la imagen en el lienzo
		$aPositions = $this->CalculatePosition($sPosition, $nWidth, $nHeight, $nCanvasWidth, $nCanvasHeight);

		$nTop = $aPositions[0];
		$nLeft = $aPositions[1];

		// alpha modo
		if($bAlpha) {
			\imagesavealpha($this->image, true);
			\imagealphablending($this->image, true);
		} else {
			\imagealphablending($this->image, false);
		}

		\imageCopyResampled($this->image, $image, $nLeft, $nTop, 0, 0, $nWidth, $nHeight, $nWidth, $nHeight);

		return $this;
	}

	public function resize() {
		list($nNewWidth,$mNewHeight,$bAlpha) = $this->getarguments("width,height,alpha", \func_get_args());

		$bVertical = ($this->attribute("imagewidth")>$this->attribute("imageheight"));

		switch(true) {
			case (\strtolower($mNewHeight)==="max"):
				$nTemp = $nNewWidth;
				$nNewWidth	= (!$bVertical) ? 0 : $nTemp;
				$nNewHeight	= (!$bVertical) ? $nTemp : 0;
				break;

			case \strtolower($mNewHeight)==="min":
				$nTemp = $nNewWidth;
				$nNewWidth	= ($bVertical) ? 0 : $nTemp;
				$nNewHeight	= ($bVertical) ? $nTemp : 0;
				break;

			default:
				$nNewHeight = $mNewHeight;
		}

		$this->CreateCopy($nNewWidth, $nNewHeight, $nNewWidth, $nNewHeight, $bAlpha);
		return $this;
	}

	public function replace() {
		list($sFind,$sReplace,$nTolerance) = $this->getarguments("rc_find,rc_replace,rc_tolerance", \func_get_args());

		$vFindMin = $vFindMax = self::call()->colorRGB($sFind);
		$vFindMin["red"]	-= $nTolerance;
		$vFindMin["green"]	-= $nTolerance;
		$vFindMin["blue"]	-= $nTolerance;

		$vFindMax["red"]	+= $nTolerance;
		$vFindMax["green"]	+= $nTolerance;
		$vFindMax["blue"]	+= $nTolerance;

		$vReplace = self::call()->colorRGB($sReplace);

		$nWidth	= $this->attribute("imagewidth");
		$nHeight = $this->attribute("imageheight");

		for($x=0;$x<$nWidth;$x++) {
			for($y=0;$y<$nHeight;$y++) {
				$nColor = \imagecolorat($this->image, $x, $y);
				$nRed = ($nColor >> 16) & 0xFF;
				$nGreen = ($nColor >> 8) & 0xFF;
				$nBlue = $nColor & 0xFF;

				if(
					($vFindMin["red"]<=$nRed && $nRed<=$vFindMax["red"]) &&
					($vFindMin["green"]<=$nGreen && $nGreen<=$vFindMax["green"]) &&
					($vFindMin["blue"]<=$nBlue && $nBlue<=$vFindMax["blue"])
				) {
					$nNewColor = \imagecolorallocate($this->image, $vReplace["red"], $vReplace["green"], $vReplace["blue"]);
					\imagesetpixel($this->image, $x, $y, $nNewColor);
				}
			}
		}

		return $this;
	}

	public function text() {
		list($sText,$sColor,$sPosition,$sMargin,$nFont,$nAngle,$sFont) = $this->getarguments("text_content,text_color,text_position,text_margin,text_size,text_angle,text_font", \func_get_args());
		if(!\file_exists($sFont)) {
			self::errorMessage($this->object, 1002);
			return false;
		}

		$vColor = self::call()->colorRGB($sColor);

		\imagealphablending($this->image, true);

		$nColor = \imagecolorallocate($this->image, $vColor["red"], $vColor["green"], $vColor["blue"]);

		$aMargin = \explode(" ", $sMargin);
		if(!isset($aMargin[1])) { $aMargin[1] = $aMargin[0]; }

		// caja circundante
		$vBox = \imageftbbox($nFont, $nAngle, $sFont, $sText);
		$nWidth = \imagesx($this->image);
		$nHeight = \imagesy($this->image);
		$nTextWidth = \abs($vBox[0]) + \abs($vBox[2]);
		$nTextHeight = \abs($vBox[1]) + \abs($vBox[5]);

		$aPositions = $this->CalculatePosition($sPosition, $nTextWidth, $nTextHeight, $nWidth, $nHeight);

		$nLeft	= $aPositions[1]+$aMargin[1];
		$nTop	= ($aPositions[0]+$nFont)+$aMargin[0];

		\imagefttext($this->image, $nFont, $nAngle, $nLeft, $nTop, $nColor, $sFont, $sText);
		return $this;
	}

	public function view() {
		list($bAlpha) = $this->getarguments("alpha", \func_get_args());

		if($bAlpha) {
			\imagealphablending($this->image, true);
			\imagesavealpha($this->image, true);
		} else {
			\imagealphablending($this->image, false);
		}

		$fOutput = $this->fOutput;
		\header("Content-type: ".$this->attribute("mime"));
		$fOutput ($this->image);
		exit();
	}

	public function write() {
		list($sFilePath,$nQuality) = $this->getarguments("filepath,quality", \func_get_args());

		if(!empty($sFilePath) && !self::call()->isURL($sFilePath)) {
			$sFilePath = self::call()->clearPath($sFilePath);
			$sFilePath = self::call()->sandboxPath($sFilePath);
			$vPath = \pathinfo($sFilePath);
			$sExtension = \strtolower($vPath["extension"]);
			$fOutput = $this->fOutput;

			$bAction = false;
			switch($sExtension) {
				case "jpeg":
				case "jpg":
					$bAction = $fOutput ($this->image, $sFilePath, $nQuality);
					break;

				case "gif":
					$bAction = $fOutput ($this->image, $sFilePath);
					break;

				case "png":
					$nQuality = 10 - \ceil($nQuality/100);
					$bAction = $fOutput ($this->image, $sFilePath, $nQuality);
					break;
			}
		}

		return $this;
	}
}

?>