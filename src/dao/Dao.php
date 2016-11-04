<?php
namespace ttm\dao;

use ttm\model\Model;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 * 
 * Interface DAO - Data Access Object. It's used for interface (uncoupled)
 * components for comunication with de integration tier
 *
 * @package ttm-core-php
 * @namespace ttm\dao
 * @abstract
 * @version 1.0
 */
interface Dao {
	
	/**
	 * @method find - finds the mapped object on data base (orm) corresponding to a type of 
	 * class (entity of model) and a id informed
	 *
	 * @param $entity - class of object (entity) mapped on data base
	 * @param $id - primary key for find register on data base
	 * @return ttm\model\Model - mapped object fill with data
	 *  
     * @access public 
     * @abstract 
     * @since 1.0
	*/
	public function find($entity,$id);

	/**
	 * @method findAll - finds all mapped objects on data base (orm) corresponding 
	 * to a type of class (entity)
	 * 
	 * @param $entity - class of object (entity) mapped on data base
	 * @return array - mapped objects fill with data
	 * 
	 * @access public
	 * @abstract 
     * @since 1.0
	 */
	public function findAll($entity);

	/**
	 * @method update - updates data base register associated to mapped entity 
	 *
	 * @param ttm\model\Model $entity - Object (entity) mapped on data base
	 * 
	 * @abstract @access public
	 * @since 1.0
	 */
	public function update(Model $entity);

	/**
	 * @method remove - removes (delete) data base register associated to mapped entity
	 *
	 * @param ttm\model\Model $entity - Object (entity) mapped on data base
	 * 
	 * @access public
	 * @abstract 
	 * @since 1.0
	 */
	public function remove(Model $entity);

	/**
	 * @method create - creates (insert) data base register associated to mapped entity
	 *
	 * @param ttm\model\Model $entity - Object (entity) mapped on data base
	 * @return ttm\model\Model - Object (entity) mapped on data base after register on 
	 * data base, that have all data on data base (example: auto-generated id)
	 * 
	 * @access public
	 * @abstract 
	 * @since 1.0
	 */
	public function create(Model $entity);

	
	/**
	 * @method getResult - returns registers associated to mapped entity based on a query
	 * on entity manager
	 *
	 * @param string $entityQuery - query (select) using the orm standards on implemented api 
	 * @param array $parameters - array of parameter for query
	 * @return a collection with objects (entity) returned by query
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 **/	
	public function getResult(string $entityQuery, array $parameters);
	
 	/**
	 * @method getResultSet - returns an array of the registers on database using a
	 * sql query
	 *
	 * @param string $sql - a select using sql
	 * @param array $parameters - array of parameter for query
	 * @return an array with the data returned by query
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 **/
	public function getResultSet(string $sql, array $parameters=null);
}