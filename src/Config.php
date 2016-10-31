<?php
namespace ttm;

use ttm\control\DataParser;

class Config {
	private static $config=null;
		
	public static function getDataParser():DataParser {
		$parserName = Config::getConfig()->dataParser;
		
		return new $parserName();
	}
	
	private static function getConfig() {
		if(Config::$config==null)
			Config::$config = json_decode(file_get_contents(__DIR__."/config.json"));
	
			return Config::$config;
	}
	
	
}