<?php \defined("NGL_SOWED") || exit();

// data ------------------------------------------------------------------------
$data = [
	"u01" => ["lastname"=>"gomez", "name"=>"juan", "age"=>35],
	"u02" => ["lastname"=>"smith", "name"=>"lucy", "age"=>23],
	"u03" => ["lastname"=>"smith", "name"=>"adam", "age"=>44]
];


// abstract object
abstract class auth {
	static function token() {
		global $ngl;
		return \json_decode($ngl("jwt")->expire("+30 minutes")->create([
			"username"=>"ariel",
			"rol"=>"admin"
		]), true);
	}

	static function verify($r) {
		global $ngl;
		return ($ngl("jwt")->verify($r["auth"],"*V3ryStR0n6k3Y@!")) ? 200 : 401;
	}
}


// object ----------------------------------------------------------------------
class info {
	function create($rq, $rs) {
		$rs->code = 201;
		$rs->body = [
			["lastname"=>"gomez", "name"=>"juan", "age"=>35]
		];
		return $rs;
	}

	function get($rq, $rs) {
		global $data;
		$rs->code = 200;
		$rs->body = $data;
		return $rs;
	}

	function getid($rq, $rs) {
		global $data;
		$rs->code = 200;
		$rs->body = $data[$rq["pathvars"]["id"]];
		return $rs;
	}
}
$info = new info();

// response
$ants = $ngl("ants")
		->format("json")
		->authtype("bearer")
		->auth([auth::class, "verify"])
		->routes_secure(false)

		->routes_base("api")
		->route(["POST", "token", [auth::class, "token"]])

		->routes_base("api/object")
		->routes([
			["GET", "get", [$info,"get"]],
			["GET", "get/{id}", [$info,"getid"]],
			[["PATH","PUT"], "update", [$info,"create"]],
		])

		->routes_base("api/lambda")
		->routes([
			["GET", "get", function($rq, $rs) use ($data) {
				$rs->headers["x-responser"] = "nogal";
				$rs->code = 200;
				$rs->body = $data;
				return $rs;
			}],
			["DELETE", "delete", function($rq, $rs){
				$rs->code = 418;
				$rs->body = ["DELETE Lambda Example!"];
				return $rs;
			}]
		])

		->routes_base("api/nut")
		->routes([
			["GET", "color", ["nut.pecan", "color"]],
			["GET", "json", ["nut.pecan", "apigetjson"]]
		])
;
exit($ants->response()->body);

?>