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
	 * @method Return a mapped object on data base (orm) corresponding to a type of 
	 * class (entity) and a id informed
	 *
     * @since 1.0
     * @abstract
	 * @access public
	 * @param $entity - class of object (entity) mapped on data base
	 * @param $id - primary key for find register on data base
	 * @return ttm\model\ObjectBO - mapped object fill with data 
	*/
	public function find($entity,$id):ObjectBO;

	/**
	 * @method Return all mapped objects on data base (orm) corresponding to a type of class (entity)
	 * 
     * @since 1.0
     * @abstract
	 * @access public
	 * @param $entity - class of object (entity) mapped on data base
	 * @return array - mapped objects fill with data
	 */
	public function findAll($entity):array;

	/**
	 * @method Update data base register associated to mapped entity 
	 *
	 * @since 1.0
	 * @abstract
	 * @access public
	 * @param ttm\model\ObjectBO $entity - Object (entity) mapped on data base
	 */
	public function update(ObjectBO $entity);

	/**
	 * @method Remove (delete) data base register associated to mapped entity
	 *
	 * @since 1.0
	 * @abstract
	 * @access public
	 * @param ttm\model\ObjectBO $entity - Object (entity) mapped on data base
	 */
	public function remove(ObjectBO $entity);

	/**
	 * @method Create (insert) data base register associated to mapped entity
	 *
	 * @since 1.0
	 * @abstract
	 * @access public
	 * @param ttm\model\ObjectBO $entity - Object (entity) mapped on data base
	 * @return ttm\model\ObjectBO - Object (entity) mapped on data base after register on 
	 * data base, that have all data on data base (example: auto-generated id)
	 */
	public function create(ObjectBO $entity):ObjectBO;

	/**
	 * @method Create a instance of entity manager corresponding orm api used
	 *
	 * @since 1.0
	 * @abstract
	 * @access public
	 * @param array $options - array of options to creation of entity manager
	 */
	public function getEntityManager(array $options=null);
	
}