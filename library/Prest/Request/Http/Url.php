<?php

class Prest_Request_Http_Url
{
	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';

	protected $_raw_url = null;
	protected $_parsed_raw_url = null;

	protected $_scheme = null;
	protected $_host = null;
	protected $_request_uri = null;
	protected $_base_url = null;
	protected $_full_base_url = null;
	protected $_base_path = null;
	protected $_path_info = null;

################################################################################
# public
################################################################################

	public function __construct( $i_url = null )
	{
		if ( $i_url )
			$this->_raw_url = $i_url;
		
		$this->_setup();
	}

################################################################################

	public function getScheme()	{ return $this->_scheme; }
	public function getHost() { return $this->_host; }
	public function getRequestUri()	{ return $this->_request_uri; }
	public function getBaseUrl() { return $this->_base_url; }
	public function getFullBaseUrl() { return $this->_full_base_url; }
	public function getBasePath() { return $this->_base_path; }
	public function getPathInfo() { return $this->_path_info; }

################################################################################
# protected
################################################################################

	protected function _setup()
	{
		if ( $this->_raw_url )
			$this->_parsed_raw_url = parse_url($this->_raw_url);

		$this->_scheme = $this->_setupScheme();
		$this->_host = $this->_setupHost();
		$this->_request_uri = $this->_setupRequestUri();
		$this->_base_url = $this->_setupBaseUrl();
		$this->_full_base_url = $this->_setupFullBaseUrl();
		$this->_base_path = $this->_setupBasePath();
		$this->_path_info = $this->_setupPathInfo();
	}

