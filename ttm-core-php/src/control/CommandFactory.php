<?php
namespace ttm\control;

class CommandFactory {
	private static $map;
	
	protected function __construct(){
		static::$map = array();
	}
	
	public function getCommand($class):Command {
		if(is_null($class))
			throw new \Exception("Type command can't be null");
		
		if(!isset(static::$map[$class])) {
			$command = new $class;
		
			static::$map[$class] = $command;
		}
			
		return static::$map[$class];
	}
	
	public static function getInstance()
	{
		static $instance = null;
		if (null === $instance) {
			$instance = new static();
		}
	
		return $instance;
	}
	
	
	private function __clone(){}
	
	private function __wakeup(){}
}

?>
