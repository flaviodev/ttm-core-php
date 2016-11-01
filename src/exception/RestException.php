<?php

namespace ttm\exception;

class RestExcpetion extends TTMException {
	
	protected $httpStatus;
	
	public function __construct($message, $httpStatus) {
		parent::__construct($message);
		$this->httpStatus = $httpStatus;
	}
	
	public function getHttpStatus() {
		return $this->httpStatus;
	}
	
	public function setHttpStatus($httpStatus) {
		$this->httpStatus = $httpStatus;
	}
}