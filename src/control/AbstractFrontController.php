<?php
namespace ttm\control;

use ttm\util\UtilDate;

abstract class AbstractFrontController extends Rest {
	private static $configFile = null;
	private static $controllers = array();
	private static $commands = array();

	private function getConfig() {
		if(AbstractFrontController::$configFile==null) {
			AbstractFrontController::$configFile=$this->getConfigInfo();
		}
		
		return AbstractFrontController::$configFile;
	}
	
	protected function getControllerImplementation($interfaceName) {
		if(isset(AbstractFrontController::$controllers[$interfaceName])) {
			return AbstractFrontController::$controllers[$interfaceName];
		}
	
		$config = $this->getConfig();
		foreach ($config->controllers as $value) {
			if(property_exists ( $value , $interfaceName)) {
				AbstractFrontController::$controllers[$interfaceName] = $value->$interfaceName;
				return $value->$interfaceName;
			}
		}
	
		return null;
	}
	
	protected function getCommandsImplementation($commandName) {
		if(isset(AbstractFrontController::$commands[$commandName])) {
			return AbstractFrontController::$commands[$commandName];
		}
	
		$config = $this->getConfig();
		foreach ($config->commands as $value) {
			if(property_exists ( $value , $commandName)) {
				AbstractFrontController::$commands[$commandName] = $value->$commandName;
				return $value->$commandName;
			}
		}
	
		return null;
	}
	
	public function processRequest()
	{
		$parameter1 = null;
		$parameter2 = null;
		
		$partsOfRequest = str_getcsv($_REQUEST['request'],"/");
		
		if(!is_null($partsOfRequest)) {
			if(isset($partsOfRequest[0])) {
				$parameter1 = $partsOfRequest[0]; 
			}

			if(isset($partsOfRequest[1])) {
				$parameter2 = $partsOfRequest[1]; 
			}
		}
		
		$return = null;
		if(strcasecmp("command", $parameter1)==0) {
			$return = $this->processCommand($parameter2);
		} else {
			$return = $this->processController($parameter1, $parameter2);
		}
		
		if(!is_null($return)) {
			$data = array();
			$data['reply']= $this->filterSendPropertiesReply($return);
				
			echo json_encode($data,false,JSON_UNESCAPED_UNICODE);
		}
	}
	
	private function filterSendPropertiesReply($data) {
		if(is_array($data) || is_subclass_of($data, \ArrayAccess::class)) {
			$arrayFO = array();
			foreach ($data as $item) {
				array_push($arrayFO, $this->getFilteredObjectSendPropertiesReply($item));
			}
			
			return $arrayFO; 
		} else {
			return $this->getFilteredObjectSendPropertiesReply($data);
		}
	}
	
	private function getFilteredObjectSendPropertiesReply($data) {
		if(is_null($data))
			return;
		
		$filteredObject = new \stdClass();
		
		$reflectionObject = new \ReflectionObject($data);
		
		foreach ($reflectionObject->getProperties() as $prop) {
			if(strpos($prop->getDocComment(), "@ttm-DtoAtribute")>-1) {
				$property = $prop->getName();
				
				$function = $this->doMethod($property,"get");
				if((int)method_exists($data,$function) > 0) {
					$reflectionMethod = new \ReflectionMethod($data, $function);
					
					$value = $reflectionMethod->invoke($data, null);
					
					if(is_a($value, \DateTime::class)) {
						$value = UtilDate::dateToString($value);
					}
				
					$filteredObject->$property = $value; 
				} else {
					$function = $this->doMethod($property,"is");
					if((int)method_exists($data,$function) > 0) {
						$reflectionMethod = new \ReflectionMethod($data, $function);
							
						$value = $reflectionMethod->invoke($data, null);
						$filteredObject->$property = $value;
					}
				}
			}
		}
		
		return $filteredObject;
	}
	
	private function doMethod($propertyName, $sufix):string {
		$firstLetter = substr($propertyName,0,1);
		$wordRest = substr($propertyName,1);
	
		return $sufix.strtoupper($firstLetter).$wordRest;
	}

	private function processController($controllerName, $actionName) {
		$controller = $this->locateController($controllerName);
		return $this->invoke($controller, $actionName);		
	}

	private function processCommand($commandName) {
		$command = $this->locateCommand($commandName);
		return $this->invoke($command, "execute", true);
	}
	
	private function invoke($object, $function,bool $isCommand=false) {
		if(is_null($object)) {
			$this->response('',404);
		} else {
			if(!is_null($function)) {
				if((int)method_exists($object,$function) > 0) {
					$input = json_decode(file_get_contents('php://input'),false,JSON_UNESCAPED_UNICODE);

					$args = array();
					$i=0;
					foreach ($input as $value) {
						if(is_array($value)) {
							$args[$i++] = $value[0];
						} else {
							$args[$i++] = $value;
						}
					}
					
					$reflectionMethod = new \ReflectionMethod($object, $function);
					if($isCommand) {
						return $reflectionMethod->invoke($object,$args);
					} else {
						return $reflectionMethod->invokeArgs($object, $args);
					}
				}
				else {
					$this->response('',404);
				}
			}
		}
	}
	
	
	public abstract function locateController($interfaceName);
	public abstract function locateCommand($interfaceName);
	public abstract function getConfigInfo();
	
}

?>
