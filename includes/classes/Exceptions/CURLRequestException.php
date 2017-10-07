<?php

namespace App\Exceptions;

class CURLRequestException extends \Exception {
	public function __construct($errMsg, $errCode, string $curlError){
		parent::__construct("$errMsg (HTTP $errCode)", $errCode);
		if (!empty($curlError))
			$this->message .= "; $curlError";
	}
}
