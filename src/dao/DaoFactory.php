<?php
namespace ttm\dao;

/**
 * Dao Factory - return a instance of a DAO corresponding to a 
 * implementation of the ttm\dao\Dao 
 *
 * @author flaviodev - Flávio de Souza - fdsdev@gmail.com
 * @version 1.0
 * @package ttm-core-php
 * @namespace ttm\dao
 */
class DaoFactory {
	/**
	 * @property has the dao implementation used for a configure architecture (project)
	 *
	 * @since 1.0
	 * @static
	 * @access private 
	 */
	private static $dao;
	
	/**
	 * @method Singleton that return de Dao implementation  
	 *
	 * @since 1.0
	 * @static
	 * @access public
	 * @param $daoImp - class of Dao implementation (\ttm\dao\Dao)
	 * @param array $config - has the options and configurations for create a concrete class of Dao
	 * @return Dao - a concrete implementation of Dao correnponding $daoImp
	 */
	public static function getInstance($daoImp, array $config):Dao {
		if(!isset(static::$dao) || is_null(static::$dao)) {
			static::$dao = static::create($daoImp,$config);
		}
	
		return static::$dao;
	}

	/**
	 * @method encapsulate the instance creation of the $daoImp 
	 *
	 * @since 1.0
	 * @static
	 * @access private
	 * @param $daoImp - class of Dao implementation (\ttm\dao\Dao)
	 * @param array $config - has the options and configurations for create a concrete class of Dao
	 * @return Dao - a concrete implementation of Dao correnponding $daoImp
	 */
	private static function create($daoImp, array $config):Dao {
		$dao = new $daoImp($config);
		
		return $dao;
	}
}