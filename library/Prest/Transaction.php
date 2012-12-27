<?php

class Prest_Transaction
{
	protected $_is_started = false;
	protected $_is_finished = false;

################################################################################
# public
################################################################################

	public function isStarted() { return $this->_is_started; }

	public function begin()
	{
		$this->_is_started = true;
	}

	public function commit( $i_save_point = null )
	{
	}

	public function rollBack( $i_save_point = null )
	{
	}

	public function finish()
	{
		if ( !$this->_is_finished )
		{
			$this->commit();

			$this->_is_finished = true;
		}
	}
}

?>