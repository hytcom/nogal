<?php
/*
# Nogal
*the most simple PHP Framework* by hytcom.net
GitHub @hytcom
___
  
# crypt
## nglCrypt *extends* nglBranch [instanciable] [2018-08-12]
Implementa la clase 'barcode-generator', para generar Códigos de Barras

https://github.com/hytcom/wiki/blob/master/nogal/docs/barcode.md

*/
namespace nogal {

	class nglGraftAWSS3 extends nglScion {
		
		public $s3;
		private $sBucket;
		private $response;

		final protected function __declareArguments__() {
			$vArguments					= [];
			$vArguments["bucket"]		= ['(string)$mValue', null];
			$vArguments["apikey"]		= ['(string)$mValue', null];
			$vArguments["apipkey"]		= ['(string)$mValue', null];
			$vArguments["filepath"]		= ['(string)$mValue', null];
			$vArguments["region"]		= ['(string)$mValue', "us-east-1"];
			$vArguments["source"]		= ['(string)$mValue', null];
			$vArguments["version"]		= ['(string)$mValue', "latest"];
		
			return $vArguments;
		}

		final protected function __declareAttributes__() {
			$vAttributes = [];
			return $vAttributes;
		}

		final protected function __declareVariables__() {
		}

		final public function __init__() {
			if(!\class_exists("\Aws\S3\S3Client")) {
				$this->__errorMode__("die");
				self::errorMessage($this->object, 1000);
			}
		}

		public function connect() {
			list($sKey,$sPkey) = $this->getarguments("apikey,apipkey", \func_get_args());
			$this->s3 = new \Aws\S3\S3Client([
				"version"		=> $this->argument("version"),
				"region"		=> $this->argument("region"),
				"credentials"	=> ["key"=>$sKey, "secret"=>$this->argument("apipkey")]
			]);

			return $this;
		}

		public function buckets() {
			$this->IsConnected();
			
			try {
				$this->response = $this->s3->listBuckets();
			} catch (\Aws\s3\Exception\S3Exception $e) {
				$this->S3Error($e);
			}

			return $this->response["Buckets"];
		}

		public function ls() {
			list($sFilePath,$sBucket) = $this->getarguments("filepath,bucket", \func_get_args());
			if(\substr($sFilePath,-1)!="/") { $sFilePath .= "/"; }
			try {
				$this->response = $this->s3->listObjects(["Bucket"=>$sBucket, "Marker"=>$sFilePath, "Delimitesr"=>"zip"]);
			} catch (\Aws\S3\Exception\S3Exception $e) {
				$this->S3Error($e);
			}

			$aLS = [];
			if(\count($this->response["Contents"])) {
				foreach($this->response["Contents"] as $aItem) {
					$aFile = [];
					$bIsDir = (\substr($aItem["Key"], -1)=="/");
					$aFile["type"] 		= $bIsDir ? "dir" : "file";
					$aFile["path"] 		= $aItem["Key"];
					$aFile["basename"]	= \basename($aItem["Key"]);
					
					$aBasename = $bIsDir ? [] : \explode(".", $aFile["basename"]);
					if(\count($aBasename)>1) {
						$aFile["extension"] = \array_pop($aBasename);
						$aFile["filename"] = \implode(".", $aBasename);
					} else {
						$aFile["extension"] = "";
						$aFile["filename"] = $aFile["basename"];
					}

					$aFile["timestamp"] = $aItem["LastModified"]->getTimestamp();
					$aFile["date"]		= \date("Y-m-d H:i:s", $aFile["timestamp"]);
					$aFile["bytes"]		= $aItem["Size"];
					$aFile["size"]		= self::call()->strSizeEncode($aItem["Size"]);
					$aFile["etag"]		= \trim($aItem["ETag"],'"');

					$aLS[] = $aFile;
				}
			}

			return $aLS;
		}

		public function mkdir() {
			list($sFilePath,$sBucket) = $this->getarguments("filepath,bucket", \func_get_args());
			if(\substr($sFilePath,-1)!="/") { $sFilePath .= "/"; }
			try {
				$this->response = $this->s3->putObject([
					"Bucket"=>$sBucket,
					"Key"=>$sFilePath
				]);
			} catch (\Aws\S3\Exception\S3Exception $e) {
				$this->S3Error($e);
			}

			return $this;
		}

		public function upload() {
			list($sSource,$sFilePath,$sBucket) = $this->getarguments("source,filepath,bucket", \func_get_args());
			try {
				$this->response = $this->s3->putObject([
					"Bucket" => $sBucket,
					"Key" => $sFilePath,
					"SourceFile" => $sSource
				]);
			} catch (\Aws\S3\Exception\S3Exception $e) {
				$this->S3Error($e);
			}

			return $this;
		}

		public function lastResponse() {
			return $this->response;
		}

		private function IsConnected() {
			if(!$this->s3) {
				self::errorMode("die");
				self::errorMessage($this->object, "ConnectionError");
			}
		}

		private function S3Error($e) {
			self::errorMode("die");
			self::errorMessage($this->object, $e->getAwsErrorCode(), $e->getAwsErrorMessage());
		}
	}
}

?>