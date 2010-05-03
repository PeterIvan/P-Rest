<?php

class Prest_Auth_Result extends Zend_Auth_Result
{
	const FAILURE_AUTH_HEADER_NOT_PRESENT = -10;

################################################################################
# public
################################################################################

	public function __construct( $i_code, $i_identity, array $i_messages = array() )
	{
		parent::__construct($i_code, $i_identity, $i_messages);

		$this->_code = $i_code;
	}
}

?>