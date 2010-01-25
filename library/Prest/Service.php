<?php

class Prest_Service
{
	protected $_config = array();

	protected $_url = null;
	protected $_router = null;

	protected $_request = null;
	protected $_response = null;

	protected $_resource = null;
	protected $_action = null;

	protected $_auth_adapter = null;
	protected $_challenge_generator = null;
	protected $_supported_languages = array();

	protected $_ok_response = null;
	protected $_redirect_response = null;
	protected $_client_error_response = null;
	protected $_server_error_response = null;

	public function __construct( array $i_config = null )
	{
		if ( $i_config )
			$this->_config = $i_config;

		if ( isset($i_config['supported_languages']) and !empty($i_config['supported_languages']) )
			$this->_supported_languages = (array)$i_config['supported_languages'];

		$this->_setup();
	}

	public function getUrl()
	{
		return $this->_url;
	}

	public function getRouter()
	{
		return $this->_router;
	}

	public function getRequest() { return $this->_request; }
	public function getResponse() { return $this->_response; }

	public function getAuthAdapter() { return $this->_auth_adapter; }
	public function getChallengeGenerator() { return $this->_challenge_generator; }

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

	public function setAuthAdapter( Zend_Auth_Adapter_Interface $i_adapter )
	{
		$this->_auth_adapter = $i_adapter;

		return $this;
	}

	public function setChallengeGenerator( Prest_ChallengeGenerator_Interface $i_generator )
	{
		$this->_challenge_generator = $i_generator;

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

				$this->_resource->$action();

				$representation = $this->_resource->getRepresentation();

				$this->_response->setBody($representation);

				$this->_response->sendResponse();
			}
			catch ( Exception $e )
			{
				die($e->getMessage());
			}
		}
		else
			die('request is invalid');
	}

	public function success( $i_code, $i_body )
	{
	}

	public function redirect( $i_code, $i_body )
	{

	}

	public function clientError( $i_code )
	{
		$client_error = "code$i_code";

	}

	public function serverError()
	{
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

		if ( $this->_config['routes'] )
			$this->_router->setRoutes($this->_config['routes']);
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

		$directory = realpath("resources/$resource");
		$file = "$directory/$resource.php";

		if ( is_dir($directory) and is_file($file) )
		{
			require_once($file);

			$config = array
			(
				'service' => $this,
				'directory' => $directory
			);

			$this->_resource = new $resource($config);
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

		return true;
	}
}

?>