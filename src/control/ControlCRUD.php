<?php

namespace ttm\control;

use ttm\dao\DaoFactory;
use ttm\model\ObjectBO;

class ControlCRUD {
	private $dao;
	
	public function __construct(array $config) {
		$this->dao =  DaoFactory::getInstance($config["dao"], $config);
	}
	
	public function getEntity($entityName,$id):ObjectBO {
		return $this->dao->find($entityName, $id);
	}
	
	public function getAllEntities($entityName):array {
		return $this->dao->findAll($entityName);
	}
	
	public function createEntity(ObjectBO $entity):ObjectBO {
		return $this->dao->create($entity);
	}
	
	public function deleteEntity(ObjectBO $entity) {
		$this->dao->remove($entity);
	}
	
	public function updateEntity(ObjectBO $entity) {
		$this->dao->update($entity);
	}
}

?>