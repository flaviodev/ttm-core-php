<?php

namespace ttm\control;

use ttm\dao\DaoFactory;
use ttm\model\ObjectBO;
use ttm\util\UtilDate;
use ttm\util\Util;
use ttm\Config;
use ttm\exception\DaoException;

class ServiceHelper {
	private $dao;

	public function __construct(array $config) {
		$this->dao =  DaoFactory::getInstance($config["dao"], $config);
	}

	public function getEntity($entity,$id):ObjectBO {
		if(is_null($id) || is_null($entity)){
			return null;
		}
		return $this->dao->find($entity, $id);
	}

	public function getEntities($entity):array {
		if(is_null($entity)){
			return null;
		}

		return $this->dao->findAll($entity);
	}

	public function createNewEntity($entity,$object):ObjectBO {
		if(is_null($object) || is_null($entity)){
			return null;
		}

		$objectBO = new $entity;
		Config::getDataParser()->parseObjectToBO($object,$objectBO);

		$objectBO->setId(0);

		return $this->dao->create($objectBO);
	}

	public function updateEntity($entity,$object){
		if(is_null($object) || is_null($entity) || !isset($object->id)){
			return;
		}

		$objectBO = $this->dao->find($entity,$object->id);
		if (!is_null($objectBO)) {
			Config::getDataParser()->parseObjectToBO($object,$objectBO);
			$this->dao->update($objectBO);
		}
	}

	public function deleteEntity($entity,$id) {
		if(is_null($id) || is_null($entity)){
			return;
		}

		$objectBO = $this->dao->find($entity, $id);

		if (!is_null($objectBO)) {
			$this->dao->remove($objectBO);
		}
	}
}