<?php

namespace ttm\dto;

use ttm\control\ControlCRUD;
use ttm\model\ObjectBO;

abstract class AbstractHelper {
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
	
	protected abstract function parseNewBO($object):ObjectBO;
	protected abstract function updateBO($object,ObjectBO &$bo);
	
}