<?php

namespace ttm\control;

use ttm\Config;
use ttm\dao\DaoFactory;
use ttm\model\ObjectBO;
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

	public function get($entity, $id):ObjectBO{
		$this->doEntityValidation($entity);
		$this->doIdValidation($id);
	
		return $this->dao->find($entity, $id);
	}
	
	public function getAll($entity):array{
		$this->doEntityValidation($entity);
		
		return $this->dao->findAll($entity);
	}
	
	//TODO implements
	public function getCriteria($entity,$attribute, $expression):array{
		$this->doEntityValidation($entity);
		
		return $this->dao->findAll($entity);
	}
	
	public function create($entity,$object):ObjectBO{
		$this->doEntityValidation($entity);
		$this->doObjectValidation($object);
		
		$objectBO = new $entity;
		Config::getDataParser()->parseObjectToBO($object,$objectBO);
		
		$objectBO->setId(0);
		
		return $this->dao->create($objectBO);
	}
	
	public function update($entity,$object){
		$this->doEntityValidation($entity);
		$this->doObjectValidation($object);

		if(!isset($object->id)){
			throw new \InvalidArgumentException("The object id shoul be setted ");
		}
		
		$objectBO = $this->dao->find($entity,$object->id);
		
		$this->doReturnedObjectValidation($objectBO,$entity,$object->id,"updating");
				
		Config::getDataParser()->parseObjectToBO($object,$objectBO);
		$this->dao->update($objectBO);
	}
	
	public function delete($entity,$id){
		$this->doEntityValidation($entity);
		$this->doIdValidation($id);
				
		$objectBO = $this->dao->find($entity, $id);

		$this->doReturnedObjectValidation($objectBO,$entity,$id,"deleting");
		
		$this->dao->remove($objectBO);
	}

	//validations
	//TODO DAO validations
	
	private function doEntityValidation($entity) {
		if(is_null($entity)){
			throw new \InvalidArgumentException("The entity can't be null ");
		}
	
		if(!is_subclass_of($entity, ObjectBO::class)) {
			throw new \InvalidArgumentException("The entity '$entity' should be an ObjectBO!");
		}
	}
	
	private function doReturnedObjectValidation($objectBO,$entity,$id,$operation) {
		if(is_null($objectBO)){
			throw new TTMException("Object $entity:($id) not found for $operation");
		}
	}
	
	private function doIdValidation($id) {
		if(is_null($id)){
			throw new \InvalidArgumentException("The id can't be null ");
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