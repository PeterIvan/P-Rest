<?php

class Prest_Request_Headers
{
	protected $_all_headers = null;

	protected $_accept = null;
	protected $_accept_language = null;
	protected $_authorization = null;

	public function __construct()
	{
	}

	public function getAllHeaders()
	{
		if ( !$this->_all_headers )
		{
			$headers = array();

			if ( function_exists('getallheaders') )
				$headers = getallheaders();

			if ( empty($headers) )
			{
				foreach ( $_SERVER as $k => $v )
				{
					if ( substr($k, 0, 5) == 'HTTP_' )
					{
						$k = str_replace('_', ' ', substr($k, 5));
						$k = str_replace(' ', '-', ucwords(strtolower($k)));

						$headers[$k] = $v;
					}
				}
			}

			$this->_all_headers = $headers;
		}

		return $this->_all_headers;
	}

	public function get( $i_header )
	{
		$method = 'get' . ucfirst($i_header);

		if ( method_exists($this, $method) )
			return $this->$method();
		else
		{
			$all_headers = $this->getAllHeaders();

			if ( isset($all_headers[$i_header]) )
				return $all_headers[$i_header];
			else
				return null;
		}
	}

	public function getAccept()
	{
		if ( !$this->_accept )
		{
			$all_headers = $this->getAllheaders();
			$accept = array();

			if ( isset($all_headers['Accept']) )
			{
				// TODO: reformat according http spec, sorting by quality etc

				$accept_exploded = explode(',', $all_headers['Accept']);

				foreach ( $accept_exploded as $i => $v )
				{
					if ( strpos($v, ';') !== false )
						$accept[] = substr($v, 0, strpos($v, ';'));
					else
						$accept[] = $v;
				}

				$this->_accept = $accept;
			}
		}

		return $this->_accept;
	}

	public function getAcceptLanguage()
	{
		if ( !$this->_accept_language )
		{
			$all_headers = $this->getAllheaders();
			$accept_language = array();

			if ( isset($all_headers['Accept-Language']) )
			{
				// TODO: reformat according http spec, sorting by quality etc

				$accept_language__exploded = explode(',', $all_headers['Accept-Language']);

				foreach ( $accept_language__exploded as $i => $v )
				{
					if ( strpos($v, ';') !== false )
						$accept_language[] = substr($v, 0, strpos($v, ';'));
					else
						$accept_language[] = $v;
				}

				$this->_accept_language = $accept_language;
			}
		}

		return $this->_accept_language;
	}

	public function getAuthorization()
	{
		if ( !$this->_authorization )
		{
			$all_headers = $this->getAllheaders();

			if ( isset($all_headers['Authorization']) )
				$this->_authorization = $all_headers['Authorization'];
		}

		return $this->_authorization;
	}
}

?>