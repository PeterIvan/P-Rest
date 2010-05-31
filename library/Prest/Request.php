<?php

class Prest_Request
{
	protected $_service = null;

	protected $_raw_params = array();

	protected $_url = null;
	protected $_method = null;
	protected $_headers = null;
	protected $_params = null;

	public function __construct( array $i_params = array() )
	{
		$this->_service = $i_params['service'];

		$this->_raw_params = $i_params;

		$this->_setup();
	}

	public function getUrl() { return $this->_url; }
	public function getMethod () { return $this->_method; }
	public function getParams() { return $this->_params; }
	public function getHeaders() { return $this->_headers; }

	public function isGet() { return ($this->_method == 'get'); }
	public function isPut() { return ($this->_method == 'put'); }
	public function isDelete() { return ($this->_method == 'delete'); }
	public function isPost() { return ($this->_method == 'post'); }
	public function isHead() { return ($this->_method == 'head'); }
	public function isOptions() { return ($this->_method == 'options'); }

	public function isSecure()
	{
	}

	public function isValid()
	{
		// TODO: some validation should be in place

		return true;
	}

################################################################################
# protected
################################################################################

	protected function _setup()
	{
		$this->_method = $this->_setupMethod();
		$this->_url = $this->_setupUrl();
		$this->_params = $this->_setupParams();
		$this->_headers = $this->_setupHeaders();
	}

	protected function _setupMethod()
	{
		$method = null;

		if ( isset($this->_raw_params['method']) )
			$method = $this->_raw_params['method'];
		else
			$method = $_SERVER['REQUEST_METHOD'];

		$method = strtolower($method);

		if ( !in_array($method, $this->_service->getSupportedMethods()) )
			throw new Prest_Exception('Unsupported method', Prest_Response::METHOD_NOT_ALLOWED);
		else
			return $method;
	}

	protected function _setupUrl()
	{
		$url = null;

		if ( isset($this->_raw_params['url']) )
			$url = new Prest_Request_Http_Url($this->_raw_params['url']);
		else
			$url = new Prest_Request_Http_Url();

		return $url;
	}

	protected function _setupParams()
	{
		$params = array();

		if ( $this->isGet() )
			$params += $_GET;
		if ( $this->isPost() )
			$params += $_POST;

		// TODO: suport PUT/DELETE
		// TODO: support custom passed params

		return $params;
	}

	protected function _setupHeaders()
	{
		$headers = null;

		if ( isset($this->_raw_params['headers']) )
			$headers = new Prest_Request_Headers($this->_raw_params['headers']);
		else
			$headers = new Prest_Request_Headers();

		return $headers;
	}
}

?>