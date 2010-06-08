<?php

class Prest_Resource
{
	protected $_service = null;
	protected $_request = null;

	protected $_directory = null;
	protected $_action = null;
	protected $_action_type = null;

	protected $_params = null;

	protected $_media_types = null;
	protected $_representation = null;

	protected $_default_media_type = null;

################################################################################
# public
################################################################################

	public function __construct( array $i_params )
	{
		# set properties #######################################################

		$this->_service = $i_params['service'];
		$this->_request = $i_params['request'];

		$this->_directory = $i_params['directory'];
		$this->_action_type = $i_params['action_type'];
		$this->_action = $i_params['action'];

		if ( isset($i_params['route_params']) )
			$this->_params = $i_params['route_params'];

		$this->_setup();
		$this->_validate();
	}

	public function getDirectory() { return $this->_directory; }

	############################################################################
	# params ###################################################################

	public function getParam( $i_param )
	{
		if ( !$this->_params )
			$this->getParams();

		if ( isset($this->_params[$i_param]) )
			return $this->_params[$i_param];

		return null;
	}

	public function getParams()
	{
		if ( !$this->_params )
		{
			$matched_route = $this->_service->getRouter()->getMatchedRoute();

			$request_params = $this->_request->getParams();
			$route_params = $matched_route['params'];

			foreach ( $route_params as $rp => $v )
				$request_params[$rp] = $v;

			$this->_params = $request_params;
		}

		return $this->_params;
	}

	############################################################################

	public function getMediaTypes() { return $this->_media_types; }
	public function getDefaultMediaType() { return $this->_default_media_type; }
	public function getActionType() { return$this->_action_type; }


	############################################################################
	# representation ###########################################################

	public function getRepresentation( $i_action = null )
	{
		$response = null;

		$action = $i_action ? $i_action : $this->_action;

		$this->_setupRepresentation();

		$this->$action();

		return $this->_representation;
	}

################################################################################
# protected
################################################################################

	############################################################################
	# setup ####################################################################

	protected function _setup()
	{
		$this->_setupMediaTypes();
	}

	protected function _setupMediaTypes()
	{
		$directory = "{$this->_directory}/representations/{$this->_action_type}";

		if ( is_dir($directory) )
		{
			$media_types = array();
			$d = new DirectoryIterator($directory);

			foreach ( $d as $item )
			{
				if ( $item->isDot() )
					continue;

				$file_name = $item->getFilename();

				if ( $item->isFile() and strpos($file_name, '.phtml') !== false )
				{
					$media_type = str_replace('_', '/', substr($file_name, 0, -6));

					$media_types[] = $media_type;
				}
			}

			$this->_media_types = $media_types;
		}
	}

	protected function _setupRepresentation()
	{
		$representation_params = array
		(
			'service' => $this->_service,
			'request' => $this->_request,
			'resource' => $this,
		);

		$this->_representation = new Prest_Representation($representation_params);
	}

	############################################################################
	# validation ###############################################################

	protected function _validate()
	{
		$this->_checkAction();
		$this->_checkMediaType();
		$this->_checkLanguage();
	}

	protected function _checkAction()
	{
		if ( !method_exists($this, $this->_action) )
			throw new Prest_Exception(null, Prest_Response::METHOD_NOT_ALLOWED);
	}

	protected function _checkMediaType()
	{
		if ( empty($this->_media_types) )
			throw new Exception('No media types are supported by this resource.');

		########################################################################
		# input ################################################################

		if ( $this->_request->isPost() or $this->_request->isPut() or $this->_request->isDelete() )
		{
			$is_input_media_type_supported = false;

			$input_media_type = $this->_request->getHeaders()->getContentType();

			if ( in_array($input_media_type['media_type'], $this->_media_types) )
			{
				$is_input_media_type_supported = true;
			}

			if ( !$is_input_media_type_supported )
				throw new Prest_Exception(null, Prest_Response::UNSUPPORTED_MEDIA_TYPE);
		}

		########################################################################
		# output ###############################################################

		$is_output_media_type_supported = false;

		$output_media_types = $this->_request->getHeaders()->getAccept();

		foreach ( $output_media_types as $output_mt )
		{
			if ( $this->_default_media_type and strpos($output_mt, '*') !== 'false' )
			{
				$pattern = str_replace('*', '.*', $output_mt);
				$pattern = str_replace('/', '\/', $pattern);
				$pattern = "/^$pattern\$/u";

				foreach ( $this->_media_types as $supported_mt )
				{
					if ( preg_match($pattern, $supported_mt) === 1 )
					{
						$is_media_type_supported = true;
						break 2;
					}
				}
			}
			elseif ( in_array($output_mt, $this->_media_types) )
			{
				$is_output_media_type_supported = true;

				break;
			}
		}

		if ( !$is_output_media_type_supported )
			throw new Prest_Exception(null, Prest_Response::NOT_ACCEPTABLE);
		// TODO: generate response body
	}

	protected function _checkLanguage()
	{
		// TODO: support input output languages

		$is_language_supported = false;

		$requested_languages = $this->_request->getHeaders()->getAcceptLanguage();
		$supported_languages = $this->_service->getSupportedLanguages();

		foreach ( $requested_languages as $requested_l )
		{
			if ( in_array($requested_l, $supported_languages) )
				$is_language_supported = true;
		}

		if ( !$is_language_supported )
			throw new Prest_Exception(null, Prest_Response::NOT_ACCEPTABLE);
		// TODO: generate response body
	}

	protected function _checkAuthentication()
	{
		$auth_result = $this->_authenticate();
	}

	protected function _checkAuthorization()
	{
		$auth_result = $this->_authorize();
	}

	protected function _authenticate()
	{
		return true;
	}

	protected function _authorize()
	{
	

		return true;
	}
}

?>