<?php

class Prest_Request
{
	protected $_url = null;
	protected $_headers = null;
	protected $_params = null;

	public function __construct()
	{
		$this->_setup();
	}

	public function getUrl() { return $this->_url; }
	public function getHeaders() { return $this->_headers; }
	public function getMethod () { return strtolower($_SERVER['REQUEST_METHOD']); }

	public function getParams()
	{
		if ( !$this->_params )
		{
			$params = array();

			if ( isset($_GET) and is_array($_GET) )
				$params += $_GET;
			if ( isset($_POST) and is_array($_POST) )
				$params += $_POST;

			$this->_params = $params;
		}

		return $this->_params;
	}

	public function isGet() { return ($_SERVER['REQUEST_METHOD'] == 'GET'); }
	public function isPut() { return ($_SERVER['REQUEST_METHOD'] == 'PUT'); }
	public function isDelete() { return ($_SERVER['REQUEST_METHOD'] == 'DELETE'); }
	public function isPost() { return ($_SERVER['REQUEST_METHOD'] == 'POST'); }
	public function isHead() { return ($_SERVER['REQUEST_METHOD'] == 'HEAD'); }
	public function isOptions() { return ($_SERVER['REQUEST_METHOD'] == 'OPTIONS'); }

	public function isSecure()
	{
	}

	public function isValid()
	{
		// TODO: some validation should be in place

		return true;
	}

	protected function _setup()
	{
		$this->_url = new Prest_Request_Http_Url();
		$this->_headers = new Prest_Http_Request_Headers();
	}
}

?>