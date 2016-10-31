<?php
namespace ttm\control;

use ttm\Config;
use ttm\exception\TTMException;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 * 
 * Abstract Class AbstractFrontController - Reuseble component that encapsulates requisitions treatment
 * of web services. Using the patterns of friendly URL: 
 * 
 * /contextaplication/serviceInterfaceAlias/method
 * and 
 * /contextaplication/command/commandAlias 
 * 
 * where the parametters are send by POST http method. 
 *
 * Simple tree of events sequence:
 * leve - method
 * 1- processRequest
 *    2- processCommand
 *       3- getCommand
 *          4- locateCommand
 *       3- invoke
 *          4- parseInputData
 *          4- parseOutputData 
 *    2- processService
 *       3- getService
 *          4- solveServiceInterfaceAlias
 *          4- locateService
 *       3- invoke
 *          4- parseInputData
 *          4- parseOutputData 
 * 
 * @package ttm-core-php
 * @namespace ttm\control
 * @abstract
 * @version 1.0
 */
abstract class AbstractFrontController extends Rest {

	/**
	 * @property $services - it's the attribute associated with the flyweight pattern that 
	 * store the concrete services (facades) associated with your respective interfaces as 
	 * they are called.
	 * 
	 * @static @access private
	 * @since 1.0 
	 */
	private static $services = array();

	/**
	 * @property $commands - it's the attribute associated with the flyweight pattern that 
	 * store the commands associated with your respective alias as they are called.
	 *
	 * @static @access private
	 * @since 1.0
	 */
	private static $commands = array();

	/**
	 * @method getService - this method is responsible for seeking on 
	 * stored called services a service associated with the passed interface name. 
	 * Whether the service not found, the method locates the service and stores it. 
	 * Validating also whether if the service implements the associated service interface. 
	 *
	 * @param $serviceInterfaceName - name of service interface for seeking/locating 
	 * @return object (instance) of required service 
	 * 
	 * @throws InvalidArgumentException - whether $serviceInterfaceName is null
	 * @throws TTMException - whether the returned service dont implements the associated interface
	 *   
	 * @access protected
	 * @since 1.0
	 */
	protected function getService($serviceInterfaceName) {
		if(is_null($serviceInterfaceName)) {
			throw new \InvalidArgumentException("The service interface name can't be null");
		}
		
		// checking whether service already called
		if(isset(AbstractFrontController::$services[$serviceInterfaceName])) {
			return static::$services[$serviceInterfaceName];
		}

		// locating service
		$service = $this->locateService($serviceInterfaceName);
		if(!is_null($service)) {
			
			//validating whether service implements the associated interface
			if(!is_subclass_of($service, $serviceInterfaceName)) {
				throw new TTMException("The service dont implements the associated interface");
			} 
			
			// keeping loaded service
			AbstractFrontController::$services[$serviceInterfaceName] = $service;
		}
	
		return $service;
	}

	/**
	 * @method getCommand - this method is responsible for seeking on 
	 * stored called commands a command associated with the passed command alias. 
	 * Wheter the command not found, the method locates the command and stores it. Validating 
	 * also whether the command implements \ttm\control\Command. 
	 * 
	 * @param $commandAlias - alias command for seeking/locating
	 * @return object (instance) of required command
	 *
	 * @throws InvalidArgumentException - whether $commandAlias is null
	 * @throws TTMException - whether the returned command dont implements the \ttm\control\Command
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected function getCommand($commandAlias) {
		if(is_null($commandAlias)) {
			throw new \InvalidArgumentException("The command alias can't be null");
		}
		
		//checking whether command already called
		if(isset(AbstractFrontController::$commands[$commandAlias])) {
			return AbstractFrontController::$commands[$commandAlias];
		}
	
		//locating command
		$command = $this->locateCommand($commandAlias);
		if(!is_null($command)) {
			//checking whether command implements the Command interface
			if(!is_subclass_of($command, Command::class)) {
				throw new TTMException("The command dont implements the \\ttm\\control\\Command");
			}
				
			// keeping command
			AbstractFrontController::$commands[$commandAlias] = $command;
		}
	
		return $command;
	}
	
	/**
	 * @method processRequest - when the client makes a server request, the .htaccess should be
	 * redirect the request to a concrete implementation of this class. The first part of the
	 * URL is the project that implements the front controller, the second part contains the 
	 * sevice or command required. This second part is divide into two other: class and method 
	 * that will be instantiated and invoked. So this method separates the parts of requested 
	 * URL, creates the object instance and invokes the method.
	 * @return - echo return of requested service/command  
	 *
	 * @access public
	 * @since 1.0
	 */
	public function processRequest() {
		//name of a service interface or 'command'(fixed text)
		$parameter1 = null;
		
		//name of the service method or alias of command  
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
		// the first parameter defines the type of process
		if(strcasecmp("command", $parameter1)===0) {
			$return = $this->processCommand($parameter2);
		} else {
			$return = $this->processService($parameter1, $parameter2);
		}
		
		if(!is_null($return)) {
			// parsing out
			echo Config::getDataParser()->parseOutputData($return);
		}
	}

