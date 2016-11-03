<?php
namespace ttm\control;

use ttm\Config;
use ttm\exception\TTMException;
use ttm\model\Model;
use ttm\exception\RestException;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 * 
 * Abstract Class AbstractRestController - Reuseble component that encapsulates 
 * requisitions treatment of web services by RESTFul. Using the patterns of a 
 * 'friendly' URL  (URI) to require resouces to web server: 
 * 
 * services:
 * /contextaplication/service/serviceInterfaceAlias/method/param1/param2/../paramN
 * 
 * commands:
 * /contextaplication/command/commandAlias/param1/param2/../paramN
 * 
 * REST Services:
 * /contextaplication/modelAlias/param1 
 * 
 * where the parametters may be send also using POST http method. 
 *
 * these names approuche the services concepts designed to this architecture, are basically
 * three types of service:
 *  - services: type of service that can promete changes on data 
 *  - commands:  type of service thar just process data and return the output information
 *  - REST service: is a spacial type of services for treatment crud operations required 
 *    by http methods, associating the resource (modelAlias) to a model class.
 *
 * Simple tree of events sequence:
 * 
 * level - method
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
 *    2- processRestService
 *       3- getModel
 *          4- solveModelAlias
 *       3- getCRUDHelper
 *       3- identify The HTTP method
 *       3- prepare parameters
 *       3- invoke CRUDHelper method for requested HTTP method
 *          4- parseInputData
 *          4- parseOutputData  
 *           
 * @see Rest
 *           
 * @package ttm-core-php
 * @namespace ttm\control
 * @abstract
 * @version 1.0
 */
abstract class AbstractRestController extends Rest {
	/**
	 * @property $commands - stores the commands associated with 
	 * your respective alias as they are called.
	 *
	 * @access private
	 * @static 
	 * @since 1.0
	 */
	private static $commands = array();

	/**
	 * @property $services - stores the concrete services (facades) associated 
	 * with your respective interfaces as they are called.
	 * 
	 * @access private
	 * @static
	 * @since 1.0 
	 */
	private static $services = array();

	/**
	 * @property $models - stores the model classes (entities) associated with 
	 * your respective model alias they are called.
	 *
	 * @access private
	 * @static 
	 * @since 1.0
	 */
	private static $models = array();

	/**
	 * @method __construct - override of parent constructor method to not invoke 
     * your behavior  
	 *
	 * @access protected
	 * @magic
	 * @since 1.0
	 */
	protected function __construct() {
		
	}
	
	/**
	 * @method getCommand - this method is responsible for seeking on
	 * stored called commands a command associated with the passed command alias.
	 * Whether the command not found, the method locates the command and stores it. 
	 * Validating also whether the command implements \ttm\control\Command.
	 *
	 * @param $commandAlias - alias command for seeking/locating
	 * @return instance of required command
	 *
	 * @throws InvalidArgumentException - whether $commandAlias is null
	 * @throws TTMException - whether the required command don't implements the \ttm\control\Command
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected function getCommand($commandAlias) {
		if(is_null($commandAlias)) {
			throw new \InvalidArgumentException("The command alias can't be null [AbstractRestController:".__LINE__."]");
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
				throw new TTMException("The command don't implements the \\ttm\\control\\Command [AbstractRestController:".__LINE__."]");
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
			throw new \InvalidArgumentException("The service interface name can't be null [AbstractRestController:".__LINE__."]");
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
				throw new TTMException("The service dont implements the associated interface [AbstractRestController:".__LINE__."]");
			} 
			
			// keeping loaded service
			AbstractRestController::$services[$serviceInterfaceName] = $service;
		}
	
		return $service;
	}
	
	/**
	 * @method getModel - this method is responsible for seeking on
	 * stored called models a models associated with the passed model alias.
	 * Whether the model not found, the method locates the model and stores it.
	 * Used by CRUD operations on REST services required.
	 *
	 * @param $modelAlias - alias of model class for seeking/locating
	 * @return model class name of required alias
	 *
	 * @throws InvalidArgumentException - whether $modelAlias is null
	 *
	 * @access protected
	 * @since 1.0
	 */
	protected function getModel($modelAlias) {
		if(is_null($modelAlias)) {
			throw new \InvalidArgumentException("The model alias can't be null [AbstractRestController:".__LINE__."]");
		}
	
		// checking whether service already called
		if(isset(AbstractRestController::$models[$modelAlias])) {
			return AbstractRestController::$models[$modelAlias];
		}
	
		// locating service
		$model = $this->solveModelAlias($modelAlias);
		
		if(!is_null($model)) {
			// keeping loaded service
			AbstractRestController::$models[$modelAlias] = $model;
		}
	
		return $model;
	}
	
