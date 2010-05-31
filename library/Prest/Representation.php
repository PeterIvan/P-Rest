<?php

class Prest_Representation
{
	protected $_service = null;
	protected $_resource = null;

	protected $_media_type = null;
	protected $_language = null;
	protected $_template = null;

	protected $_is_file = false;

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

################################################################################

	public function getMediaType() { return $this->_media_type; }
	public function getLanguage() { return $this->_language; }

	public function setIsFile( $i_bool )
	{
		$this->_is_file = (bool)$i_bool;

		return $this;
	}

################################################################################

	public function render()
	{
		if ( !$this->_is_file )
		{
			ob_start();

			$template = $this->_selectTemplate();

			require_once($template);

			$buffer = ob_get_contents();

			ob_end_clean();

			return $buffer;
		}
		else
			return '';
	}

################################################################################
# protected
################################################################################

	protected function _setup()
	{
		$this->_media_type = $this->_selectMediaType();
		$this->_language = $this->_selectLanguage();
	}

	protected function _selectMediaType()
	{
		$selected_media_type = null;

		$available_media_types = $this->_resource->getMediaTypes();
		$requested_media_types = $this->_resource->getHeader('Accept');

		// TODO: weighted selection

		foreach ( $requested_media_types as $requested_mt )
		{
			if ( in_array($requested_mt, $available_media_types) )
			{
				$selected_media_type = $requested_mt;

				break;
			}
		}

		return $selected_media_type;
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

		return $selected_language;
	}

	//protected function
}

?>