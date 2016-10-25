<?php
namespace ttm\dao;

class DaoFactory {
	private static $dao;
	
	public static function getInstance($daoImp, array $config):Dao {
		if(!isset(static::$dao) || is_null(static::$dao)) {
			static::$dao = static::create($daoImp,$config);
		}
	
		return static::$dao;
	}

	private static function create($daoImp, array $config):Dao {
		$dao = new $daoImp($config);
		
		return $dao;
	}
}