<?php

class Prest_Response
{
	const OK = 200;

	const CLIENT_ERROR = 400;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const UNSUPPORTED_MEDIA_TYPE = 415;

	const SERVER_ERROR = 500;

	protected $_headers = array();
	protected $_response_code = 200;
	protected $_body = null;

################################################################################
# public
################################################################################

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