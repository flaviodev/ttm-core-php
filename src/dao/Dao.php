<?php
namespace ttm\dao;

use ttm\model\ObjectBO;

/**
 * Interface DAO - Data Access Object. It's used for interface (uncouple)
 * components for comunication with de integration tier
 *
 * @author flaviodev - Flávio de Souza - fdsdev@gmail.com
 * @version 1.0
 * @package ttm-core-php
 * @namespace ttm\dao
 */
interface Dao {
	
	/**
	 * @method find - finds the mapped object on data base (orm) corresponding to a type of 
	 * class (entity) and a id informed
	 *
	 * @param $entity - class of object (entity) mapped on data base
	 * @param $id - primary key for find register on data base
	 * @return ttm\model\ObjectBO - mapped object fill with data
	 *  
     * @abstract @access public 
     * @since 1.0
	*/
	public function find($entity,$id):ObjectBO;

	/**
	 * @method findAll - finds all mapped objects on data base (orm) corresponding 
	 * to a type of class (entity)
	 * 
	 * @param $entity - class of object (entity) mapped on data base
	 * @return array - mapped objects fill with data
	 * 
	 * @abstract @access public
     * @since 1.0
	 */
	public function findAll($entity):array;

	/**
	 * @method update - updates data base register associated to mapped entity 
	 *
	 * @param ttm\model\ObjectBO $entity - Object (entity) mapped on data base
	 * 
	 * @abstract @access public
	 * @since 1.0
	 */
	public function update(ObjectBO $entity);

	/**
	 * @method remove - removes (delete) data base register associated to mapped entity
	 *
	 * @param ttm\model\ObjectBO $entity - Object (entity) mapped on data base
	 * 
	 * @abstract @access public
	 * @since 1.0
	 */
	public function remove(ObjectBO $entity);

	/**
	 * @method create - creates (insert) data base register associated to mapped entity
	 *
	 * @param ttm\model\ObjectBO $entity - Object (entity) mapped on data base
	 * @return ttm\model\ObjectBO - Object (entity) mapped on data base after register on 
	 * data base, that have all data on data base (example: auto-generated id)
	 * 
	 * @abstract @access public
	 * @since 1.0
	 */
	public function create(ObjectBO $entity):ObjectBO;

	/**
	 * @method getEntityManager - creates a instance of entity manager corresponding 
	 * orm api used
	 *
	 * @param array $options - array of options to creation of entity manager
	 * 
	 * @abstract @access public
	 * @since 1.0
	 */
	public function getEntityManager(array $options=null);
	
}