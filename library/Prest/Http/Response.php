<?php

class Prest_Http_Response
{
	protected $_headers = null;
	protected $_body = null;

	public function __construct()
	{
		$this->_setup();
	}

	public function setResponseCode( $i_code )
	{
		$this->_headers->setResponseCode($i_code);
	}

	public function setBody( $i_body )
	{
		$this->_body = $i_body;

		return $this;
	}

	public function sendResponse()
	{
		if ( $this->_body instanceof Prest_Representation )
		{
			# add Content-Type header ###########
			$media_type = $this->_body->getMediaType();

			if ( $media_type )
				$this->_headers->add('Content-Type', $media_type, true);

			# add Content-Language header ###########
			$language = $this->_body->getLanguage();

			if ( $language )
				$this->_headers->add('Content-Language', $language, true);
		}

		$this->_headers->send();

		echo $this->_body;
	}

	protected function _setup()
	{
		$this->_headers = new Prest_Http_Response_Headers();
	}
}

?>