	protected function _setupScheme()
	{
		$scheme = null;

		if ( $this->_raw_url )
		{
			if ( strpos($this->_raw_url, self::SCHEME_HTTPS) !== false )
				$scheme = self::SCHEME_HTTPS;
			else
				$scheme = self::SCHEME_HTTP;
		}
		else
		{
			// nicked from Zend Framework (http://framework.zend.com/),
			// Zend_Controller_Request_Http class

			$scheme = (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on')
				? self::SCHEME_HTTPS : self::SCHEME_HTTP;
		}

		return $scheme;
	}

	protected function _setupHost()
	{
		$host = null;

		if ( $this->_raw_url )
		{
			$host = $this->_parsed_raw_url['host'];
		}
		else
		{
			// nicked from Zend Framework (http://framework.zend.com/),
			// Zend_Controller_Request_Http class

			$host = $_SERVER['HTTP_HOST'];

			if ( !empty($host) )
				return $host;

			$scheme = $this->getScheme();
			$name = $_SERVER['SERVER_NAME'];
			$port = $_SERVER['SERVER_PORT'];

			if ( ($scheme == self::SCHEME_HTTP && $port == 80) or ($scheme == self::SCHEME_HTTPS && $port == 443) )
				$host = $name;
			else
				$host = $name . ':' . $port;
		}

		return $host;
	}

	protected function _setupRequestUri()
	{
		$request_uri = null;

		if ( $this->_raw_url )
		{
			$request_uri = $this->_parsed_raw_url['path'];
		}
		else
		{
			// nicked from Zend Framework (http://framework.zend.com/),
			// Zend_Controller_Request_Http class

			if ( isset($_SERVER['HTTP_X_REWRITE_URL']) ) // check this first so IIS will catch
				$request_uri = $_SERVER['HTTP_X_REWRITE_URL'];
			elseif
			(
				// IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
				isset($_SERVER['IIS_WasUrlRewritten']) and $_SERVER['IIS_WasUrlRewritten'] == '1'
				and
				isset($_SERVER['UNENCODED_URL']) and $_SERVER['UNENCODED_URL'] != ''
			)
				$request_uri = $_SERVER['UNENCODED_URL'];
			elseif ( isset($_SERVER['REQUEST_URI']) )
			{
				$request_uri = $_SERVER['REQUEST_URI'];
				// Http proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
				$scheme_and_http_host = $this->getScheme() . '://' . $this->getHost();

				if ( strpos($request_uri, $scheme_and_http_host) === 0 )
					$request_uri = substr($request_uri, strlen($scheme_and_http_host));
			}
			elseif ( isset($_SERVER['ORIG_PATH_INFO']) )
			{
				// IIS 5.0, PHP as CGI
				$request_uri = $_SERVER['ORIG_PATH_INFO'];

				if ( !empty($_SERVER['QUERY_STRING']) )
					$request_uri .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		return $request_uri;
	}

	protected function _setupBaseUrl()
	{
		// nicked from Zend Framework (http://framework.zend.com/),
		// Zend_Controller_Request_Http class

		$base_url = null;

		$filename = ( isset($_SERVER['SCRIPT_FILENAME']) ) ? basename($_SERVER['SCRIPT_FILENAME']) : '';

		if ( isset($_SERVER['SCRIPT_NAME']) and basename($_SERVER['SCRIPT_NAME']) === $filename )
			$base_url = $_SERVER['SCRIPT_NAME'];
		elseif ( isset($_SERVER['PHP_SELF']) and basename($_SERVER['PHP_SELF']) === $filename )
			$base_url = $_SERVER['PHP_SELF'];
		elseif ( isset($_SERVER['ORIG_SCRIPT_NAME']) and basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename)
			$base_url = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
		else
		{
			// Backtrack up the script_filename to find the portion matching
			// php_self

			$path = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
			$file = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
			$segs = explode('/', trim($file, '/'));
			$segs = array_reverse($segs);
			$index = 0;
			$last = count($segs);

			do
			{
				$seg = $segs[$index];
				$base_url = '/' . $seg . $base_url;
				++$index;
			} while ( ($last > $index) and (false !== ($pos = strpos($path, $base_url))) and (0 != $pos));
		}

		// Does the base_url have anything in common with the request_uri?
		$request_uri = $this->getRequestUri();

		if ( 0 === strpos($request_uri, $base_url) )
		{
			// full $base_url matches
			
			return $base_url;
		}

		if ( 0 === strpos($request_uri, dirname($base_url)) )
		{
			// directory portion of $base_url matches
			return rtrim(dirname($base_url), '/');
		}

		$basename = basename($base_url);

		if (empty($basename) or !strpos($request_uri, $basename))
		{
			// no match whatsoever; set it blank
			$base_url = '';

			return $base_url;
		}

		// If using mod_rewrite or ISAPI_Rewrite strip the script filename
		// out of base_url. $pos !== 0 makes sure it is not matching a value
		// from PATH_INFO or QUERY_STRING
		if
		(
			( strlen($request_uri) >= strlen($base_url) )
			and
			(
				(false !== ($pos = strpos($request_uri, $base_url))) and ($pos !== 0)
			)
		)
		{
			$base_url = substr($request_uri, 0, $pos + strlen($base_url));
		}

		$base_url = rtrim($base_url, '/');

		return $base_url;
	}

	protected function _setupFullBaseUrl()
	{
		$host = $this->getHost();
		$base_url = $this->getBaseUrl();

		return $this->getScheme() . '://' . $host . $base_url;
	}

	protected function _setupBasePath()
	{
		$base_path = null;

		// nicked from Zend Framework (http://framework.zend.com/),
		// Zend_Controller_Request_Http class

		$filename = ( isset($_SERVER['SCRIPT_FILENAME']) ) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
		$base_url = $this->getBaseUrl();

		if (basename($base_url) === $filename)
			$base_path = dirname($base_url);
		else
			$base_path = $base_url;

		if (substr(PHP_OS, 0, 3) === 'WIN')
			$base_path = str_replace('\\', '/', $base_path);

		$base_path = rtrim($base_path, '/');

        return $base_path;
	}

	protected function _setupPathInfo()
	{
		$path_info = null;

		// nicked from Zend Framework (http://framework.zend.com/),
		// Zend_Controller_Request_Http class

		$base_url = $this->getBaseUrl();

		if ( null === ($request_uri = $this->getRequestUri()) )
			return null;

		// Remove the query string from REQUEST_URI
		if ( $pos = strpos($request_uri, '?') )
			$request_uri = substr($request_uri, 0, $pos);

		if ( (null !== $base_url) and (false === ($path_info = substr($request_uri, strlen($base_url)))) )
		{
			// If substr() returns false then PATH_INFO is set to an empty string
			$path_info = '';
		}
		elseif ( null === $base_url)
			$path_info = $request_uri;

		$path_info = rtrim($path_info, '/');

		return $path_info;
	}
}

?>