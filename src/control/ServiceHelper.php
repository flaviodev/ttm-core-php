<?php

namespace ttm\control;

use ttm\Config;
use ttm\dao\DaoFactory;
use ttm\model\Model;
use ttm\exception\TTMException;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * ServiceHelper - Helper class for encapsulating the treatment of the methods
 * dao invocation (CRUD operations)
 *
 * @package ttm-core-php
 * @namespace ttm\control
 * @final
 * @version 1.0
 */
final class ServiceHelper {
	/**
	 * @property $instance - it's the attribute associated with the singleton pattern 
	 *
	 * @access private
	 * @static
	 * @since 1.0
	 */
	private static $instance;
	
	private $daos;
	
	public static function getInstance(array $config) {
		if(is_null(ServiceHelper::$instance)) {
			ServiceHelper::$instance = new ServiceHelper($config);
		}
		
		return ServiceHelper::$instance;
	}
	
	private function __construct(array $config) {
		$this->daos =  DaoFactory::getInstance($config);
		if(is_null($this->daos) || sizeof($this->daos)==0) {
			throw new TTMException("There aren't dao configured");
		}
	}
	
	private function getDaoByEntity($entity) { 
		if(is_null($entity)) {
			throw new \InvalidArgumentException("The entity name can't be null");
		}

		//getting registred dao by model namespace 
		return $this->getDao(substr($entity,0,strripos($entity, "\\")));
	}

	private function getDao($dataSourceAlias) {
		if(is_null($dataSourceAlias)) {
			throw new \InvalidArgumentException("The alias data source can't be null");
		}
		
		$dao = null;
				
		if(isset($this->daos[$dataSourceAlias])) {
			$dao =  $this->daos[$dataSourceAlias];
		} else {
			throw new TTMException("There isnt data soure associated to entity");
		}
	
		return $dao;
	}
	
	public function get($entity, $id):Model{
		$this->doEntityValidation($entity);
		$dataSource = $this->getDaoByEntity($entity);
		
		return $dataSource->find($entity,$id);
	}
	
	public function getAll($entity):array{
		$this->doEntityValidation($entity);
		
		$dataSource = $this->getDaoByEntity($entity);
				
		return $dataSource->findAll($entity);
	}
	
	public function getCriteria($entity, $entityQueryAlias ,$expression):array {
		$this->doEntityValidation($entity);
		
		$entityQuery = "SELECT ".$entityQueryAlias." FROM ".$entity." ".$entityQueryAlias." WHERE ".$expression;
		
		return $this->getResult($entityQuery, null, $entity);
	}

	public function getResult($entityQuery, $parameters, $dataSourceAlias=null):array {
		$dataSource = $this->getDaoByEntity($dataSourceAlias);
	
		return $dataSource->getResult($entityQuery, $parameters);
	}
	
	public function getResultSet($sqlQuery, $parameters, $dataSourceAlias=null):array {
		$dataSource = $this->getDaoByEntity($dataSourceAlias);
	
		return $dataSource->getResultSet($sqlQuery, $parameters);
	}
	
	public function create($entity, $object):Model {
		$this->doEntityValidation($entity);
		$this->doObjectValidation($object);
		
		$model = new $entity();
		Config::getDataParser()->parseObjectToModel($object,$model);
		
		$model->setId(0);
		$dataSource = $this->getDaoByEntity($entity);
		
		return $dataSource->create($model);
	}
	
	public function update($entity, $object){
		$this->doEntityValidation($entity);
		$this->doObjectValidation($object);

		if(!isset($object->id)){
			throw new \InvalidArgumentException("The object id should be setted");
		}
		
		$dataSource = $this->getDaoByEntity($entity);
		
		$model = $dataSource->find($entity,$object->id);
		
		$this->doReturnedObjectValidation($model,$entity,$object->id,"updating");
				
		Config::getDataParser()->parseObjectToModel($object,$model);
		$dataSource->update($model);
	}
	
	public function delete($entity, $id){
		$this->doEntityValidation($entity);
		$this->doIdValidation($id);
				
		$dataSource = $this->getDaoByEntity($entity);
		
		$model = $dataSource->find($entity, $id);

		$this->doReturnedObjectValidation($model,$entity,$id,"deleting");
		
		$dataSource->remove($model);
	}

	//validations
	//TODO DAO validations (ex: before update)
	
	private function doEntityValidation($entity) {
		if(is_null($entity)){
			throw new \InvalidArgumentException("The entity can't be null");
		}
	}
	
	private function doReturnedObjectValidation($model, $entity, $id, $operation) {
		if(is_null($model)){
			throw new TTMException("Object ".$entity.":(".$id.") not found for ".$operation);
		}
	}
	
	private function doIdValidation($id) {
		if(is_null($id)){
			throw new \InvalidArgumentException("The id can't be null");
		}
	}

	private function doObjectValidation($object) {
		if(is_null($object)){
			throw new \InvalidArgumentException("The object can't be null ");
		}
	}
	
	/**
	 * @method __clone - prevents cloning on this instance
	 *
	 * @access private
	 * @since 1.0
	 */
	private function __clone() {}
	
	/**
	 * @method __wakeup - prevents unserialize on this instance
	 *
	 * @access private
	 * @since 1.0
	 */
	private function __wakeup() {}
}