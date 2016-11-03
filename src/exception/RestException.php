<?php

namespace ttm\exception;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * RestException - Exception class to the treatement of Rest operations exceptions,
 * keeping the http status to send on response
 *
 * @see TTMExceptions
 *
 * @package ttm-core-php
 * @namespace ttm\exception
 * @version 1.0
 */
class RestException extends TTMException {
	
	/**
	 * @property $httpStatus - this attribute keeps the http status for sending to 
	 * client on exceptions 
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected $httpStatus;

	/**
	 * @method __construct 
	 *
	 * @param $message - message about exception
	 * @param $httpStatus - http status for sending to cliente
	 *
	 * @access public
	 * @magic
	 * @since 1.0
	 */
	public function __construct($message, $httpStatus) {
		parent::__construct($message);
		$this->httpStatus = $httpStatus;
	}
	
	
	/**
	 * @method getHttpStatus 
	 *
	 * @return http status for sending to client
	 *
	 * @access public
	 * @since 1.0
	 */
	public function getHttpStatus() {
		return $this->httpStatus;
	}

	/**
	 * @method setHttpStatus
	 *
	 * @param $httpStatus - http status for sending to client
	 *
	 * @access public
	 * @since 1.0
	 */
	public function setHttpStatus($httpStatus) {
		$this->httpStatus = $httpStatus;
	}
}