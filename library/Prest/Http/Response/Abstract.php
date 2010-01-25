<?php

class Prest_Http_Response_Abstract
{
	protected $_service = null;

	public function __construct( array $i_config = array() )
	{
		if ( isset($i_config['service']) )
			$this->_service = $i_config['service'];
	}

	public function getService() { return $this->_service; }

	public function setService( Prest_Service $i_service )
	{
		$this->__service = $i_service;
		return $this;
	}
}

?>