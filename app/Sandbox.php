<?php


namespace GigyaHelper;


use Gigya\PHP\GSRequest;

class Sandbox extends Application {

	public function get_GSRequest(string $method): GSRequest {
		return parent::get_GSRequest($method);
	}

	public function run() {
		// TODO: Implement run() method.
	}
}