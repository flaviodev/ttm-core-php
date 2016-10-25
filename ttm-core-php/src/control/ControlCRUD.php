<?php

namespace ttm\control;

use ttm\dao\DaoFactory;
use ttm\dao\DoctrineDao;
use ttm\model\ObjectBO;

class ControlCRUD {
	private $entityName;
	private $dao;
	
	public function __construct($entityName, array $config) {
		$this->entityName = $entityName;
		$this->dao = DaoFactory::getInstance(DoctrineDao::class, $config);
	}
	
	public function getEntity($key):ObjectBO {
		return $this->dao->find($this->entityName, $key);
	}
	
	public function getEntities():array {
		return $this->dao->findAll($this->entityName);
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