	/**
	 * @method processRequest - when the client makes a server request, the .htaccess should 
	 * beredirect the request to a concrete implementation of this class. The first part 
	 * of the URI is the project that implements the rest controller, the second part 
	 * contains the required resource, this second part is divide into another parts (complement
	 * and resource arguments, like described on class comments). This method is responsible
	 * to identify the type of required service and to solve the complement and arguments
	 * for handling the client request and for involking necessary methods to mount the expected
	 * response.
	 * 
	 * @access public
	 * @since 1.0
	 */
	public function processRequest() {
		try {

			// fixed types 'service' and 'command' or model alias 
			$resource = null;
			
			//ServiceInterfaceAlias, CommandAlias or RequestParameter of RestServices  
			$resourceComplement = null;
			
			//NameMehod of a service or another paramenter for 
			//services or commands (not RESTservices) 
			$resourceArgs = null;
			
			// broking URI for defining the type service and solve 
			// another parts (based on type service)
			if(!strpos($_REQUEST['request'], "/")) {
				$resource = $_REQUEST['request'];
			} else {
				$partsOfRequest = array_filter(str_getcsv($_REQUEST['request'],"/"));
				
				if(!is_null($partsOfRequest) && sizeof($partsOfRequest)>=2) {
					$resource = $partsOfRequest[0]; 
					$resourceComplement = $partsOfRequest[1];
					
					if(sizeof($partsOfRequest)>2) {
						$resourceArgs = array_slice($partsOfRequest, 2);
					}
				}
			}
			
			// defining the type of process and delegating to handlers
			if($resource == "command") {
				// solving command process parameters
				$commandAlias = $resourceComplement;
				
				$this->processCommand($commandAlias,$resourceArgs);
			} else if($resource == "service") {
				// solving service process parameters
				$serviceInterfaceAlias = $resourceComplement;
				$methodName = $resourceArgs[0];
				$resourceArgs = array_slice($resourceArgs, 1);
				
				$this->processService($serviceInterfaceAlias,$methodName,$resourceArgs);
			} else {
				// solving rest service process parameters
				$modelAlias = $resource;
				$requestParameter = $resourceComplement;
				
				$this->processRESTService($modelAlias, $requestParameter);
			}
		} catch (TTMException $ttme) {
			// on ttm exceptions show the message 
			$this->response($ttme->getMessage(), 500);
		} catch (\Exception $e) {
			// on general exceptions show the trace
			$this->response($e->getMessage(), 500);
		} catch (\Error $err) {
			// on errors show the trace
			$this->response($err->getMessage(), 500);
		}
	}
	
	/**
	 * @method processCommand - encapsulates handle of the location and invocation 
	 * of a command request. The command should implement the
	 * \ttm\controlCommand interface. (usually request for getting reports)
	 *
	 * @param $commandAlias - alias of the command
	 * @param $resourceArgs - arguments for invoking the required command
	 * 
	 * @return reply of the command invocation
	 *
	 * @throws RestException - whether $commandAlias is null
	 * @throws RestException - whether http method isent GET or POST
	 *
	 * @access private
	 * @since 1.0
	 */
	private function processCommand($commandAlias,$resourceArgs) {
		// getting configured parser input/output handle
		$parser = Config::getDataParser();
		
		try {
			
			if(is_null($commandAlias)) {
				throw new RestException("The command alias can't be null",500);
			}
			
			$requestMethod = $this->get_request_method();
			if($requestMethod!="GET" && $requestMethod!="POST") {
				throw new RestException("HTTP method should be GET or POST",405);
			}
		
			// setting parameters for invoking command
			$args = array();
			if(!is_null($resourceArgs)) {
				array_push($args, $resourceArgs);
			}
			
			// whether request method is POST setting input on 
			// paramenters for invoking command
			if($requestMethod=="POST") {
				$inputArgs = $this->getInputPost();
				if(!is_null($inputArgs)) {
					array_push($args, $inputArgs);
				}
			}

			$this->cleanInputs($args);
			
			// involking required command
			$command = $this->getCommand($commandAlias);
			$return = $this->invoke($command, "execute", $args, true);
						
			// sending response of command invoke
			$this->response($parser->parseOutputData($return),200);
		} catch (RestException $re) {
			$this->response($parser->parseOutputData($re->getMessage()),$re->getHttpStatus());
		} catch (\Exception $e) {
			$this->response($parser->parseOutputData($e->getMessage()),500);
		} catch (\Error $err) {
			$this->response($parser->parseOutputData($err->getMessage()),500);
		}
	}

