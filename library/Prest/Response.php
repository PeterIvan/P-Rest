<?php

class Prest_Response
{
	const OK = 200;
	const CREATED = 201;
	const NO_CONTENT = 204;

	const MOVED_PERMANANTLY = 301;
	const SEE_OTHER = 303;

	const BAD_REQUEST = 400;
	const AUTHORIZATION_REQUIRED = 401;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const NOT_ACCEPTABLE = 406;
	const CONFLICT = 409;
	const GONE = 410;
	const REQUEST_ENTITY_TOO_LARGE = 413;
	const UNSUPPORTED_MEDIA_TYPE = 415;

	const SERVER_ERROR = 500;
	const NOT_IMPLEMENTED = 501;

	protected $_headers = array();
	protected $_response_code = 200;
	protected $_body = null;

	protected $_representation = null;

################################################################################
# public
################################################################################

	public function __construct( Prest_Representation $i_representation = null )
	{
		if ( $i_representation )
			$this->_representation = $i_representation;

		$this->_setup();
	}

	############################################################################
	# headers ##################################################################

	public function setHeaders( array $i_headers )
	{
		foreach ( $i_headers as $header )
		{
			$replace = isset($header['replace']) ? $header['replace'] : false;

			$this->addHeader($header['name'], $header['value'], $replace);
		}

		return $this;
	}

	public function addHeader( $i_header_name, $i_value, $i_replace = false )
	{
		$header_name = $this->_normalizeName($i_header_name);

		if ( $i_replace )
		{
			foreach ( $this->_headers as $i => $header )
			{
				if ( $header['name'] == $header_name )
				{
					unset($this->_headers[$i]);

					break;
				}
			}
		}

		$this->_headers[] = array
		(
			'name' => $header_name,
			'value' => $i_value,
			'replace' => $i_replace
		);

		return $this;
	}

	public function clearHeaders()
	{
		$this->_headers = array();

		return $this;
	}

	############################################################################
	# response code ############################################################

	public function setResponseCode( $i_code )
	{
		$this->_response_code = $i_code;

		return $this;
	}

	############################################################################
	# body #####################################################################

	public function setBody( $i_body )
	{
		$this->_body = trim($i_body);
	}

	public function hasBody()
	{
		return ( !empty($this->_body) );
	}

	public function clearBody()
	{
		$this->_body = null;

		return $this;
	}

	############################################################################

	public function send()
	{
		$this->_sendHeaders();

		echo $this->_body;
	}

################################################################################
# protected
################################################################################

	protected function _setup()
	{
		if ( $this->_representation )
		{
			$representation_response_code = $this->_representation->getResponseCode();
			$representation_headers = $this->_representation->getHeaders();

			if ( $representation_response_code )
				$this->setResponseCode($representation_response_code);

			if ( $representation_headers )
				$this->setHeaders($representation_headers);

			$this->_body = $this->_representation;
		}
	}

	protected function _normalizeName( $i_header )
	{
		$filtered = str_replace(array('-', '_'), ' ', (string)$i_header);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);

		return $filtered;
	}

	protected function _sendHeaders()
	{
		if ( !headers_sent() )
		{
			header("HTTP/1.1 {$this->_response_code}");

			foreach ( $this->_headers as $i => $header )
				header("{$header['name']}: {$header['value']}", $header['replace']);
		}

		return $this;
	}
}

?>