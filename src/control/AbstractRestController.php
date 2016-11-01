<?php
namespace ttm\control;

use ttm\Config;
use ttm\exception\TTMException;
use ttm\model\Model;
use ttm\exception\RestExcpetion;

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
abstract class AbstractRestController extends Rest {
	/**
	 * @property $commands - it's the attribute associated with the flyweight pattern that 
	 * store the commands associated with your respective alias as they are called.
	 *
	 * @static @access private
	 * @since 1.0
	 */
	private static $commands = array();

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
	 * @property $services - it's the attribute associated with the flyweight pattern that
	 * store the concrete services (facades) associated with your respective interfaces as
	 * they are called.
	 *
	 * @static @access private
	 * @since 1.0
	 */
	private static $models = array();

	// override -> dont invoke father;
	public function __construct() {
		
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
		if(isset(AbstractRestController::$commands[$commandAlias])) {
			return AbstractRestController::$commands[$commandAlias];
		}
	
		//locating command
		$command = $this->locateCommand($commandAlias);
		if(!is_null($command)) {
			//checking whether command implements the Command interface
			if(!is_subclass_of($command, Command::class)) {
				throw new TTMException("The command dont implements the \\ttm\\control\\Command");
			}
	
			// keeping command
			AbstractRestController::$commands[$commandAlias] = $command;
		}
	
		return $command;
	}

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
		if(isset(AbstractRestController::$services[$serviceInterfaceName])) {
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
			AbstractRestController::$services[$serviceInterfaceName] = $service;
		}
	