	/**
	 * @method processService - encapsulates handle of the location and invocation 
	 * of a service request (usually associated a business rule)    
	 *
	 * @param $serviceInterfaceAlias - alias of the service interface
	 * @param $methodName - name of the method for invoking 
	 * @param $resourceArgs - arguments for invoking the required service
	 * 
	 * @return reply of the service method invocation
	 *
	 * @throws RestException - whether $serviceInterfaceAlias is null
	 * @throws RestException - whether $methodName is null
	 * @throws RestException - whether http method isent GET or POST
	 * 
	 * @access private
	 * @since 1.0
	 */
	private function processService($serviceInterfaceAlias, $methodName, $resourceArgs) {
		// getting configured parser input/output handle
		$parser = Config::getDataParser();
		
		if(is_null($serviceInterfaceAlias)) {
			throw new RestException("The service interface alias can't be null",500);
		}

		if(is_null($methodName)) {
			throw new RestException("The service method name can't be null",500);
		}

		$requestMethod = $this->get_request_method();
		if($requestMethod!="GET" && $requestMethod!="POST") { 
			throw new RestException("HTTP method should be GET or POST",405);
		}
		
		// setting parameters for invoking service
		try {
			$args = array();
			
			if(!is_null($resourceArgs)) {
				array_push($args, $resourceArgs);
			}
			
			// whether request method is POST setting input on
			// paramenters for invoking service
			if($requestMethod=="POST") {
				$inputArgs = $this->getInputPost();
				if(!is_null($inputArgs)) {
					array_push($args, $inputArgs);
				}
			}
				
			$this->cleanInputs($args);
			
			// solving alias before getting the service
			$serviceInterfaceName = $this->solveServiceInterfaceAlias($serviceInterfaceAlias);
			
			// invoking required service method
			$service = $this->getService($serviceInterfaceName);
			$return = $this->invoke($service, $methodName, $args);
				
			// sending response of command invoke
			$this->response($parser->parseOutputData($return),200);
		} catch (RestException $re) {
			$this->response($parser->parseOutputData($re->getMessage()),$re->getHttpStatus());
		} catch (\Exception $e) {
			$this->response($parser->parseOutputData($e->getMessage()),500);
		} catch (\Error $err) {
			$this->response($parser->parseOutputData($err->getMessage()),500);
		}
	}
	
	/**
	 * @method processRESTService - encapsulates handle of the HTTP REST method resquested,
	 * usind a CRUDHelper for invoking the CRUD operations associated to REST service required
	 * 
	 *
	 * @param $modelAlias - is the resource of the URI resposible to associate on model class 
	 * (entity) where the CRUD operations will be executed. So, the resource name should be a
	 * model class name.
	 * @param $requestParameter - possibles request parameters:
	 * GET: 
	 *    - id = (ex: 1 or 1&2 on composite key)
	 *    - no parameters = (array args null) for returning all elements
	 *    - where clause = Entity alias with an expression (ex: alias:expression -> u:u.active=true) 
	 * POST: no parameters = because the parameter was sending on input post
	 * PUT: no parameters = same case of POST, the parameter on input post
	 * DELETE: id =  (ex: 1 or 1&2 on composite key)
	 * 
	 * @return reply of the CRUDHelper method invocation
	 *
	 * @access private
	 * @since 1.0
	 */
	private function processRESTService($modelAlias, $requestParameter) {
		// getting configured parser input/output handle
		$parser = Config::getDataParser();
		try {
			// getting model class 
			$model = $this->getModel($modelAlias);

			// getting CRUDHelper 
			$CRUDHelper = Config::getCRUDHelper($this->getDaoConfig());
			
			// getting resquest method
			$requestMethod = $this->get_request_method(); 

			$args = array();
			// setting model on arguments (entity)
			array_push($args, $model);
			
			switch ($requestMethod) {
				case "GET": {
					$method = null;
					
					// checking the parameter for choose the get method 
					// of CRUDHelper and mounting the arguments for invocation
					if(is_null($requestParameter)){
						$method = "getAll";
					} else if(is_numeric($requestParameter)){
						$method = "get";
						//id
						array_push($args, $requestParameter);
					} else {
						// alias:expression (o:o.id>2)
						$partsOfParameter = str_getcsv($requestParameter,":");
						if(!is_null($partsOfParameter) && sizeof($partsOfParameter)==2) {
							$method = "getBySimpleCriteria";
							// alias
							array_push($args, $partsOfParameter[0]);
							// expression
							array_push($args, $partsOfParameter[1]);
						}
					}
					
					$this->cleanInputs($args);
					
					//invoking CRUDHelper get method
					$return = $this->invoke($CRUDHelper,$method,$args);
					
					if(is_null($return)) {
						// sending response for no content  
						$this->response(null,204);
					} else {
						// parsing out the reply of the method invocation with associated http status
						$this->response($parser->parseOutputData($return),200);
					}
					
					break;
				}
				
				case "POST": {
					// getting input post and setting on arguments for invoking
					$dto = $this->getInputPost();
					array_push($args, $dto);
					
					$this->cleanInputs($args);
					
					//invoking CRUDHelper create method
					$return = $this->invoke($CRUDHelper,"create",$args);
					
					// parsing out the reply of the method invocation with associated http status
					$this->response($parser->parseOutputData($return),201);
					
					break;
				}
				
				case "PUT": {
					// getting input post and setting on arguments for invoking
					$dto = $this->getInputPost();
					array_push($args, $dto);
					
					$this->cleanInputs($args);
					
					//invoking CRUDHelper update method
					$return = $this->invoke($CRUDHelper,"update",$args);
					
					// parsing out the reply of the method invocation with associated http status
					$this->response($parser->parseOutputData($return),202);
					
					break;
				}
				
				case "DELETE": {
					//id or composite id
					array_push($args, $requestParameter);
					
					$this->cleanInputs($args);
					
					//invoking CRUDHelper update method
					$this->invoke($CRUDHelper,"delete",$args);
					
					// sending response for no content
					$this->response(null,204);
						
					break;
				}
				
				default: {
					throw new RestException("HTTP method invalid",405);
				}
			}
		} catch (RestException $re) {
			$this->response($parser->parseOutputData($re->getMessage()),$re->getHttpStatus());
		} catch (\Exception $e) {
			$this->response($parser->parseOutputData($e->getMessage()),500);
		} catch (\Error $err) {
			$this->response($parser->parseOutputData($err->getMessage()),500);
		}
	}

