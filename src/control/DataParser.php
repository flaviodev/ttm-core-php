<?php

namespace ttm\control;

use ttm\model\ObjectBO;


/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * Interface DataParser - defines the methods for parsing data on request/reply process.
 * This interface promotes high cohesion and low coupling, centralizing the process of
 * data change between layers, and allows to change the communication machanism with low impact
 *
 * @package ttm-core-php
 * @namespace ttm\control
 * @abstract
 * @version 1.0
 */
interface DataParser {
	
	/**
	 * @method parseInputData - parses the input data to type of data treated by layer control
	 *
	 * @param $inputData - input data for parsing
	 * @return parsed data
	 *
	 * @abstract @access public
	 * @since 1.0
	 */
	public abstract function parseInputData($inputData);

	/**
	 * @method parseOutputData - parses the output data to type of data treated by layer view
	 *
	 * @param $outputData - output data for parsing
	 * @return parsed data
	 * 
	 * @abstract @access public
	 * @since 1.0
	 */
	public abstract function parseOutputData($outputData);

	/**
	 * @method parseObjectToBO - parses the intput object to a business object (model). This
	 * mechanism promotes a dynamic dynamic and centered of view data to a handle object on 
	 * layer control stereotypes, setting attributes and converting data types (ex: numbers 
	 * and dates)   
	 *
	 * @param $object - view data input for parsing
	 * @param &$objectBO - reference of business object for setting parsed data
	 *
	 * @throws InvalidArgumentException - whether paramater $object is null
	 * @throws InvalidArgumentException - whether parameter &$objectBO is null
	 * 
	 * @abstract 
	 * @access public
	 * @since 1.0
	 */
	public function parseObjectToBO($object,ObjectBO &$objectBO);

	/**
	 * @method parseObjectBOToObject - parses the business object to a simple output
	 * data on standard layer view (DTO - data transport object). 
	 * Such as parseObjectToBO method, this mechanism promotes also a dynamic and centered 
	 * parsing, but now of the business object to a handle object on layer view, setting 
	 * attributes and converting data types (ex: numbers and dates)
	 *
	 * @param $objectBO - business object input for parsing
	 * @return - object with parsed data
	 *
	 * @throws InvalidArgumentException - whether $objectBO is null
	 *
	 * @abstract 
	 * @access public
	 * @since 1.0
	 */
	public function parseObjectBOToObject(ObjectBO $objectBO);
}