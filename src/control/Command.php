<?php

namespace ttm\control;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * Interface Command - defines a stereotype for offering a type of service to the 
 * view layer. This stereotype was designed for implementing a concept of the 
 * service that does not performs alterations on data business of the system, but only 
 * processes data for returning output data (informations, ex: resquest of reports) 
 *
 * @package ttm-core-php
 * @namespace ttm\control
 * @abstract
 * @version 1.0
 */
interface Command {
	public function execute(array $args);
}