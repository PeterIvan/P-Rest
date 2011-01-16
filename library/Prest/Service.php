<?php

class Prest_Service
{
	protected $_config = array();
	protected $_params = array();

	protected $_resource_directories = array();

	protected $_url = null;
	protected $_router = null;

	protected $_dispatcher = null;

	protected $_supported_languages = array();

	protected $_default_output_media_type = 'application/json';
	protected $_default_language = null;

	protected $_transaction = null;

################################################################################
# public
################################################################################

	public function __construct( array $i_config = null )
	{
		if ( $i_config )
			$this->_config = $i_config;

		########################################################################

		if ( isset($i_config['supported_languages']) and !empty($i_config['supported_languages']) )
			$this->_supported_languages = (array)$i_config['supported_languages'];

		if ( isset($i_config['default_output_media_type']) and !empty($i_config['default_output_media_type']) )
			$this->_default_output_media_type = $i_config['default_output_media_type'];

		if ( isset($i_config['default_language']) and !empty($i_config['default_language']) )
			$this->_default_language = $i_config['default_language'];

		########################################################################
		
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

	public function getSupportedLanguages() { return $this->_supported_languages; }
	public function getDefaultOutputMediaType() { return $this->_default_output_media_type; }
	public function getDefaultLanguage() { return $this->_default_language; }
	
	public function addResourceDirectory( $i_directory )
	{
		$resolved_directory = realpath($i_directory);

		if ( $resolved_directory and !in_array($resolved_directory, $this->_resource_directories) )
			$this->_resource_directories[] = $resolved_directory;
	}

	public function dispatch()
	{
		if ( !$this->_transaction )
			$this->_transaction = new Prest_Transaction();

		$this->_transaction->begin();

		$response = null;

		try
		{
			$request = new Prest_Request(array('service' => $this));

			$representation = $this->_dispatcher->dispatch($request);

			$response = new Prest_Response($representation);

			$this->_transaction->finish();

			$response->send();
		}
		catch ( Prest_Exception $e )
		{
			$this->_transaction->rollBack();

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
			$this->_transaction->rollBack();

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
		$representation = $this->_dispatcher->dispatch($i_request);

		return $representation;
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

################################################################################
# Transaction ##################################################################

	public function setTransaction( Prest_Transaction $i_transaction )
	{
		$this->_transaction = $i_transaction;
	}

	public function getTransaction() { return $this->_transaction; }

	public function transactionStarted()
	{
		if ( $this->_transaction )
			return $this->_transaction->isStarted();
		else
			return false;
	}

################################################################################
# protected
################################################################################

	protected function _setup()
	{
		$this->_router = $this->_setupRouter();
		$this->_dispatcher = $this->_setupDispatcher();
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

	

}
?>