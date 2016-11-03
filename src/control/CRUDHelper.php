<?php

namespace ttm\control;

use ttm\exception\TTMException;
use ttm\model\Model;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * Interface CRUDHelper - interface of the helper service for encapsulating 
 * the treatment of the methods dao invocation (CRUD operations), this service 
 * was designed for using by  RestController, as well as, by the services and commands
 *
 * @package ttm-core-php
 * @namespace ttm\control
 * @abstract
 * @version 1.0
 */
interface CRUDHelper extends Service {
	/**
	 * @method get - find a registry on datasource by id of an entity
	 *
	 * @param $entity - name of model class for finding
	 * @param $id - id of registry 
	 * 
	 * @return correspondent model object of find result (whether there is) 
	 *
	 * @throws InvalidArgumentException - whether $entity be null
	 * @throws InvalidArgumentException - whether $id be null
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public function get($entity, $id):Model;

	/**
	 * @method getAll - find all registers on datasource by an entity, recomend just
	 * to small data collections
	 *
	 * @param $entity - name of model class for finding
	 * 
	 * @return correspondent model objects of find result (whether there are)
	 *
	 * @throws InvalidArgumentException - whether $entity be null
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public function getAll($entity):array;
	
	/**
	 * @method getBySimpleCriteria - find the registers on datasource by an entity, 
	 * creating a simple criteria for applying a WHERE CLAUSE just about the entity
	 * 
	 * @example SELECT u FROM Project\model\User u WHERE u.actived IS TRUE
	 * 
	 * for mounting this ORM query language dinamicaly the method uses the parameters:
	 * 
	 * "SELECT ".$entityQueryAlias." FROM ".$entity." ".$entityQueryAlias." WHERE ".$whereClause;
	 *
	 * Attetion: this method allows that layer view pass some simples causes to control
	 * layer, but cant be against the layers independence. A good pratice of this method
	 * is when the view layer object has the same properties names of the model class 
	 * properties names. Because in this way the WHERE CLAUSE on the view layer references 
	 * to a view layer object (DTO), how it has the same properties names is possible to 
	 * apply the criteria dinamicaly.
	 * 
	 * @see AbstractRestController for more understand about view and model objects 
	 * association 
	 *
	 * @param $entity - name of model class for quering
	 * @param $entityQueryAlias - alias for entity on query (in example would de letter 'u')
	 * @param $whereClause - expression to filter the registers on query, using the atrributes
	 * on object (always using the alias like example: 'u.actived IS TRUE')
	 *   
	 * @return correspondent model objects of query result (whether there are)
	 *
	 * @throws InvalidArgumentException - whether $entity be null
	 * @throws InvalidArgumentException - whether $entityQueryAlias be null
	 * @throws InvalidArgumentException - whether $whereClause be null
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public function getBySimpleCriteria($entity, $entityQueryAlias ,$whereClause):array;

	/**
	 * @method getResult - find the registers on datasource using a ORM query for getting
	 * model objects as return.
	 *
	 * @example SELECT i FROM Project\model\Invoice i WHERE i.taxAmount>?
	 *
	 *
	 * @param $ormQuery - an orm query for getting a collection of model objects
	 * @param $parameters - the parameters for put on '?' or ':bindValueName' 
	 * references in the query
	 * @param $dataSourceAlias - data source where query will be executed, 
	 * whether alias didnt give, will be returned the default data source.
	 *  
	 * @return correspondent model objects of query result (whether there are)
	 *
	 * @throws InvalidArgumentException - whether $ormQuery be null
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public function getResult($ormQuery, $parameters, $dataSourceAlias=null):array;
	
	/**
	 * @method getResultSet - find the registers on datasource using a SQL query for getting
	 * an array with data registers as return.
	 *
	 * @example SELECT * FROM invoices_table i WHERE i.inv_tax_amount > ?
	 *
	 *
	 * @param $sqlQuery - an sql query for getting an array of data registers
	 * @param $parameters - the parameters for put on '?'references in the query
	 * @param $dataSourceAlias - data source where query will be executed, 
	 * whether alias didnt give, will be returned the default data source.
	 * 
	 * @return an array with two dimensions ([row][column]) contains the result
	 * sql query  
	 *
	 * @throws InvalidArgumentException - whether $sqlQuery be null
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public function getResultSet($sqlQuery, $parameters, $dataSourceAlias=null):array;
	
	/**
	 * @method create - creates a model object and persistes it on mapped table 
	 * in entity associated data source, using the configured implementation of
	 * data parser to assembly the model object by view layer object 
	 *
	 * @param $entity - model class for creating
	 * @param $object - view layer object recivied (dto - data transport object)
	 *
	 * @return the model object created
	 *
	 * @throws InvalidArgumentException - whether $entity be null
	 * @throws InvalidArgumentException - whether $object be null
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public function create($entity, $object):Model;
	
	/**
	 * @method update - updates a model object and persistes the changes on mapped table
	 * in entity associated data source, using the configured implementation of
	 * data parser to change the model object attributes by view layer object
	 *
	 * @param $entity - model class for updating
	 * @param $object - view layer object recivied (dto - data transport object)
	 *
	 * @throws InvalidArgumentException - whether $entity be null
	 * @throws InvalidArgumentException - whether $object be null
	 * @throws InvalidArgumentException - whether id of $object be null or not setted
	 * @throws TTMException - whether model object for updating not found
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public function update($entity, $object);
	
	/**
	 * @method delete - remove a model object on mapped table in entity associated data source
	 *
	 * @param $entity - model class for deleting
	 * @param $id - id of the object for deleting
	 *
	 * @throws InvalidArgumentException - whether $entity be null
	 * @throws InvalidArgumentException - whether $id be null
	 * @throws TTMException - whether model object for deleting not found
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public function delete($entity, $id);
}