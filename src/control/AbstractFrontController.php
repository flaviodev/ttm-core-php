<?php
namespace ttm\control;

use ttm\util\UtilDate;
use ttm\util\Util;

/**
 * AbstractFrontController - Reuseble component that encapsulates treatment requisitions
 * of web services. Using the pattern of friendly URL (ex:/contextaplication/service/method) 
 * where the parametters are send by POST http method. 
 *
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 * 
 * @package ttm-core-php
 * @namespace ttm\control
 * @abstract
 * @version 1.0
 */
abstract class AbstractFrontController {
	
	/**
	 * @property $configFile - has the configuration of dependences of services/commands 
	 * interfaces and yours concrete classes.
	 * 
	 * @static @access private 
	 * @since 1.0 
	 */
	private static $configFile = null;

	/**
	 * @property $controllers - it's a flyweight pattern that store the concrete services 
	 * (facades) associated with your respective interfaces, as it is in the configuration file.
	 * 
	 * @static @access private
	 * @since 1.0 
	 */
	private static $controllers = array();

	/**
	 * @property $commands - it's a flyweight pattern that store the concrete commands
	 * associated with your respective names, as it is in the configuration file.
	 *
	 * @static @access private
	 * @since 1.0
	 */
	private static $commands = array();

	
	/**
	 * @method getConfig - encapsulates the invocation of the abstract method that returns 
	 * the properties of configuration file on concrete implementation of the front controller 
	 *
	 * @return object that contains the configuration file properties  
	 * 
	 * @access public
	 * @since 1.0 
	 */
	private function getConfig() {
		if(AbstractFrontController::$configFile==null) {
			AbstractFrontController::$configFile=$this->getConfigInfo();
		}
		
		return AbstractFrontController::$configFile;
	}

	/**
	 * @method getControllerImplementation - this method is responsible for seeking on 
	 * controllers configuration what is the concrete service to the given interface,
	 * keeping the services already requested. 
	 *
	 * @param $interfaceName - name of service interface for seeking the concrete service configurated
	 * @access protected
	 * @since 1.0
	 */
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

	/**
	 * @method getCommandsImplementation - this method is responsible for seeking on
	 * commands configuration what is the concrete command to the given command name,
	 * keeping the commands already requested. In this case, there is just one common interface
	 * for all commands, so the seeking is for name and not for interface.
	 *
	 * @param $commandName - name of command for seeking the concrete command configurated
	 * @access protected
	 * @since 1.0
	 */
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
	
	/**
	 * @method processRequest - when the client makes a server request, the .htaccess should be
	 * redirect the request to a concrete implementation of this class. The first part of the
	 * URL is project that implments the front controller, the secont part contains the sevice 
	 * or command required. This second part is divide into two other: class and method that will
	 * be instantiated and invoked. This method separates the parts of requested URL, creates
	 * the class and invokes the method.
	 *
	 * @access public
	 * @since 1.0
	 */
	public function processRequest()
	{
		//name of a service interface or command(fixed text)
		$parameter1 = null;
		
		//name of the service method or name of concrete command  
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
		// the first parammeter defines the type of process
		if(strcasecmp("command", $parameter1)===0) {
			$return = $this->processCommand($parameter2);
		} else {
			$return = $this->processController($parameter1, $parameter2);
		}
		
		// TODO create an uncoupled component for manipulating data of request/reply
		if(!is_null($return)) {
			$data = array();
			$data['reply']= $this->filterSendPropertiesReply($return);
				
			echo json_encode($data,false,JSON_UNESCAPED_UNICODE);
		}
	}

	/**
	 * @method processController - encapsulates the locale of controller  
	 *
	 * @access public
	 * @since 1.0
	 */
	private function processController($controllerName, $actionName) {
		$controller = $this->locateController($controllerName);
		return $this->invoke($controller, $actionName);		
	}

	/**
	 * @method constructor of class
	 *
	 * @param array $options - has the options and configurations for
	 * @access public
	 * @since 1.0
	 */
	private function processCommand($commandName) {
		$command = $this->locateCommand($commandName);
		return $this->invoke($command, "execute", true);
	}

	/**
	 * @method constructor of class
	 *
	 * @param array $options - has the options and configurations for
	 * @access public
	 * @since 1.0
	 */
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

	/**
	 * @method getFilteredObjectSendPropertiesReply - encapsulate a parsing mechanism for conversting
	 * a model class to a dto class, obtaing the attribuites of model class using refletion tools.
	 * The filter process is to separate what the attributes that will be sent on reply, dont sending
	 * for examples attribuites used on collection mapping (orm). This filter uses a anotation in
	 * attributes (@ttm-DtoAttribute) that should be sent.
	 *
	 * @param model object for 'filtering'and convertion
	 * @return new object (dto) with just the marked attributes
	 *
	 * @access private
	 * @since 1.0
	 */
	private function getFilteredObjectSendPropertiesReply($data) {
		if(is_null($data))
			return;
	
			$filteredObject = new \stdClass();
	
			$reflectionObject = new \ReflectionObject($data);
	
			foreach ($reflectionObject->getProperties() as $prop) {
				if(strpos($prop->getDocComment(), "@ttm-DtoAttribute")>-1) {
					$property = $prop->getName();
	
					$function = Util::doMethodName($property,"get");
					if((int)method_exists($data,$function) > 0) {
						$reflectionMethod = new \ReflectionMethod($data, $function);
							
						$value = $reflectionMethod->invoke($data, null);
							
						if(is_a($value, \DateTime::class)) {
							$value = UtilDate::dateToString($value);
						}
	
						$filteredObject->$property = $value;
					} else {
						$function = Util::doMethodName($property,"is");
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
	
	/**
	 * @method filterSendPropertiesReply - method verifies if the parameter is a model object or
	 * an array of objects and delegates to method: getFilteredObjectSendPropertiesReply
	 *
	 * @param model object for 'filtering'and convertion
	 * @return new object (dto) with just the marked attributes
	 *
	 * @access private
	 * @since 1.0
	 */
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
	
	
	/**
	 * @method constructor of class
	 *
	 * @param array $options - has the options and configurations for
	 * @access public
	 * @since 1.0
	 */
	public abstract function locateController($interfaceName);


	/**
	 * @method constructor of class
	 *
	 * @param array $options - has the options and configurations for
	 * @access public
	 * @since 1.0
	 */
	public abstract function locateCommand($interfaceName);

	/**
	 * @method constructor of class
	 *
	 * @param array $options - has the options and configurations for
	 * @access public
	 * @since 1.0
	 */
	public abstract function getConfigInfo();
	
}

