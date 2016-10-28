<?php

namespace ttm\control;

use ttm\control\ControlCRUD;
use ttm\model\ObjectBO;

class ServiceHelper {
	private $entityName;
	private $crud;
	
	public function __construct($entityName, array $config) {
		$this->entityName = $entityName;
		
		$this->crud = new ControlCRUD($entityName, $config);
	}
	
	public function getBO($key):ObjectBO {
		if(is_null($key))
			return null;
		
		return $this->crud->getEntity($key);
	}

	public function getBOs():array {
		return $this->crud->getEntities();
	}
		
	public function create($object):ObjectBO {
		if(is_null($object))
			return null;
		
		$bo = $this->parseNewBO($object);
		
		return $this->crud->createEntity($bo);
	}
	
	public function update($object){
		if(is_null($object))
			return null;
		
		$bo = $this->crud->getEntity($object->id);
		$this->updateBO($object,$bo);

		$this->crud->updateEntity($bo);
	}
	
	public function delete($key) {
		if(is_null($key))
			return;
		
		$bo = $this->crud->getEntity($key);
		
		$this->crud->deleteEntity($bo);
	}
	
	protected function parseNewBO($object):ObjectBO {
		if(is_null($object))
			return null;
		
		$bo = new $this->entityName;
		$this->parseObjectToBO($object,$bo);
		
		$bo->setId(null);
		
		return $bo;
	}

	protected function updateBO($object,ObjectBO &$bo) {
		$this->parseObjectToBO($object,$bo);
	}

	private function parseObjectToBO($object,ObjectBO &$bo) {
		$reflectionObject = new \ReflectionObject($object);

		foreach ($reflectionObject->getProperties() as $prop) {
			$function = $this->doMethodSet($prop->getName());
			if((int)method_exists($bo,$function) > 0) {
				$reflectionMethod = new \ReflectionMethod($bo, $function);
				$reflectionMethod->invokeArgs($bo, $prop->getValue());
			}
		}
	}
	
	
	private function doMethodSet($propertyName):string {
		$firstLetter = substr($propertyName,0,1);
		$wordRest = substr($propertyName,1);
		
		return "set".strtoupper($firstLetter).$wordRest;
	}
}