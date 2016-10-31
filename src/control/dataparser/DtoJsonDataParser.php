<?php

namespace ttm\control\dataparser;

use ttm\control\DataParser;
use ttm\util\Util;
use ttm\util\UtilDate;
use ttm\model\ObjectBO;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * Class DtoJsonDataParser - implements the DataParser methods for parsing data on 
 * request/reply process using json. Implementes a dynamic mechanism using reflection to
 * setting data of the on object to other.
 * 
 * @see ttm\control\DataParser 
 *
 * @package ttm-core-php
 * @namespace ttm\control
 * @version 1.0
 */
class DtoJsonDataParser implements DataParser {
	
	/**
	 * @method parseInputData - implements method to parse the input data, creating 
	 * a object using json decode method
	 *
	 * @param $inputData - json input data 
	 * @return a stdClass object with parsed data
	 *
	 * @access public
	 * @since 1.0
	 */
	public function parseInputData($inputData) {
		if(is_null($inputData)) {
			return null;
		}
		
		return json_decode($inputData,false,JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @method parseOutputData - implements method to parse the output data to an
	 * object using json encode. Whether the output object is a business object the
	 * method delegates the parse to parseObjectBOToObject before the json encode.  
	 *
	 * @param $outputData - output data for parsing
	 * @return json encoded data
	 *
	 * @access public
	 * @since 1.0
	 */
	public function parseOutputData($outputData) {
		if(is_null($outputData)) {
			return null;
		}

		// checking whether the $outputData is a array or an iterator object
		if(is_array($outputData) || is_subclass_of($outputData, \ArrayAccess::class)) {
			$arrayFO = array();
			foreach ($outputData as $item) {
				// checking whether the item is a business object
				if(is_subclass_of($item, ObjectBO::class)) {
					array_push($arrayFO, $this->parseObjectBOToObject($item));
				} else {
					array_push($arrayFO, $item);
				}
			}
		
			return $this->mountReturn($arrayFO);
		} else {
			// checking whether the output data is a business object
			if(is_subclass_of($outputData, ObjectBO::class)) {
				return $this->mountReturn($this->parseObjectBOToObject($outputData));
			}	
			
			return $this->mountReturn($outputData);
		}
	}

	/**
	 * @method mountReturn - mounts the centered type of return.  
	 *
	 * @param $outputData - output data for parsing
	 * @return json encoded data
	 *
	 * @access private
	 * @since 1.0
	 */
	private function mountReturn($outputData) {
		return json_encode($outputData,false,JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @method parseObjectToBO - implements method to parse the intput object to a business 
	 * object (model). Using reflection for setting the data of input object to a reference 
	 * of a business object, usually for creating or update a register on data base. 
	 *
	 * @param $object - stdClass input data for parsing
	 * @param &$objectBO - reference of business object for setting parsed data
	 *
	 * @throws InvalidArgumentException - whether paramater $object is null
	 * @throws InvalidArgumentException - whether parameter &$objectBO is null
	 *
	 * @access public
	 * @since 1.0
	 */
	public function parseObjectToBO($object,ObjectBO &$objectBO){
		if(is_null($object)) {
			throw new \InvalidArgumentException("Input object can't be null");
		}
		
		if(is_null($objectBO)) {
			throw new \InvalidArgumentException("Reference of the output objectBO can't be null");
		}
		
		$reflectionObject = new \ReflectionObject($object);
		
		// getting properties of object
		foreach ($reflectionObject->getProperties() as $prop) {

			// assembling setter method
			$function = Util::doMethodName($prop->getName(),"set");
			
			// checking whether method exists
			if((int)method_exists($objectBO,$function) > 0) {
				$reflectionMethod = new \ReflectionMethod($objectBO, $function);
				
				// checking type parameter of setter method
				$reflectionPar = $reflectionMethod->getParameters()[0];
		
				$value = $prop->getValue($object);
				// whether paramter is a Date, make conversion 
				if(strcasecmp($reflectionPar->getType(),"DateTime")===0) {
					$value = UtilDate::stringToDate($value);
				}
		
				// invoking setter method with input value
				$reflectionMethod->invoke($objectBO, $value);
			}
		}
		
	}

	/**
	 * @method parseObjectBOToObject - implements method to parse the business object 
	 * to a simple output data (DTO - data transport object) using json.
	 * Such as parseObjectToBO method, this mechanism uses also reflection mechanisms for
	 * setting the data of the business object to the output object. This implementation
	 * encapsulates a parsing mechanism for conversting of a model class to a dto class, 
	 * obtaing the attribuites of model class. But cant send all attributes to layer view, 
	 * so was implementedt the filter process to separate what the attributes that will be 
	 * sent on reply, dont sending for examples attribuites used on collection mapping (orm). 
	 * This filter uses a anotation in attributes (@ttm-DtoAttribute) that should be sent. 
	 *
	 * @param $objectBO - business object input for parsing
	 * @return - object dto with parsed data
	 * 
	 * @throws InvalidArgumentException - whether $objectBO is null
	 *
	 * @access public
	 * @since 1.0
	 */
	public function parseObjectBOToObject(ObjectBO $objectBO){
		if(is_null($objectBO)) {
			throw new \InvalidArgumentException("ObjectBO can't be null");
		}
		
		$objectDTO = new \stdClass();
		
		$reflectionObject = new \ReflectionObject($objectBO);
		
		// getting properties of business object
		foreach ($reflectionObject->getProperties() as $prop) {
			// checking whether attribute has anotation that indicates for including on DTO
			if(strpos($prop->getDocComment(), "@ttm-DtoAttribute")>-1) {
				$property = $prop->getName();
		
				// assembling getter method
				$function = Util::doMethodName($property,"get");
				if((int)method_exists($objectBO,$function) > 0) {
					$reflectionMethod = new \ReflectionMethod($objectBO, $function);
		
					$value = $reflectionMethod->invoke($objectBO, null);
		
					// checking the value, whether is a date make conversion
					if(is_a($value, \DateTime::class)) {
						$value = UtilDate::dateToString($value);
					}
		
					$objectDTO->$property = $value;
				} else {
					// same process where attribute is a boolean type 
					$function = Util::doMethodName($property,"is");
					if((int)method_exists($objectBO,$function) > 0) {
						$reflectionMethod = new \ReflectionMethod($objectBO, $function);
		
						$value = $reflectionMethod->invoke($objectBO, null);
						$objectDTO->$property = $value;
					}
				}
			}
		}
		
		return $objectDTO;
	}
}