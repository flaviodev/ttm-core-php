<?php
namespace ttm\control;

use ttm\model\ObjectBO;

class ControlAux {
	private $entityName;
	private $crud;
	
	public function __construct($daoName,$entityName, array $config) {
		$this->entityName = $entityName;
		$this->crud = new ControlCRUD($daoName,$entityName, $config);
	}

	public function getDTO($key) {
		$objectBO = $this->crud->getEntity($key);
		return $this->getDTOByBO($objectBO);
	}

	public function getDTOs() {
		$objectBOs = $this->crud->getEntities();
		
		$arrayDTOs = array();
		foreach ($objectBOs as $objectBO) {
			array_push($arrayDTOs, $this->getDTOByBO($objectBO));
		}
		
		return $arrayDTOs;
	}
	
	public function create($dto) {
		$entity = $this->getBOByDTO($dto);
		$this->crud->createEntity($entity);
	}
	
	public function update($dto) {
		$entity = $this->getBOByDTO($dto);
		$this->crud->updateEntity($entity);
	}
	
	public function delete($key) {
		$entity = $this->crud->getEntity($key);
		$this->crud->deleteEntity($entity);
	}
		
	
	private function getDTOByBO(ObjectBO $objectBO) {
		$dto = array();

		$reflectionClass = new \ReflectionClass($objectBO);
		$methods = $reflectionClass->getMethods();

		foreach ($methods as $method) {
			$name = $method->getName();

			if(strpos($name, "get")===0) {
				$this->setAttributeDTOByBO($name, "get", $method, $objectBO, $dto);
			} else if (strpos($name, "is")===0) {
				$this->setAttributeDTOByBO($name, "is", $method, $objectBO, $dto);
			}
		}

		return $dto;
	}

	private function getBOByDTO($dto) {
		$objectBO = new $this->entityName();
	
		$reflectionClass = new \ReflectionClass($objectBO);
		$methods = $reflectionClass->getMethods();
	
		foreach ($methods as $method) {
			$name = $method->getName();
	
			if(strpos($name, "get")===0) {
				$this->setAttributeBOByDTO($name, "get", $method, $dto, $objectBO);
			} else if (strpos($name, "is")===0) {
				$this->setAttributeBOByDTO($name, "is", $method, $dto, $objectBO);
			}
		}
	
		return $objectBO;
	}
	
	
	private function setAttributeDTOByBO($name, $prefix, $method, $objectBO, &$dto) {
		$attributeName = substr($name, strlen($prefix));
		$attributeNameLow = strtolower(substr($attributeName,0,1)).substr($attributeName, 1);
		$attributeType = $method->getReturnType()->__toString();
		$attributeValue = $method->invoke($objectBO);

		if(strcasecmp($attributeType, "DateTime")===0) {
			$attributeValue = $attributeValue->format("Y-m-d");
		}

		$dto[$attributeNameLow] = $attributeValue;
	}
	
	
	private function setAttributeBOByDTO($name, $prefix, $method, $dto, &$objectBO) {
		$attributeName = substr($name, strlen($prefix));
		$attributeNameLow = strtolower(substr($attributeName,0,1)).substr($attributeName, 1);
		$attributeType = $method->getReturnType()->__toString();
		$attributeValue = $method->invoke($dto);
	
		if(strcasecmp($attributeType, "DateTime")===0) {
			$strDay = substr($attributeValue, 8,2);
			$strMonth = substr($attributeValue, 5,2);
			$strYear = substr($attributeValue, 0,4);
			$dateTime = new \DateTime();
			$dateTime->setDate($strYear, $strMonth, $strDay);
			
			$attributeValue = $dateTime;
		}
	
		$objectBO[$attributeNameLow] = $attributeValue;
	}
}