<?php
namespace ttm\dao;

use ttm\exception\TTMException;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 * 
 * DaoFactory - return a instance of a DAO corresponding to a 
 * implementation of the ttm\dao\Dao and configurated data sources 
 * 
 * This factory allows the creation of more than one data source configuration, 
 * is possible to map classes for two or more differents data base on same time
 *
 * @package ttm-core-php
 * @namespace ttm\dao
 * 
 * @final
 * @version 1.0
 */
final class DaoFactory {
	/**
	 * @property $daos - it's the attribute associated with the flyweight pattern that
	 * store the concrete daos associated with your respective interfaces as they are called.
	 *
	 * @access private
	 * @static 
	 * @since 1.0
	 */
	private static $daos = array();
	
	/**
	 * @method getInstance - this method is responsible create and registry the dao 
	 * implementations based on data sources configurations. The registry is based on
	 * a alias setted for data source (ex: organization_db), as well as, using the
	 * namesspace of models class, allowing seek the datasource by entity name, ex:
	 * 
	 * Data base 1: organization_A_db (Project\model\Costumer mapped on this database)
	 *   - registry datasource by alias: org_A
	 *   - registry datasource by model namespace: Project\model
	 *
	 * on the same project:
	 * 
	 * Data base 2: organization_B_db (Project\model_B\Partner mapped on this database)
	 *   - registry datasource by alias: org_B
	 *   - registry datasource by model namespace: Project\model_B
	 *
	 * on this way is possible found the dao using an alias, as well as, the entity name
	 *
	 * @param array $daoConfig - has the options and configurations for creating the concrete
	 * classes of Dao
	 * @return an array of concrete implementations of Dao correnponding passed configuration
	 * 
	 * @throws InvalidArgumentException - whether $daoConfig is null
	 * 
	 * @access public
	 * @static
	 * @since 1.0
	 */
	 public static function getInstance(array $daoConfig) {
		if(is_null($daoConfig)) {
			throw new \InvalidArgumentException("The array of dao config can't be null [DaoFactory:".__LINE__."]");
		}
		
		foreach ($daoConfig as $alias=>$config) {
			// checking whether service already called
			if(!isset(DaoFactory::$daos[$alias])) {
				$dao = DaoFactory::create($config['dao'],$config);
				
				// registring datasource by alias
				DaoFactory::$daos[$alias]=$dao; 
				
				// registring datasource by namespace model (for seeking by entities)
				DaoFactory::$daos[$config['namespaceModel']]=$dao;
			}
		}
		
		return DaoFactory::$daos;
	}

	/**
	 * @method create - encapsulate the instance creation of the $daoImp (concrete class
	 * of Dao) 
	 *
	 * @param $daoImp - class of Dao implementation (\ttm\dao\Dao)
	 * @param array $config - has the options and configurations for create a concrete class of Dao
	 * @return Dao - a concrete implementation of Dao correnponding $daoImp
	 * 
	 * @exception TTMException - when can't create dao
	 * 
	 * @access private
	 * @static
	 * @since 1.0
	 */
	private static function create($daoImp, array $config):Dao {
		try {
			return new $daoImp($config);
		} catch (\Error $err) {
			throw new TTMException("Error on create dao: ".$err);
		}
	}
	
	/**
	 * @method __construct - prevents create a new instance without using static method
	 *
	 * @access protect
	 * @magic
	 * @since 1.0
	 */
	protected function __construct() {}
	
	
	/**
	 * @method __clone - prevents cloning on this instance  
	 *
 	 * @access private
 	 * @magic
	 * @since 1.0
	 */
	private function __clone() {}
	
	/**
	 * @method __wakeup - prevents unserialize on this instance
	 *
	 * @access private
	 * @magic
	 * @since 1.0
	 */
	private function __wakeup() {}
}