<?php

namespace ttm\control;

use ttm\Config;
use ttm\dao\DaoFactory;
use ttm\model\Model;
use ttm\exception\TTMException;

final class ServiceHelper {
	private static $instance;
	private $dao;

	// TODO Factory dao exception
	private static function getDao(array $config) {
		if(is_null(ServiceHelper::$dao)) {
			ServiceHelper::$dao = DaoFactory::getInstance($config["dao"], $config);
		}
	}
	
	public static function getInstance(array $config) {
		if(is_null(ServiceHelper::$instance)) {
			ServiceHelper::$instance = new ServiceHelper($config);
		}
		
		return ServiceHelper::$instance;
	}
	
	private function __construct(array $config) {
		$this->dao =  DaoFactory::getInstance($config["dao"], $config);
	}

	public function get($entity, $id):Model{
		$this->doEntityValidation($entity);
	}
	
	public function getAll($entity):array{
		$this->doEntityValidation($entity);

		return $this->dao->findAll($entity);
	}
	
	//TODO implements
	public function getCriteria($entity, $attribute, $expression):array {
		$this->doEntityValidation($entity);
		
		return $this->dao->findAll($entity);
	}
	
	public function create($entity,$object):Model {
		$this->doEntityValidation($entity);
		$this->doObjectValidation($object);
		
		$model = new $entity();
		Config::getDataParser()->parseObjectToModel($object,$model);
		
		$model->setId(0);
		
		return $this->dao->create($model);
	}
	
	public function update($entity,$object){
		$this->doEntityValidation($entity);
		$this->doObjectValidation($object);

		if(!isset($object->id)){
			throw new \InvalidArgumentException("The object id should be setted");
		}
		
		$model = $this->dao->find($entity,$object->id);
		
		$this->doReturnedObjectValidation($model,$entity,$object->id,"updating");
				
		Config::getDataParser()->parseObjectToModel($object,$model);
		$this->dao->update($model);
	}
	
	public function delete($entity,$id){
		$this->doEntityValidation($entity);
		$this->doIdValidation($id);
				
		$model = $this->dao->find($entity, $id);

		$this->doReturnedObjectValidation($model,$entity,$id,"deleting");
		
		$this->dao->remove($model);
	}

	//validations
	//TODO DAO validations
	
	private function doEntityValidation($entity) {
		if(is_null($entity)){
			throw new \InvalidArgumentException("The entity can't be null");
		}
	
// 		if(!is_subclass_of($entity, Model::class)) {
// 			throw new \InvalidArgumentException("The entity '$entity' should be an Model!");
// 		}
	}
	
	private function doReturnedObjectValidation($model,$entity,$id,$operation) {
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
	 * Método clone do tipo privado previne a clonagem dessa instância
	 * da classe
	 *
	 * @return void
	 */
	private function __clone()
	{
	}
	
	/**
	 * Método unserialize do tipo privado para prevenir a desserialização
	 * da instância dessa classe.
	 *
	 * @return void
	 */
	private function __wakeup()
	{
	}

}