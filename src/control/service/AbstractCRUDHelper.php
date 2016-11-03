<?php

namespace ttm\control\service;

use ttm\control\CRUDHelper;
use ttm\dao\DaoFactory;
use ttm\exception\DaoException;
use ttm\exception\TTMException;
use ttm\model\Model;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * AbstractCRUDHelper - implements of CRUD Helper interface offering common 
 * methods for concrete implementations
 *
 * @see interface CRUDHelper
 *
 * @package ttm-core-php
 * @namespace ttm\control\service
 * @abstract
 * @version 1.0
 */
abstract class AbstractCRUDHelper implements CRUDHelper {
	/**
	 * @property $daos - keeps in an array the dao implementations registred for any 
	 * data source configured
	 *
	 * @access private
	 * @static
	 * @since 1.0
	 */
	private $daos;
	
	/**
	 * @method __construct - the construtor method encampsulate the daos creations,
	 * keeping the configured and resgistred daos classes on project implementation
	 *
	 * @param array $daoConfig - has the options and configurations for creating the concrete
	 * classes of Dao
	 *
	 * @throws DaoException - whether the dao factory dont return any dao
	 *
	 * @access protected
	 * @magic
	 * @since 1.0
	 */
	protected function __construct(array $daoConfig) {
		$this->daos =  DaoFactory::getInstance($daoConfig);
		if(is_null($this->daos) || sizeof($this->daos)==0) {
			throw new DaoException("There aren't dao returned [CRUDHelper:".__LINE__."]");
		}
	}

	/**
	 * @method getDaoByEntity - seeks and returns an associated dao to the informated 
	 *  entity (model class), using your namespace (model folder) like an alias 
	 * for getting the registred dao
	 *
	 * @param $entity - the model class for seeking the correspondent dao (datasource)
	 * classes of Dao
	 * 
	 * @return responsible dao for treatment the entity
	 *
	 * @throws InvalidArgumentException - whether $entity be null
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected function getDaoByEntity($entity) { 
		if(is_null($entity)) {
			throw new \InvalidArgumentException("The entity name can't be null [CRUDHelper:".__LINE__."]");
		}

		//getting registred dao by model namespace 
		return $this->getDao(substr($entity,0,strripos($entity, "\\")));
	}

	/**
	 * @method getDao - seeks and returns the registred dao for a passed data source alias
	 *
	 * @param $dataSourceAlias - name for seeking a registred data source on project, 
	 * whether alias didnt give, will be returned the first registrated data source. 
	 * (the first data source is the default data source)
	 * 
	 * @return correspondent dao to informated alias 
	 *
	 * @throws TTMException - whether there isnt a registred dao to informated alis
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected function getDao($dataSourceAlias) {
		if(is_null($dataSourceAlias)) {  
			return array_values($this->daos)[0];
		}
		
		$dao = null;
				
		if(isset($this->daos[$dataSourceAlias])) {
			$dao =  $this->daos[$dataSourceAlias];
		} else {
			throw new TTMException("There isnt data soure associated to entity [CRUDHelper:".__LINE__."]");
		}
	
		return $dao;
	}
}