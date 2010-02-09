<?php

class Prest_Http_Response_Headers
{
	protected $_response_code = null;
	protected $_headers = array();

	public function __construct(  )
	{

	}

	public function setResponseCode( $i_code )
	{
		$this->_response_code = $i_code;

		return $this;
	}

	public function set( array $i_headers )
	{
		foreach ( $i_headers as $header )
		{
			$replace = isset($header['replace']) ? $header['replace'] : false;

			$this->add($header['name'], $header['value'], $replace);
		}

		return $this;
	}

	public function add( $i_header_name, $i_value, $i_replace = false )
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

	public function clearAll()
	{
		$this->_response_code = 200;
		$this->_headers = array();

		return $this;
	}

	########################
	# headers mutators #####

	public function setContentType( $i_content_type, $i_charset = 'utf-8' )
	{
		// TODO

		return $this;
	}

	public function send()
	{
		if ( !headers_sent() )
		{
			header("HTTP/1.1 {$this->_response_code}");

			foreach ( $this->_headers as $i => $header )
				header("{$header['name']}: {$header['value']}", $header['replace']);
		}

		return $this;
	}

	protected function _normalizeName( $i_header )
	{
		$filtered = str_replace(array('-', '_'), ' ', (string)$i_header);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);

		return $filtered;
	}
}

?>