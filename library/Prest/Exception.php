<?php

class Prest_Exception extends Exception
{
	protected $_code = Prest_Response::SERVER_ERROR;
	protected $_headers = array();

	public function getHeaders() { return $this->_headers; }

	public function setHeaders( array $i_headers)
	{
		$this->_headers = $i_headers;

		return $this;
	}
}

?>