<?php

class Prest_Service
{
	protected $_config = array();
	protected $_params = array();

	protected $_resource_directories = array();

	protected $_url = null;
	protected $_router = null;

	protected $_dispatcher = null;

	protected $_auth_adapter = null;
	protected $_auth_challenge_generator = null;
	protected $_supported_languages = array();

################################################################################
# public
################################################################################

	public function __construct( array $i_config = null )
	{
		if ( $i_config )
			$this->_config = $i_config;

		if ( isset($i_config['supported_languages']) and !empty($i_config['supported_languages']) )
			$this->_supported_languages = (array)$i_config['supported_languages'];

		$this->_setup();
	}

	public function setParam( $i_param, $i_value )
	{
		$this->_params[$i_param] = $i_value;

		return $this;
	}

	public function getParam( $i_param )
	{
		if ( isset($this->_params[$i_param]) )
			return $this->_params[$i_param];

		return null;
	}

	public function getRouter()
	{
		return $this->_router;
	}

	public function getSupportedMethods()
	{
		return array ('get', 'put', 'delete', 'post', 'head', 'options');
	}

	public function getDispatcher()
	{
		return $this->_dispatcher;
	}

	public function getAuthAdapter() { return $this->_auth_adapter; }
	public function getAuthChallengeGenerator() { return $this->_auth_challenge_generator; }

	public function getDefaultLanguage()
	{
		if ( isset($this->_config['default_language']) and !empty($this->_config['default_language']) )
			return $this->_config['default_language'];

		return null;
	}

	public function getSupportedLanguages() { return $this->_supported_languages; }

	public function addResourceDirectory( $i_directory )
	{
		$resolved_directory = realpath($i_directory);

		if ( $resolved_directory and !in_array($resolved_directory, $this->_resource_directories) )
			$this->_resource_directories[] = $resolved_directory;
	}

	public function setAuthAdapter( Zend_Auth_Adapter_Interface $i_adapter )
	{
		$this->_auth_adapter = $i_adapter;

		return $this;
	}

	public function setAuthChallengeGenerator( Prest_Auth_ChallengeGenerator_Interface $i_generator )
	{
		$this->_auth_challenge_generator = $i_generator;

		return $this;
	}

	public function dispatch()
	{
		$response = null;

		try
		{
			$request = new Prest_Request(array('service' => $this));

			$representation = $this->_dispatcher->dispatch($request);

			$response = new Prest_Response($representation);

			$response->send();
		}
		catch ( Prest_Exception $e )
		{
			$response = new Prest_Response();

			$response->setResponseCode($e->getCode());
			$response->setHeaders($e->getHeaders());

			$message = $e->getMessage();

			if ( !empty($message) )
				$response->setBody($message);

			$response->send();
		}
		catch ( Exception $e )
		{
			$response = new Prest_Response();

			$response->setResponseCode(Prest_Response::SERVER_ERROR);

			$message = $e->getMessage();

			if ( $message )
				$response->setBody($message);

			$response->send();
		}
	}

	public function makeRequest( Prest_Request $i_request )
	{
		var_dump('make');
		$representation = $this->_dispatcher->dispatch($i_request);

		var_dump($representation);
	}

	public function getResourceDirectory( $i_resource_name )
	{
		$directory = null;

		foreach ( $this->_resource_directories as $resource_directory )
		{
			$potentional_directory = "$resource_directory/$i_resource_name";

			if ( is_dir($potentional_directory) )
			{
				$directory = $potentional_directory;
				break;
			}
		}

		return $directory;
	}

	public function authenticate()
	{
		if ( $this->_auth_adapter )
		{
			$auth = Zend_Auth::getInstance();

			$auth_result = $auth->authenticate($this->_auth_adapter);

			if ( !$auth_result->isValid() )
			{
				if ( $this->_auth_challenge_generator )
				{
					$challenge = $this->_auth_challenge_generator->generate();

					$this->_response->code401($challenge);
				}
				else
					die('Auth challenge generator is not defined.');
			}
		}
		else
			die('auth adapter not defined.'); //TODO: throw

		return $this;
	}

################################################################################
# protected
################################################################################

	protected function _setup()
	{
		//$this->_url = new Prest_Service_Url();

		$this->_router = $this->_setupRouter();
		$this->_dispatcher = $this->_setupDispatcher();

		$this->_response = new Prest_Http_Response( array('service' => $this) );
	}

	protected function _setupRouter()
	{
		$router_params = array( 'service' => $this );

		return new Prest_Router($router_params);
	}

	protected function _setupDispatcher()
	{
		$dispatcher_params = array('service' => $this, 'router' => $this->_router);

		return new Prest_Dispatcher($dispatcher_params);
	}

	protected function _prepareDispatch()
	{
		$this->_setupResource();
		$this->_setupAction();
	}

	protected function _setupResource()
	{
		$matched_route = $this->_router->getMatchedRoute();
		$resource = $matched_route['resource'];
	}

	protected function _setupAction()
	{
		$matched_route = $this->_router->getMatchedRoute();
		$request_method = $this->_request->getMethod();

		$action = $matched_route['type'] . ucfirst($request_method);

		if ( method_exists($this->_resource, $action) )
			$this->_action = $action;
		else
			die("$action dont exists."); // TODO: better handling
	}

	protected function _validateRequest()
	{
		//$resource_validator = new

		return (bool)$this->_resource->validate($this->_action);
	}

}
?>