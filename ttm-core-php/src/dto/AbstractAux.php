<?php

namespace ttm\dto;

use ttm\control\ControlCRUD;
use ttm\model\ObjectBO;

abstract class AbstractAux {
	private $entityName;
	private $crud;
	
	public function __construct($entityName, array $config) {
		$this->entityName = $entityName;
		$this->crud = new ControlCRUD($entityName, $config);
	}
	
	public function getDTO($key):ObjectDTO {
		$bo = $this->crud->getEntity($key);
		// return $this->getDTOByBO($bo);
		return $this->parseDTO ($bo);
	}
	
	public function getDTOs():array {
		$bos = $this->crud->getEntities();
		
		$dtos = array();
		foreach($bos as $bo) {
			// array_push($dtos, $this->getDTOByBO($bo));
			array_push($dtos, $this->parseDTO($bo));
		}
		
		return $dtos;
	}
	
	public function create($dto):ObjectDTO {
		// $bo = $this->getBOByDTO($dto);
		$bo = $this->parseNewBO($dto);
		$bo = $this->crud->createEntity($bo);
		
		return $this->parseDTO($bo);
	}
	
	public function update($dto){
		$bo = $this->crud->getEntity($dto->id);
		// $this->mergeBOByDTO($dto, $bo);
		$this->updateBO($dto,$bo);
		$this->crud->updateEntity($bo);
	}
	
	public function delete($key) {
		$bo = $this->crud->getEntity($key);
		$this->crud->deleteEntity($bo);
	}
	
	protected abstract function parseNewBO($dto):ObjectBO;
	protected abstract function updateBO($dto,ObjectBO &$bo);
	protected abstract function parseDTO(ObjectBO $bo):ObjectDTO;
	
	// private function getDTOByBO(ObjectBO $objectBO) {
	// $dto = array();
	
	// $reflectionClass = new \ReflectionClass($objectBO);
	// $methods = $reflectionClass->getMethods();
	
	// foreach ($methods as $method) {
	// $name = $method->getName();
	// if(strpos($name, "get")===0) {
	// $this->setAttributeDTOByBO($name, "get", $method, $objectBO, $dto);
	// } else if (strpos($name, "is")===0) {
	// $this->setAttributeDTOByBO($name, "is", $method, $objectBO, $dto);
	// }
	// }
	
	// return $dto;
	// }
	
	// private function mergeBOByDTO($dto, &$objectBO) {
	// $reflectionClass = new \ReflectionClass($objectBO);
	// $methods = $reflectionClass->getMethods();
	
	// foreach ($methods as $method) {
	// $name = $method->getName();
	
	// if(strpos($name, "set")===0) {
	// $this->setAttributeBOByDTO($name, "set", $method, $dto, $objectBO);
	// }
	// }
	// }
	
	// private function setAttributeDTOByBO($name, $prefix, $method, $objectBO, &$dto) {
	// $attributeName = substr($name, strlen($prefix));
	// $attributeNameLow = strtolower(substr($attributeName,0,1)).substr($attributeName, 1);
	// $attributeType = $method->getReturnType()->__toString();
	// $attributeValue = $method->invoke($objectBO);
	
	// if(strcasecmp($attributeType, "DateTime")===0) {
	// $attributeValue = $attributeValue->format("Y-m-d");
	// }
	
	// $dto[$attributeNameLow] = $attributeValue;
	// }
	
	// private function setAttributeBOByDTO($name, $prefix, $method, $dto, &$objectBO) {
	// $attributeName = substr($name, strlen($prefix));
	// $attributeNameLow = strtolower(substr($attributeName,0,1)).substr($attributeName, 1);
	
	// if(!(strcasecmp($attributeNameLow, "id")===0)) {
	// $attributeType = $method->getParameters()[0]->getType()->__toString();
	
	// $attributeValue = $dto->$attributeNameLow;
	
	// if(strcasecmp($attributeType, "DateTime")===0) {
	// $strDay = substr($attributeValue, 8,2);
	// $strMonth = substr($attributeValue, 5,2);
	// $strYear = substr($attributeValue, 0,4);
	// $dateTime = new \DateTime();
	// $dateTime->setDate($strYear, $strMonth, $strDay);
	
	// $attributeValue = $dateTime;
	// }
	
	// $method->invoke($objectBO,$attributeValue);
	// }
	// }
}