	/**
	 * @method getInputPost - encapsulates getting and parsing in of input post
	 *
	 * @return input post 
	 *
	 * @access private
	 * @since 1.0
	 */
	private function getInputPost() {
		return Config::getDataParser()->parseInputData(file_get_contents('php://input'));
	}
	
	/**
	 * @method invoke - this method is responsible for invoking the services and commands, 
	 * encapsulating the construction of the parameters using the given post arguments 
	 *
	 * @param $object - its the instance of the service/command where the method will be invoked
	 * @param $methodName - name of method will be invoked (using reflection mechamism)
	 * @param array $arguments - arguments for invoking of the method 
	 * @param bool $arrayHowUniqueArg - flag that indicates how to pass the arguments on method 
	 * invocation, whether true the arguments are passes with a array, else any position of array 
	 * arguments will be a paramter of method signature 
	 * 
	 * @return reply of the method invocation
	 * 
	 * @access private
	 * @since 1.0
	 */
	private function invoke($object, $methodName, array $arguments, bool $arrayHowUniqueArg=false) {
		if(is_null($object)) {
			// whether service or command not found
			$this->response('Requested service/command not found',404);
		} else {
			// checking whether method exists
			if(!is_null($methodName)) {
				if((int)method_exists($object,$methodName) > 0) {
					// invoking method on service/command
					$reflectionMethod = new \ReflectionMethod($object, $methodName);
					if($arrayHowUniqueArg) {
						// on command sets an array of arguments
						return $reflectionMethod->invoke($object,$arguments);
					} else {
						// on service the sequence of input arguments should be the 
						// same of the method parameters (method signature)
						return $reflectionMethod->invokeArgs($object, $arguments);
					}
				} else {
					$this->response('Method of service/command not found',404);
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
	 * 
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
	 * 
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
	 * 
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
	 * @method solveModelAlias - determines the creation of a method for solving the
	 * model alias returning the complete name of service model call (with namespace), 
	 * bucause process of REST service needs of the model class for CRUDHelp invoking
	 *
	 * @example User -> Project\model\User
	 *
	 * @param $modelAlias - model alias for solving
	 * 
	 * @return the model class name
	 *
	 * @throws InvalidArgumentException - The model alias can't be null
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public abstract function solveModelAlias($modelAlias);
	
	/**
	 * @method getDaoConfig - determines how RestController implementation will send the dao 
	 * configurations 
	 *
	 * @return array of dao configurations of the data sources configured
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public abstract function getDaoConfig();
}

