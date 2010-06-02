<?php

class Prest_Dispatcher
{
	protected $_service = null;
	protected $_router = null;

################################################################################
# public
################################################################################

	public function __construct( array $i_params )
	{
		$this->_service = $i_params['service'];
		$this->_router = $i_params['router'];
	}

	public function dispatch( Prest_Request $i_request, array $i_params = null )
	{var_dump('ddd');
		if ( $i_request->isValid() )
		{
			$resource = $this->_prepareResource($i_request);

			return $resource->getRepresentation();
		}
	}

################################################################################
# protected
################################################################################

	protected function _prepareResource( Prest_Request $i_request )
	{
		$resource = null;

		$matched_route = $this->_router->getMatchedRoute($i_request->getUrl());

		if ( !$matched_route )
			throw new Prest_Exception(null, Prest_Response::NOT_FOUND);

		$resource_name = $matched_route['resource'];

		$resource_dir = $this->_service->getResourceDirectory($resource_name);

		$class = basename($resource_name);
		$file = "$resource_dir/$class.php";

		if ( is_dir($resource_dir) and is_file($file) )
		{
			require_once($file);

			$config = array
			(
				'service' => $this->_service,
				'request' => $i_request,
				'directory' => $resource_dir,
				'action_type' => $matched_route['type'],
				'action' => $matched_route['type'] . ucfirst($i_request->getMethod())
			);

			$resource = new $class($config);

			return $resource;
		}
		else
			throw new Prest_Exception(null, Prest_Response::NOT_FOUND);
	}
}

?>