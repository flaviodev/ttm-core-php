<?php
namespace ttm;

use ttm\control\DataParser;
use ttm\control\CRUDHelper;
use ttm\exception\TTMException;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * Config - Utilitary class to access the core configuration
 *
 * @package ttm-core-php
 * @namespace ttm
 * @abstract
 * @version 1.0
 */
abstract class Config {
	
	/**
	 * @property $config - keeps the data configurations on a single instance
	 *
	 * @access private
	 * @static
	 * @since 1.0
	 */
	private static $config=null;

	/**
	 * @property $parser - keeps the single instance of the DataParser implementation
	 *
	 * @access private
	 * @static
	 * @since 1.0
	 */
	private static $parser=null;
	
	/**
	 * @property $CRUDHelper - keeps the single instance of the CRUDHelper implementation
	 *
	 * @access private
	 * @static
	 * @since 1.0
	 */
	private static $CRUDHelper=null;
	
	
	/**
	 * @method getDataParser - returns the concrete class configured that implements 
	 * DataParser interface
	 *
	 * @return the data parser configurated
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 */
	public static function getDataParser():DataParser {
		if(is_null(Config::$parser)) {
			$parserName = Config::getConfig()->dataParser;

			Config::$parser = new $parserName();
		}
		
		return Config::$parser;
	}

	/**
	 * @method getCRUDHelper - creates and returns the instance of concrete class 
	 * configured that implements the CRUDHelper service interface
	 *
	 * @param array $daoConfig - dao configurations for creating CRUDHelper
	 * @param bool $force - determines whether should be created again, may used
	 * for changing the dao configuration  
	 *
	 * @return the instance of CRUDHelper 
	 *
	 * @throws InvalidArgumentException - whether $force is true and $daoConfig is null
	 * @throws InvalidArgumentException - whether $daoConfig is null and there isent 
	 * CRUDHelper already created
     * @throws TTMException - Whether CRUDHelperImp on configuration file dont implements 
     * CRUDHelper interface
	 * 
	 * @access public
	 * @static
	 * @since 1.0
	 */
	public static function getCRUDHelper(array $daoConfig=null, bool $force=false):CRUDHelper {
		if($force && is_null($daoConfig)) {
			throw new \InvalidArgumentException("The array of dao config can't be null for forcing new instance of CRUDHelper [Config:".__LINE__."]");
		}

		if(is_null(Config::$CRUDHelper) && is_null($daoConfig)) {
			throw new \InvalidArgumentException("There isent instance of CRUDHelper, the array of dao config can't be null [Config:".__LINE__."]");
		}

		if($force || is_null(Config::$CRUDHelper)) {
			$CRUDHelperNameImpl = Config::getConfig()->CRUDHelper;
			
			$CRUDHelperImp = new $CRUDHelperNameImpl($daoConfig);
			
			
			if(!($CRUDHelperImp instanceof CRUDHelper)) {
				throw new TTMException("The CRUD Helper implementation dont implements the CRUDHelper interface  [Config:".__LINE__."]");
			}

			Config::$CRUDHelper = $CRUDHelperImp; 
		} 

		return Config::$CRUDHelper;
	}
	
	/**
	 * @method getConfig - loads and keeps the core configuration on file config.json
	 *
	 * @return core configuration
	 *
	 * @access private
	 * @static
	 * @since 1.0
	 */
	private static function getConfig() {
		if(Config::$config==null) {
			Config::$config = json_decode(file_get_contents(__DIR__."/config.json.php"));
		}
	
		return Config::$config;
	}

	/**
	 * @method __clone - prevents cloning on this instance
	 *
	 * @access private
	 * @since 1.0
	 */
	private function __clone() {}
	
	/**
	 * @method __wakeup - prevents unserialize on this instance
	 *
	 * @access private
	 * @since 1.0
	 */
	private function __wakeup() {}
}