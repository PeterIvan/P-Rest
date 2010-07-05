<?php

class Prest_Representation
{
	protected $_service = null;
	protected $_resource = null;

	protected $_headers = array();
	protected $_media_type = null;
	protected $_language = null;
	protected $_template = null;

	protected $_has_template = true;

	protected $_is_file = false;
	protected $_file = true;

################################################################################
# public
################################################################################

	public function __construct( array $i_config = null )
	{
		$this->_service = $i_config['service'];
		$this->_request = $i_config['request'];
		$this->_resource = $i_config['resource'];

		$this->_setup();
	}

	public function __toString() { return $this->render(); }

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
		$header_name = $this->_normalizeHeaderName($i_header_name);

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

	public function getHeaders() { return $this->_headers; }

################################################################################

	public function getMediaType() { return $this->_media_type; }
	public function getLanguage() { return $this->_language; }

	public function isFile() { return $this->_is_file; }

	public function setIsFile( $i_bool )
	{
		$this->_is_file = (bool)$i_bool;

		return $this;
	}

	public function setFile( $i_file )
	{
		$this->_file = $i_file;
	}

	public function setHasTemplate( $i_bool )
	{
		$this->_has_template = (bool)$i_bool;

		return $this;
	}

################################################################################

	public function render()
	{
		if ( !$this->_is_file )
		{
			if ( $this->_has_template )
			{
				ob_start();

				require_once($this->_template);

				$buffer = ob_get_contents();

				ob_end_clean();

				return $buffer;
			}
		}
		else
		{
			if ( is_file($this->_file) )
				readfile($this->_file);
		}

		return '';
	}

################################################################################
# protected
################################################################################

	protected function _setup()
	{
		$this->_selectMediaType();
		$this->_selectLanguage();
		$this->_selectTemplate();

		$this->addHeader('Content-Type', $this->_media_type);
		$this->addHeader('Content-Language', $this->_language);
	}

	protected function _selectMediaType()
	{
		$selected_media_type = null;

		$available_media_types = $this->_resource->getMediaTypes();
		$requested_media_types = $this->_request->getHeaders()->getAccept();

		// TODO: weighted selection

		foreach ( $requested_media_types as $requested_mt )
		{
			if ( in_array($requested_mt, $available_media_types) )
			{
				$selected_media_type = $requested_mt;

				break;
			}
		}

		$this->_media_type = $selected_media_type;

		if ( empty($this->_media_type) )
			$this->_media_type = $this->_resource->getDefaultOutputMediaType();
	}

	protected function _selectLanguage()
	{
		$selected_language = null;

		$supported_languages = $this->_service->getSupportedLanguages();
		$requested_languages = $this->_request->getHeaders()->getAcceptLanguage();

		// TODO: weighted selection

		foreach ( $requested_languages as $requested_language )
		{
			if ( in_array($requested_language, $supported_languages) )
			{
				$selected_language = $requested_language;

				break;
			}
		}

		$this->_language = $selected_language;
	}

	protected function _selectTemplate()
	{
		$selected_template = null;
		$route_type = $this->_resource->getActionType();

		$file_name = str_replace('/', '_', $this->_media_type) . '.phtml';
		$template = "{$this->_resource->getDirectory()}/representations/$route_type/$file_name";

		if ( is_file($template) )
			$this->_template = $template;
	}

	protected function _normalizeHeaderName( $i_header )
	{
		$filtered = str_replace(array('-', '_'), ' ', (string)$i_header);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);

		return $filtered;
	}
}

?>