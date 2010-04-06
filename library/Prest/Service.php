<?php

class Prest_Service
{
	protected $_config = array();
	protected $_params = array();

	protected $_resource_directories = array();

	protected $_url = null;
	protected $_router = null;

	protected $_request = null;
	protected $_response = null;

	protected $_resource = null;
	protected $_action = null;

	protected $_auth_adapter = null;
	protected $_auth_challenge_generator = null;
	protected $_supported_languages = array();

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

	public function getBaseUrl()
	{
		$request_url = $this->_request->getUrl();

		return $request_url->getScheme() . '://' . $request_url->getHost() . $request_url->getBasePath();;
	}

	public function getRouter()
	{
		return $this->_router;
	}

	public function getRequest() { return $this->_request; }
	public function getResponse() { return $this->_response; }

	public function getAuthAdapter() { return $this->_auth_adapter; }
	public function getAuthChallengeGenerator() { return $this->_auth_challenge_generator; }

	public function getDefaultMediaType()
	{
		if ( isset($this->_config['default_media_type']) and !empty($this->_config['default_media_type']) )
			return $this->_config['default_media_type'];

		return null;
	}

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
		$this->_prepareDispatch();

		if ( $this->_validateRequest() )
		{
			try
			{
				$action = $this->_action;

				$representation = $this->_resource->$action();

				$this->_response->setBody($representation);

				$this->_response->send();
			}
			catch ( Exception $e )
			{
				die($e->getMessage());
			}
		}
		else
			die('request is invalid');
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

	protected function _setup()
	{
		$this->_url = new Prest_Service_Url();

		$this->_setupRouter();

		$this->_request = new Prest_Http_Request();
		$this->_response = new Prest_Http_Response( array('service' => $this) );
	}

	protected function _setupRouter()
	{
		if ( !$this->_router )
		{
			$config = array( 'service' => $this );

			$this->_router = new Prest_Router($config);
		}
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

		$directory = $this->_findResourceDirectory($resource);
		$class = basename($resource);
		$file = "$directory/$class.php";

		if ( is_dir($directory) and is_file($file) )
		{
			require_once($file);

			$config = array
			(
				'service' => $this,
				'directory' => $directory
			);

			$this->_resource = new $class($config);
		}
		else
			$this->_response->clientError(404);
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
		return (bool)$this->_resource->validate($this->_action);
	}

	protected function _findResourceDirectory( $i_resource )
	{
		$directory = null;

		foreach ( $this->_resource_directories as $resource_directory )
		{
			$potentional_directory = "$resource_directory/$i_resource";

			if ( is_dir($potentional_directory) )
			{
				$directory = $potentional_directory;
				break;
			}
		}

		return $directory;
	}
}

?>