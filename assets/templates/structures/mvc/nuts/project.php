<?php

namespace nogal;

class nutProject extends nglNut {

	protected function init() {
		$this->SafeMethods(["test"]);
	}

	protected function test($aArguments) {
		return "testing the nut";
	}

	protected function testUnsafe($aArguments) {
		// this nut only run when
		// safemode = false;
		return "testing the unsafe nut";
	}
}

?>