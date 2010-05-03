<?php

class Prest_Resource
{
	protected $_service = null;

	protected $_request = null;
	protected $_response = null;

	protected $_params = null;

	protected $_directory = null;

	protected $_media_types = null;
	protected $_representation = null;

	protected $_media_type = null;
	protected $_language = null;

	protected $_security = null;

	public function __construct( array $i_config )
	{
		# set properties ###############################################

		$this->_service = $i_config['service'];
		$this->_request = $this->_service->getRequest();
		$this->_response = $this->_service->getResponse();

		$this->_directory = $i_config['directory'];

		$this->_setup();

		# run init method if present ###################################

		if ( method_exists($this, 'init') )
			$this->init();
	}

	public function getDirectory() { return $this->_directory; }

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

	public function getMediaTypes() { return $this->_media_types; }

	public function getRepresentation() { return $this->_representation; }

	public function indexOptions()
	{
	}

	public function identityOptions()
	{
	}

	public function getSecurityConfig()	{ return $this->_security; }

	public function validate( $i_action )
	{
		if ( $this->_authorized_personnel_only )
		{
			$this->_service->authenticate();
		}

		return true;
	}

	public function authorize()
	{
		return true;
	}

	protected function _setup()
	{
		$request = $this->_service->getRequest();

		if ( $request->isGet() )
		{
			$this->_setupMediaTypes();
			$this->_setupRepresentation();
		}
	}

	protected function _setupMediaTypes()
	{
		$route = $this->_service->getRouter()->getMatchedRoute();
		$directory = "{$this->_directory}/representations/{$route['type']}";

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

			if ( !empty($media_types) )
				$this->_media_types = $media_types;
		}
	}

	protected function _setupRepresentation()
	{
		$media_type = $this->_selectBestMediaType();
		$language = $this->_selectBestLanguage();
		$template = $this->_selectRepresentationTemplate($media_type);

		$config = array
		(
			'service' => $this->_service,
			'resource' => $this,
			'media_type' => $media_type,
			'language' => $language,
			'template' => $template
		);

		$this->_representation = new Prest_Representation($config);
	}

	protected function _selectBestMediaType()
	{
		if ( !$this->_media_types )
			die('No media types are supported by this resource.');

		$requested = $this->_request->getHeaders()->getAccept();
		$default = $this->_service->getDefaultMediaType();

		$selected_media_type = null;

		foreach ( $this->_media_types as $media_type )
		{
			if ( in_array($media_type, $requested) )
			{
				$selected_media_type = $media_type;
				break;
			}
		}

		if ( !$selected_media_type )
		{
			echo 'media type not selected.';// TODO:
		}

		return $selected_media_type;
	}

	protected function _selectBestLanguage()
	{
		$supported = $this->_service->getSupportedLanguages();
		$requested = $this->_request->getHeaders()->getAcceptLanguage();
		$default = $this->_service->getDefaultLanguage();

		$selected_language = null;

		foreach ( $supported as $language )
		{
			if ( in_array($language, $requested) )
			{
				$selected_language = $language;
				break;
			}
		}

		if ( !$selected_language )
		{
			// TODO:
		}

		return $selected_language;
	}

	protected function _selectRepresentationTemplate( $i_media_type )
	{
		$selected_template = null;
		$route = $this->_service->getRouter()->getMatchedRoute();

		if ( $i_media_type )
		{
			$file_name = str_replace('/', '_', $i_media_type) . '.phtml';
			$template = "{$this->_directory}/representations/{$route['type']}/$file_name";

			if ( is_file($template) )
				$selected_template = $template;
		}

		return $selected_template;
	}
}

?>