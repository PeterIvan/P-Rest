<?php

class Prest_Router
{
	protected $_service = null;

	protected $_routes = array();
	protected $_matched_route = null;

	public function __construct( $i_config = array() )
	{
		if ( isset($i_config['service']) )
			$this->_service = $i_config['service'];
		if ( isset($i_config['routes']) )
			$this->_routes = $i_config['routes'];
	}

	public function setRoutes( array $i_routes )
	{
		$this->_routes = $i_routes;
	}

	public function getMatchedRoute()
	{
		if ( !$this->_matched_route )
			$this->_matched_route = $this->_matchRoute();

		return $this->_matched_route;
	}

	protected function _matchRoute()
	{
		$matched_route = null;
		$route_map = $this->_createRouteMap();

		$path_info = ltrim($this->_service->getRequest()->getUrl()->getPathInfo(), '/');

		foreach ( $route_map as $route_index => $routes )
		{
			$route_params = $routes['params'];

			unset($routes['params']);

			foreach ( $routes as $route_type => $route )
			{
				$matched_params = array();

				if ( preg_match_all("/^{$route['pattern']}$/i", $path_info, $matched_params) === 1 )
				{
					$params = array();

					foreach ( $route_params as $i => $p )
						$params[$p] = $matched_params[$i + 1];

					if ( $route_type == 'identity' )
						$params['identity'] = end($matched_params);

					$matched_route = array
					(
						'resource' => $this->_routes[$route_index]['resource'],
						'type' => $route_type,
						'params' => $params
					);

					break 2;
				}
			}
		}

		return $matched_route;
	}

	protected function _createRouteMap()
	{
		$route_map = array();

		foreach ( $this->_routes as $route_index => $route )
		{
			$map_entry = array();


			$list_route = null;

			$route_pattern = $route['route'];

			$param_pos = strpos($route_pattern, ':');

			if ( $param_pos !== false  ) // if route contains params
			{
				do
				{
					$param_name = substr($route_pattern, $param_pos + 1, (strpos($route_pattern, '/', $param_pos) - 1) - $param_pos);

					if ( isset($route['params'], $route['params'][$param_name]) )
					{
						$route_pattern = str_replace(':' . $param_name, '(' . $route['params'][$param_name] . ')', $route_pattern);
						$map_entry['params'][] = $param_name;
					}
					else
						die("param $param_name is not defined"); // TODO: throw

					$param_pos = strpos($route_pattern, ':');
				}
				while ( $param_pos !== false );
			}

			$route_pattern = str_replace('/', '\/', $route_pattern);

			$map_entry['index']['pattern'] = $route_pattern;

			# identity #########

			if ( isset($route['identity']) and $route['identity'] !== false )
				$map_entry['identity']['pattern'] = $route_pattern . '\/(' . $route['identity'] . ')';

			#################

			$route_map[$route_index] = $map_entry;
		}

		return $route_map;
	}
}

?>