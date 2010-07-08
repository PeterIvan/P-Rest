<?php

class Prest_Request_Headers
{
	protected $_raw_headers = array();
	protected $_all_headers = null;

	protected $_accept = array();
	protected $_accept_language = array();

	protected $_content_type = array();
	protected $_content_language = array();

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

				if ( isset($_SERVER['CONTENT_TYPE']) )
					$headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
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

	############################################################################
	# Accept headers ###########################################################

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

	############################################################################
	# Content headers ##########################################################

	public function getContentType()
	{
		// TODO: consult section 7.2.1 of RFC2616

		if ( !$this->_content_type )
		{
			$all_headers = $this->getAllheaders();

			if ( isset($all_headers['Content-Type']) )
			{
				$ct = $all_headers['Content-Type'];

				$params = array();
				$media_type = $ct;

				if ( strpos($ct, ';') !== false )
				{
					list($media_type, $raw_params) = explode(';', $ct);

					if ( !empty($raw_params) )
					{
						$param_list = array();

						if ( strpos($raw_params, ',') !== false )
							$param_list = explode(',', $raw_params);
						else
							$param_list[] = $raw_params;

						foreach ( $param_list as $raw_param )
						{
							list($p_name, $p_value) = explode('=', $raw_param);
							
							$params[$p_name] = $p_value;
						}
					}
				}

				$this->_content_type = array('media_range' => $media_type, 'params' => $params);
			}
		}

		return $this->_content_type;
	}

	public function getContentLanguage()
	{
		if ( empty($this->_content_language) )
		{
			$all_headers = $this->getAllheaders();

			if ( isset($all_headers['Content-Language']) )
			{
				$cl = $all_headers['Content-Language'];

				if ( strpos($cl, ',') !== false )
				{
					$languages = explode(',', $cl);
					$trimmed_languages = array();

					foreach ( $languages as $lang )
						$trimmed_languages[] = trim($lang);

					$this->_content_language = $trimmed_languages;
				}
				else
					$this->_content_language = (array)trim($cl);
			}
		}

		return $this->_content_language;
	}

	############################################################################

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