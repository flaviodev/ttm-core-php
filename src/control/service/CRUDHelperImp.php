<?php

namespace ttm\control\service;

use ttm\Config;
use ttm\control\CRUDHelper;
use ttm\exception\DaoException;
use ttm\exception\TTMException;
use ttm\model\Model;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * CRUDHelperImp - implements of CRUD Helper interface for encapsulating the 
 * treatment of the methods dao invocation (CRUD operations)
 *
 * @see AbstractCRUDHelper 
 * @see interface CRUDHelper
 *
 * @package ttm-core-php
 * @namespace ttm\control\service
 * @final
 * @version 1.0
 */
final class CRUDHelperImp extends AbstractCRUDHelper {
	/**
	 * @method __construct - the construtor method encampsulate the daos creations,
	 * keeping the configured and resgistred daos classes on project implementation
	 *
	 * @param array $daoConfig - has the options and configurations for creating the concrete
	 * classes of Dao
	 *
	 * @throws DaoException - whether the dao factory dont return any dao
	 *
	 * @access public
	 * @magic
	 * @since 1.0
	 */
	public function __construct(array $daoConfig) {
		parent::__construct($daoConfig);
	}

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
	 * @since 1.0
	 */
	public function get($entity, $id):Model{
		$this->doEntityValidation($entity);
		$this->doIdValidation($id);
		
		$dataSource = $this->getDaoByEntity($entity);
		
		return $dataSource->find($entity,$id);
	}

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
	 * @since 1.0
	 */
	public function getAll($entity):array{
		$this->doEntityValidation($entity);
		
		$dataSource = $this->getDaoByEntity($entity);
				
		return $dataSource->findAll($entity);
	}

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
	 * This mounted query is delegated to method getResult  
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
	 * @since 1.0
	 */
	public function getBySimpleCriteria($entity, $entityQueryAlias ,$whereClause):array {
		$this->doEntityValidation($entity);
		
		if(is_null($entityQueryAlias)){
			throw new \InvalidArgumentException("The entity query alias can't be null [CRUDHelper:".__LINE__."]");
		}
		
		if(is_null($whereClause)){
			throw new \InvalidArgumentException("The where clause can't be null [CRUDHelper:".__LINE__."]");
		}
		
		$entityQuery = "SELECT ".$entityQueryAlias." FROM ".$entity." ".$entityQueryAlias." WHERE ".$whereClause;
		
		return $this->getResult($entityQuery, null, $entity);
	}

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
	 * @since 1.0
	 */
	public function getResult($ormQuery, $parameters, $dataSourceAlias=null):array {
		if(is_null($ormQuery)){
			throw new \InvalidArgumentException("The orm query can't be null [CRUDHelper:".__LINE__."]");
		}
		
		$dataSource = $this->getDao($dataSourceAlias);
	
		return $dataSource->getResult($ormQuery, $parameters);
	}

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
	 * @since 1.0
	 */
	public function getResultSet($sqlQuery, $parameters, $dataSourceAlias=null):array {
		if(is_null($sqlQuery)){
			throw new \InvalidArgumentException("The sql query can't be null [CRUDHelper:".__LINE__."]");
		}
		
		$dataSource = $this->getDao($dataSourceAlias);
	
		return $dataSource->getResultSet($sqlQuery, $parameters);
	}
	
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
	 * @since 1.0
	 */
	public function create($entity, $object):Model {
		$this->doEntityValidation($entity);
		$this->doObjectValidation($object);
		
		$model = new $entity();
		Config::getDataParser()->parseObjectToModel($object,$model);

		//setting 0 to id of the new model object
		$model->setId(0);
		$dataSource = $this->getDaoByEntity($entity);
		
		return $dataSource->create($model);
	}
	
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
	 * @since 1.0
	 */
	public function update($entity, $object){
		$this->doEntityValidation($entity);
		$this->doObjectValidation($object);

		if(!isset($object->id)){
			throw new \InvalidArgumentException("The object id should be setted [CRUDHelper:".__LINE__."]");
		}
		
		$dataSource = $this->getDaoByEntity($entity);
		
		$model = $dataSource->find($entity,$object->id);
		
		$this->doReturnedObjectValidation($model,$entity,$object->id,"updating");
				
		Config::getDataParser()->parseObjectToModel($object,$model);
		$dataSource->update($model);
	}

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
	 * @since 1.0
	 */
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
	
	/**
	 * @method doEntityValidation - centered validation for checking the entity on crud operations
	 *
	 * @param $entity - model class for validating
	 *
	 * @throws InvalidArgumentException - whether $entity be null
	 *
	 * @access private
	 * @since 1.0
	 */
	private function doEntityValidation($entity) {
		if(is_null($entity)){
			throw new \InvalidArgumentException("The entity can't be null [CRUDHelper:".__LINE__."]");
		}
	}

	/**
	 * @method doReturnedObjectValidation - centered validation for checking the retorned
	 * object on crud operations, when this object is required by an operation (ex: update 
	 * and delete)
	 *
	 * @param $returnedObject - returned object for validating
	 * @param $entity - model class for mountig of the validation message
     * @param $id - id of required object for mountig of the validation message
	 * @param $operation - executed operation for mountig of the validation message
	 *
	 * @throws InvalidArgumentException - whether $returnedObject be null
	 *
	 * @access private
	 * @since 1.0
	 */
	private function doReturnedObjectValidation($returnedObject, $entity, $id, $operation) {
		if(is_null($returnedObject)){
			throw new TTMException("Object ".$entity.":(".$id.") not found for ".$operation." [CRUDHelper:".__LINE__."]");
		}
	}

	/**
	 * @method doIdValidation - centered validation for checking the id of required 
	 * model object
	 *
	 * @param $id - id of required object (primary key on associated table)
	 *
	 * @throws InvalidArgumentException - whether $id be null
	 *
	 * @access private
	 * @since 1.0
	 */
	private function doIdValidation($id) {
		if(is_null($id)){
			throw new \InvalidArgumentException("The id can't be null [CRUDHelper:".__LINE__."]");
		}
	}

	/**
	 * @method doObjectValidation - centered validation for checking the view layer object
	 *
	 * @param $object - view layer object (dto) sent for any operation
	 *
	 * @throws InvalidArgumentException - whether $object be null
	 *
	 * @access private
	 * @since 1.0
	 */
	private function doObjectValidation($object) {
		if(is_null($object)){
			throw new \InvalidArgumentException("The object can't be null [CRUDHelper:".__LINE__."]");
		}
	}

}