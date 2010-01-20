<?php

class Prest_Representation
{
	protected $_service = null;
	protected $_resource = null;

	protected $_media_type = null;
	protected $_language = null;
	protected $_template = null;

	public function __construct( array $i_config = null )
	{
		$this->_service = $i_config['service'];
		$this->_resource = $i_config['resource'];
		$this->_media_type = $i_config['media_type'];
		$this->_language = $i_config['language'];
		$this->_template = $i_config['template'];
	}

	public function __toString() { return $this->render(); }

	public function getMediaType() { return $this->_media_type; }
	public function getLanguage() { return $this->_language; }

	public function render()
	{
		ob_start();

		require_once($this->_template);

		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}
}

?>