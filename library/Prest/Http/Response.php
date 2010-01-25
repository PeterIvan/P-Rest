<?php

class Prest_Http_Response
{
	protected $_service = null;

	protected $_headers = null;
	protected $_body = null;

	protected $_informational = null;
	protected $_successful = null;
	protected $_redirection = null;
	protected $_client_error = null;
	protected $_server_error = null;

	public function __construct( array $i_config = array() )
	{
		if ( isset($i_config['service']) )
			$this->_service = $i_config['service'];

		$this->_setup();
	}

	public function getHeaders() { return $this->_headers; }

	public function setResponseCode( $i_code )
	{
		$this->_headers->setResponseCode($i_code);
	}

	public function setBody( $i_body )
	{
		$this->_body = $i_body;

		return $this;
	}

	public function send()
	{
		if ( $this->_body instanceof Prest_Representation )
		{
			# add Content-Type header ###########
			$media_type = $this->_body->getMediaType();

			if ( $media_type )
				$this->_headers->add('Content-Type', $media_type, true);

			# add Content-Language header ###########
			$language = $this->_body->getLanguage();

			if ( $language )
				$this->_headers->add('Content-Language', $language, true);
		}

		$this->_headers->send();

		if ( $this->_body )
			echo $this->_body;
	}

	public function informational( $i_code )
	{
		if ( !$this->_informational )
			$this->_informational = new Prest_Http_Response_Informational();

		//$this->_

		$args = func_get_args();
		unset($args[0]);

		$method = "code$i_code";

		$body = call_user_func_array(array($this->_informational, $method), $args);


	}

	public function successful( $i_code )
	{
		$args = func_get_args();
		unset($args[0]);
	}

	public function redirection( $i_code )
	{
		$args = func_get_args();
		unset($args[0]);
	}

	public function clientError( $i_code, array $i_params = array() )
	{
		if ( !$this->_client_error )
		{
			$config = array
			(
				'service' => $this->_service,
			);

			$this->_client_error = new Prest_Http_Response_ClientError($config);
		}

		$method = "code$i_code";

		$this->_client_error->$method($i_args);

		exit;
	}

	public function serverError( $i_code )
	{
		$args = func_get_args();
		unset($args[0]);
	}

	protected function _setup()
	{
		$this->_headers = new Prest_Http_Response_Headers();
	}
}

?>