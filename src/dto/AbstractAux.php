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
		if(is_null($key))
			return null;
		
		$bo = $this->crud->getEntity($key);
		return $this->parseDTO ($bo);
	}

	public function getBO($key):ObjectBO {
		if(is_null($key))
			return null;
		
		return $this->crud->getEntity($key);
	}

	public function getBOs():array {
		return $this->crud->getEntities();
	}
	
	public function getDTOs():array {
		$bos = $this->crud->getEntities();
		
		$dtos = array();
		foreach($bos as $bo) {
			array_push($dtos, $this->parseDTO($bo));
		}
		
		return $dtos;
	}
	
	public function create($dto):ObjectDTO {
		if(is_null($dto))
			return null;
		
		$bo = $this->parseNewBO($dto);
		$bo = $this->crud->createEntity($bo);
		
		return $this->parseDTO($bo);
	}
	
	public function update($dto){
		if(is_null($dto))
			return null;
		
		$bo = $this->crud->getEntity($dto->id);
		$this->updateBO($dto,$bo);
		$this->crud->updateEntity($bo);
	}
	
	public function delete($key) {
		if(is_null($key))
			return;
		
		$bo = $this->crud->getEntity($key);
		$this->crud->deleteEntity($bo);
	}
	
	public function parseDTOs($bos):array {
		if(is_null($bos))
			return null;
		
		$dtos = array();
		foreach($bos as $bo) {
			array_push($dtos, $this->parseDTO($bo));
		}
		
		return $dtos;
	}
	
	protected abstract function parseNewBO($dto):ObjectBO;
	protected abstract function updateBO($dto,ObjectBO &$bo);
	protected abstract function parseDTO(ObjectBO $bo):ObjectDTO;
	
}