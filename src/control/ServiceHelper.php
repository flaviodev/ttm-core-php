<?php

namespace ttm\control;

use ttm\dao\DaoFactory;
use ttm\model\ObjectBO;
use ttm\util\UtilDate;
use ttm\util\Util;

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
		$this->parseObjectToBO($object,$objectBO);

		$objectBO->setId(0);

		return $this->dao->create($objectBO);
	}

	public function updateEntity($entity,$object){
		if(is_null($object) || is_null($entity) || !isset($object->id)){
			return;
		}

		$objectBO = $this->dao->find($entity,$object->id);
		if (!is_null($objectBO)) {
			$this->parseObjectToBO($object,$objectBO);
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

	private function parseObjectToBO($object,ObjectBO &$objectBO) {
		$reflectionObject = new \ReflectionObject($object);

		foreach ($reflectionObject->getProperties() as $prop) {
			$function = Util::doMethodName($prop->getName(),"set");
			if((int)method_exists($objectBO,$function) > 0) {
				$reflectionMethod = new \ReflectionMethod($objectBO, $function);
				$reflectionPar = $reflectionMethod->getParameters()[0];

				$value = $prop->getValue($object);
				if(strcasecmp($reflectionPar->getType(),"DateTime")===0) {
					$value = UtilDate::stringToDate($value);
				}

				$reflectionMethod->invoke($objectBO, $value);
			}
		}
	}
}