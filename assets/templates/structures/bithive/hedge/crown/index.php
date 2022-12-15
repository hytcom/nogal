<?php defined("NGL_SOWED") || exit(); ?>
<!DOCTYPE html>
<html>
	<head>
		<title>nogal <?php echo $ngl("sysvar")->VERSION["version"]; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css" />
		<style>
			body { font-family:sans-serif; text-align:center; }
			th,td { text-align: left; font-size: 11px; padding: 2px 6px; }
			.nogal { font-size: 10px; font-weight: normal; line-height: 1em; }
			.nogal b { font-size: 12px; font-weight: bold !important}
			small, small * { font-size: 10px; font-weight: normal; }
			address { color: #AB0000; font-size: 18px; font-weight: bold; padding: 5px; text-align: center; }
			.success { background-color: #00AB00; color: #FFFFFF; font-size: 18px; font-weight: bold; margin: 10px; padding: 10px; text-align: center; }
			.error { background-color: #AB0000; color: #FFFFFF; margin: 4px; padding: 4px; text-align: center; }
			h3 { padding: 10px !important; text-align:left; }
			li a { font-size: 14px; line-height:1.5rem; }
		</style>
	</head>
	<body>
		<div class="container">
			<br />
			<svg
				xmlns="http://www.w3.org/2000/svg"
				xml:space="preserve"
				height="100px"
				version="1.1"
				style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd"
				viewBox="0 0 287 230"
				xmlns:xlink="http://www.w3.org/1999/xlink"
			>
				<defs>
					<style type="text/css">
						<![CDATA[
							.cup {fill:#000000;fill-rule:nonzero}
							.trunk {fill:#AB0000;fill-rule:nonzero}
						]]>
					</style>
				</defs>
				<g id="nogal">
					<path class="cup" d="M40 80c-26,0 -39,32 -21,50 6,5 13,8 21,8 3,0 5,3 5,6 0,22 25,36 44,25 5,-3 7,4 20,4 13,0 15,-7 20,-4 9,5 20,5 29,0 5,-3 7,4 20,4 13,0 15,-7 20,-4 19,11 44,-3 44,-25 0,-3 2,-6 5,-6 26,0 39,-31 21,-50 -5,-5 -13,-8 -21,-8 -3,0 -5,-2 -5,-5 0,-17 -13,-30 -29,-30 -3,0 -6,-2 -6,-5 0,-8 -3,-15 -8,-21 -9,-9 -24,-11 -35,-4 -5,3 -7,-4 -20,-4 -13,0 -15,7 -20,4 -19,-11 -44,2 -44,25 0,3 -2,5 -5,5l0 0c-17,0 -30,13 -30,30 0,3 -2,5 -5,5zm-28 1c6,-6 14,-10 23,-12 2,-18 16,-32 34,-34 4,-27 33,-43 57,-31 11,-5 24,-5 35,0 24,-12 53,4 57,31 18,2 32,16 34,34 33,5 47,45 24,68 -6,6 -15,11 -24,12 -3,27 -32,42 -56,31 -11,5 -24,5 -35,0 -11,5 -24,5 -35,0 -11,5 -23,5 -34,0 -25,11 -54,-4 -57,-31 -33,-4 -47,-45 -23,-68z"/>
					<path class="trunk" d="M129 123l0 -39c0,-7 -3,-14 -8,-19 -5,-5 -12,-8 -19,-8 -5,0 -8,-3 -8,-7 0,-4 3,-8 8,-8 11,0 22,5 29,12 8,8 13,19 13,30 0,-11 4,-22 12,-30 8,-7 18,-12 30,-12 4,0 7,4 7,8 0,4 -3,7 -7,7 -8,0 -14,3 -19,8 -5,5 -8,12 -8,19l0 39 33 -19 0 0c7,-4 11,-10 13,-17 2,-6 1,-14 -3,-20 -2,-4 -1,-8 3,-11 4,-2 8,0 10,3 6,10 7,22 5,32 -3,11 -10,20 -20,26l0 0 -21 12 24 0c8,0 14,-3 19,-8 5,-5 8,-12 8,-19 0,-5 3,-8 8,-8 4,0 7,3 7,8 0,11 -4,22 -12,29 -8,8 -18,13 -30,13l-44 0 0 44c0,7 3,14 8,19 5,5 11,8 19,8 4,0 7,3 7,8 0,4 -3,7 -7,7 -12,0 -22,-5 -30,-12 -8,-8 -12,-18 -12,-30 0,12 -5,22 -13,30 -7,7 -18,12 -29,12 -5,0 -8,-3 -8,-7 0,-5 3,-8 8,-8 7,0 14,-3 19,-8 5,-5 8,-12 8,-19l0 -44 -45 0c-11,0 -22,-5 -30,-13 -7,-7 -12,-18 -12,-29 0,-5 4,-8 8,-8 4,0 7,3 7,8 0,7 3,14 8,19 5,5 12,8 19,8l24 0 -21 -12 0 0c-10,-6 -16,-15 -19,-26 -3,-10 -2,-22 4,-32 2,-3 7,-5 10,-3 4,3 5,7 3,11 -4,6 -4,14 -3,20 2,7 7,13 13,17l0 0 34 19z"/>
				</g>
			</svg>
			<br />
			<small class="nogal">
				<b>nogal</b><br />
				the most simple PHP framework<br />
				click <a href="https://github.com/hytcom/wiki/tree/master/nogal" target="_blank">aqu&iacute;</a> para mas info<br />
				<br />
			</small>

			<h3>VERSION</h3>
			<?php echo $ngl("shift")->html($ngl("sysvar")->VERSION, ["classes"=>"table table-striped table-bordered"]); ?>
			<br /><br />
			<h3>OBJECTS</h3>
			<?php echo $ngl("shift")->html($ngl()->availables(), ["classes"=>"table table-striped table-bordered"]); ?>
			<br /><br />
			<h3>CONSTANTS</h3>
			<?php echo $ngl("shift")->html($ngl()->constants(), ["classes"=>"table table-striped table-bordered"]); ?>
			<br /><br />
			<h3>TESTS</h3>
			<table class="class table table-striped table-bordered">
				<tr class="class-head">
					<th width="30%" class="class-head-cell">RIND</th>
					<td class="class-cell">
						<ul>
							<li><a href="/test" target="_blank">TEST</a></li>
							<li><a href="/test-nut" target="_blank">TEST-NUT</a></li>
							<li><a href="/subfolder/" target="_blank">/subfolder/ (environment variables)</a></li>
							<li><a href="/subfolder/text" target="_blank">/subfolder/text (variable)</a></li>
					</td>
				</tr>
				<tr class="class-head">
					<th class="class-head-cell">NUTS LINKS</th>
					<td class="class-cell">
						<ul>
							<li><a href="/nut/pecan/color" target="_blank">WITHOUT ARGUMENTS</a></li>
							<li><a href="/nut/pecan/getjson?url=https://cdn.upps.cloud/json/postas_vacunacion_covid.geojson&claim=features&format=json" target="_blank">WITH ARGUMENTS</a></li>
						</ul>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>