	/**
	 * @method processService - encapsulates the location and invocation of a service request    
	 *
	 * @param $serviceInterfaceAlias - alias of the service interface
	 * @param $methodName - name of the method for invoking 
	 * @return reply of the service method invocation
	 *
	 * @throws InvalidArgumentException - whether $serviceInterfaceAlias is null
	 * @throws InvalidArgumentException - whether $methodName is null
	 * 
	 * @access private
	 * @since 1.0
	 */
	private function processService($serviceInterfaceAlias, $methodName) {
		if(is_null($serviceInterfaceAlias)) {
			throw new \InvalidArgumentException("The service interface alias can't be null");
		}

		if(is_null($methodName)) {
			throw new \InvalidArgumentException("The method name can't be null");
		}
		
		// solving alias before getting the service 
		$serviceInterfaceName = $this->solveServiceInterfaceAlias($serviceInterfaceAlias);
		$service = $this->getService($serviceInterfaceName);
		return $this->invoke($service, $methodName);		
	}

	/**
	 * @method processCommand - encapsulates the location and invocation of a command request.
	 * The command should implements the \ttm\controlCommand interface.
	 *
	 * @param $commandAlias - alias of the command
	 * @return reply of the command invocation
	 *
	 * @throws InvalidArgumentException - whether $commandAlias is null
	 *
	 * @access private
	 * @since 1.0
	 */
	private function processCommand($commandAlias) {
		if(is_null($commandAlias)) {
			throw new \InvalidArgumentException("The command alias can't be null");
		}
	
		$command = $this->getCommand($commandAlias);
		return $this->invoke($command, "execute", true);
	}
	
	/**
	 * @method invoke - this method is responsible for invoking the services and  commands, 
	 * encapsulating the construction of the parameters using the given post arguments 
	 *
	 * @param $object - its the instance of the service/command where the method will be invoked
	 * @param $methodName - name of method will be invoked (using reflection mechamism)
	 * @param bool $isCommand - flag that indicates whether object is a command (default false)
	 * @return reply of the method invocation
	 * 
	 * @access private
	 * @since 1.0
	 */
	private function invoke($object, $methodName, bool $isCommand=false) {
		if(is_null($object)) {
			$this->response('Requested service/command not found',404);
		} else {
			if(!is_null($methodName)) {
				if((int)method_exists($object,$methodName) > 0) {
					//getting input parameters
					$input = Config::getDataParser()->parseInputData(file_get_contents('php://input'));

					// creating array of arguements for invonking service
					$args = array();

					if(!is_null($input)) {
						// creating array of arguements for invonking service
						$i=0;
						foreach ($input as $value) {
							// whether parameter arrived encapsulated on secong level of the input array
							if(is_array($value)) {
								$args[$i++] = $value[0];
							} else {
								$args[$i++] = $value;
							}
						}
					}
					
					// invoking method on service/command
					$reflectionMethod = new \ReflectionMethod($object, $methodName);
					if($isCommand) {
						// on command sets an array of arguments
						return $reflectionMethod->invoke($object,$args);
					} else {
						// on service the sequence of input arguments should be the 
						// same of the method parameters (method signature)
						return $reflectionMethod->invokeArgs($object, $args);
					}
				} else {
					$this->response('Resource of service/command not found',404);
				}
			}
		}
	}
	
	/**
	 * @method locateService - determines the creation of a mechanism for seeking the services
	 * on project that to implement the front controller
	 *
	 * @example Service interface: \Project\control\services\ServiceRegister 
	 *          Service implementation: \Project\control\services\ServiceRegisterImp   
	 *
	 * @param $serviceInterfaceName - service interface name for locating
	 * @return the instance of required service 
	 *
	 * @throws InvalidArgumentException - The service interface name can't be null
	 * @throws InvalidArgumentException - No service registered for this service interface
	 * @throws TTMException - Error on instance of the service
	 * 
	 * @access public 
	 * @abstract
	 * @since 1.0
	 */
	public abstract function locateService($serviceInterfaceName);

	/**
	 * @method solveServiceInterfaceAlias - determines the creation of a method for solving the 
	 * service interface alias returning the name of service interface, bucause the locate 
	 * service method needs the service interface for locating the associated concrete service.
	 * 
	 * @example URL with service alias: serviceregister (http://.../project/serviceregister/getcustumer)
	 *          Class of the Service interface:   \Project\control\services\ServiceRegister
	 *
	 * @param $serviceInterfaceAlias - service interface alias for solving
	 * @return the service interface name
	 *
	 * @throws InvalidArgumentException - The service interface alias can't be null
	 *
	 * @access public 
	 * @abstract
	 * @since 1.0
	 */
	public abstract function solveServiceInterfaceAlias($serviceInterfaceAlias);
	

	/**
	 * @method locateCommand - determines the creation a mechanism for seek the command
	 * on project that to implement the front controller
	 *
	 * @example Command alias: commandCostumerReport (http://.../project/command/commandCostumerReport)
	 *          Command implementation: \Project\control\commands\CommandCostumerReport   
	 *
	 * @param $commandAlias - command alias for locating
	 * @return the instance of required command
	 *
	 * @throws InvalidArgumentException - The command alias can't be null
	 * @throws InvalidArgumentException - No command registered for this command alias
	 * @throws TTMException - Error on instance of the command
	 *
	 * @access public 
	 * @abstract
	 * @since 1.0
	 */
	public abstract function locateCommand($commandAlias);

}