		return $service;
	}
	
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
	protected function getModel($modelAlias) {
		if(is_null($modelAlias)) {
			throw new \InvalidArgumentException("The model alias name can't be null");
		}
	
		// checking whether service already called
		if(isset(AbstractRestController::$models[$modelAlias])) {
			return static::$models[$modelAlias];
		}
	
		// locating service
		$model = $this->locateModel($modelAlias);
		if(!is_null($model)) {
				
			//checking whether command implements the Command interface
			if(!is_subclass_of($model, Model::class)) {
				throw new TTMException("The command dont implements the \\ttm\\control\\Command");
			}
				
			// keeping loaded service
			AbstractRestController::$models[$modelAlias] = $model;
		}
	
		return $model;
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
		$resource = null;
		
		//name of the service method or alias of command  
		$resourceComplement = null;
		
		$partsOfRequest = array_filter(str_getcsv($_REQUEST['request'],"/"));
		
		$resource = null;$
		$resourceComplement=null;
		$resourceArgs = null;
		
		if(!is_null($partsOfRequest) && sizeof($partsOfRequest)>=2) {
			$resource = $partsOfRequest[0]; 
			$resourceComplement = $partsOfRequest[1];
			
			if(sizeof($partsOfRequest)>2) {
				$resourceArgs = array_slice($partsOfRequest, 2);
			}
		}
		
		// the first parameter defines the type of process
		if($resource == "command") {
			$this->processCommand($resourceComplement,$resourceArgs);
		} else if($resource == "service") {
			$this->processService($resource, $resourceComplement,$resourceArgs);
		} else {
			$this->processRestService($resource, $resourceComplement);
		}
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
	private function processCommand($commandAlias,$resourceArgs) {
		$parser = Config::getDataParser();
		try {
			
			if(is_null($commandAlias)) {
				throw new RestExcpetion("The command alias can't be null",500);
			}
			
			$requestMethod = $this->get_request_method();
			if($requestMethod!="GET" && $requestMethod!="POST") {
				throw new RestExcpetion("HTTP method should be GET or POST",405);
			}
		
			$args = array();
			if(!is_null($resourceArgs)) {
				array_push($args, $resourceArgs);
			}
				
			$inputArgs = $this->getInputPost();
			if(!is_null($inputArgs)) {
				array_push($args, $inputArgs);
			}

			$this->cleanInputs($args);
			$command = $this->getCommand($commandAlias);
			$return = $this->invoke($command, "execute", $args, true);
						
			$this->response($parser->parseOutputData($return),200);
		} catch (RestExcpetion $re) {
			$this->response($parser->parseOutputData($re->getMessage()),$re->getHttpStatus());
		} catch (\Exception $e) {
			$this->response($parser->parseOutputData($e->getMessage()),500);
		} catch (\Error $err) {
			$this->response($parser->parseOutputData($err->getMessage()),500);
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
	private function processService($serviceInterfaceAlias, $methodName, $resourceArgs) {
		$parser = Config::getDataParser();
		
		if(is_null($serviceInterfaceAlias)) {
			throw new RestExcpetion("The service interface alias can't be null",500);
		}

		if(is_null($methodName)) {
			throw new RestExcpetion("The service method name can't be null",500);
		}

		$requestMethod = $this->get_request_method();
		if($requestMethod!="GET" && $requestMethod!="POST") { 
			throw new RestExcpetion("HTTP method should be GET or POST",405);
		}
		
		try {
			$args = array();
			
			if(!is_null($resourceArgs)) {
				array_push($args, $resourceArgs);
			}
			
			$inputArgs = $this->getInputPost();
			if(!is_null($inputArgs)) {
				array_push($args, $inputArgs);
			}
			
			$this->cleanInputs($args);
			// solving alias before getting the service
			$serviceInterfaceName = $this->solveServiceInterfaceAlias($serviceInterfaceAlias);
			$service = $this->getService($serviceInterfaceName);
			$return = $this->invoke($service, $methodName, $args);
				
			$this->response($parser->parseOutputData($return),200);
		} catch (RestExcpetion $re) {
			$this->response($parser->parseOutputData($re->getMessage()),$re->getHttpStatus());
		} catch (\Exception $e) {
			$this->response($parser->parseOutputData($e->getMessage()),500);
		} catch (\Error $err) {
			$this->response($parser->parseOutputData($err->getMessage()),500);
		}
	}
	
	
	/**
	 * @method processService - encapsulates the location and invocation of a service request
	 *
	 * @param $modelAlias - alias of the service interface
	 * @param $methodName - name of the method for invoking
	 * @return reply of the service method invocation
	 *
	 * @throws InvalidArgumentException - whether $serviceInterfaceAlias is null
	 * @throws InvalidArgumentException - whether $methodName is null
	 *
	 * @access private
	 * @since 1.0
	 */
	private function processRestService($modelAlias, $requestParameter) {
		$parser = Config::getDataParser();
		try {
			if(is_null($modelAlias)) {
				throw new RestExcpetion("The model alias can't be null",500);
			}
			
			$model = $this->getModel($modelAlias);
			$serviceHelper = ServiceHelper::getInstance($this->getDaoConfig());
			$requestMethod = $this->get_request_method(); 
			
			$args = array();
			
			switch ($requestMethod) {
				case "GET": {
					$method = null;
					array_push($args, $model);
					
					if(is_null($requestParameter)){
						$method = "getAll";
					} else if(is_numeric($requestParameter)){
						$method = "get";
						//id
						array_push($args, $requestParameter);
					} else {
						$partsOfParameter = str_getcsv($requestParameter,":");
						if(!is_null($partsOfParameter) && sizeof($partsOfParameter)==2) {
							$method = "getCriteria";
							// attribute
							array_push($args, $partsOfParameter[0]);
							// expression
							array_push($args, $partsOfParameter[1]);
						}
					}
					
					$this->cleanInputs($args);
					$return = $this->invoke($serviceHelper, $method,$args);

					if(is_null($return)) {
						$this->response(null,204);
					} else {
						$this->response($parser->parseOutputData($return),200);
					}
					
					break;
				}
				
				case "POST": {
					$dto = $this->getInputPost();
					array_push($args, $dto);
					
					$this->cleanInputs($args);
					$return = $this->invoke($serviceHelper,"create",$args);
					$this->response($parser->parseOutputData($return),201);
					
					break;
				}
				
				case "PUT": {
					$dto = $this->getInputPost();
					array_push($args, $dto);
					
					$this->cleanInputs($args);
					$return = $this->invoke($serviceHelper,"update",$args);
					$this->response($parser->parseOutputData($return),202);
					
					break;
				}
				
				case "DELETE": {
					if(!is_numeric($requestParameter)){
						throw new RestExcpetion("The parameter for deleting should numeric", 500);
					}

					//id
					array_push($args, $requestParameter);
					
					$this->cleanInputs($args);
					$this->invoke($serviceHelper,"delete",$args);
					$this->response(null,204);
						
					break;
				}
				
				default: {
					throw new RestExcpetion("HTTP method invalid",405);
				}
			}
		} catch (RestExcpetion $re) {
			$this->response($parser->parseOutputData($re->getMessage()),$re->getHttpStatus());
		} catch (\Exception $e) {
			$this->response($parser->parseOutputData($e->getMessage()),500);
		} catch (\Error $err) {
			$this->response($parser->parseOutputData($err->getMessage()),500);
		}
	}
	
	private function getInputPost() {
		return Config::getDataParser()->parseInputData(file_get_contents('php://input'));
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
	private function invoke($object, $methodName,$args, bool $arrayHowUniqueArg=false) {
		if(is_null($object)) {
			$this->response('Requested service/command not found',404);
		} else {
			if(!is_null($methodName)) {
				if((int)method_exists($object,$methodName) > 0) {
					
					// invoking method on service/command
					$reflectionMethod = new \ReflectionMethod($object, $methodName);
					if($arrayHowUniqueArg) {
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
	public abstract function locateModel($modelAlias);

	
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
	public abstract function getDaoConfig();
	
}

