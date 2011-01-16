<?php

class Prest_Response
{
	const OK_CONTINUE = 100;
	const SWITCHING_PROTOCOLS = 101;

	const OK = 200;
	const CREATED = 201;
	const ACCEPTED = 202;
	const NON_AUTHORITATIVE_INFORMATION = 203;
	const NO_CONTENT = 204;
	const RESET_CONTENT = 205;
	const PARTIAL_CONTENT = 206;

	const MULTIPLE_CHOICES = 300;
	const MOVED_PERMANANTLY = 301;
	const FOUND = 302;
	const SEE_OTHER = 303;
	const NOT_MODIFIED = 304;
	const USE_PROXY = 305;
	// 306 is reserved
	const TEMPORARY_REDIRECT = 307;

	const BAD_REQUEST = 400;
	const UNATUHORIZED = 401;
	const AUTHORIZATION_REQUIRED = 401; // Incorrect name
	const PAYMENT_REQUIRED = 402;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const NOT_ACCEPTABLE = 406;
	const PROXY_AUTHENTICATION_REQUIRED = 407;
	const REQUEST_TIMEOUT = 408;
	const CONFLICT = 409;
	const GONE = 410;
	const LENGTH_REQUIRED = 411;
	const PRECONDITION_FAILED = 412;
	const REQUEST_ENTITY_TOO_LARGE = 413;
	const REQUEST_URI_TOO_LONG = 414;
	const UNSUPPORTED_MEDIA_TYPE = 415;
	const REQUEST_RANGE_NOT_SATISFIABLE = 416;
	const EXPECTATION_FAILED = 417;

	const SERVER_ERROR = 500;
	const NOT_IMPLEMENTED = 501;
	const BAD_GATEWAY = 502;
	const SERVICE_UNAVAILABLE = 503;
	const GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;

################################################################################

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