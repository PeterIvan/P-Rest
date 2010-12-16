<?php

class Prest_Router_MatchedRoute implements ArrayAccess
{
	protected $_class = null;
	protected $_name = null;
	protected $_params = null;
	protected $_priority = null;
	protected $_resource = null;
	protected $_type = null;

################################################################################
# public
################################################################################

################################################################################
# Startup ######################################################################

	public function __construct( array $i_route = array() )
	{
		if ( !empty($i_route) )
		{
			foreach ( $i_route as $param => $value )
			{
				$p_name = "_$param";

				$this->$p_name = $value;
			}
		}
	}

################################################################################
# Magic methods ################################################################

	public function __set( $i_name, $i_value )
	{
		if ( $this->offsetExists($i_name) )
			$this->$i_name = $i_value;
	}

	public function __get( $i_name )
	{
		if ( $this->offsetExists($i_name) )
			return $this->$i_name;
		else
			return null;
	}

	public function __isset( $i_name )
	{
		return $this->offsetSet($i_name);
	}

	public function __unset( $i_name )
	{
		$this->offsetUnset($i_name);
	}

################################################################################

	public function nameIs( $i_name )
	{
		return ( strcmp($this->_name, $i_name) == 0 );
	}

################################################################################
# ArrayAccess ##################################################################

	public function offsetSet( $i_offset, $i_value )
	{
		$offset = $this->_normalizeOffset($i_offset);

		if ( $this->offsetExists($offset) )
			$this->$offset = $i_value;
	}

	public function offsetGet( $i_offset )
	{
		$offset = $this->_normalizeOffset($i_offset);

		if ( $this->offsetExists($offset) )
			return $this->$offset;
		else
			return null;
	}

	public function offsetExists( $i_offset )
	{
		$offset = $this->_normalizeOffset($i_offset);

		if ( isset($this->$offset) || property_exists($this, $offset) )
			return true;
		else
			return false;
	}

	public function offsetUnset( $i_offset )
	{
		$offset = $this->_normalizeOffset($i_offset);

		if ( $this->offsetExists($offset) )
			$this->$offset = null;
	}

################################################################################
# protected
################################################################################

	protected function _normalizeOffset( $i_offset )
	{
		if ( $i_offset[0] != '_' )
			$i_offset = "_$i_offset";

		return $i_offset;
	}
}

?>