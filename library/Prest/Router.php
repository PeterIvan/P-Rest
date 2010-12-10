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

		return $this;
	}

	public function getMatchedRoute( Prest_Request_Http_Url $i_url )
	{
		$path_info = ltrim($i_url->getPathInfo(), '/');

		$matched_route = $this->_matchRoute($path_info);

		return $matched_route;
	}

	protected function _matchRoute( $i_path_info )
	{
		$path_info = $i_path_info;

		$route_map = $this->_createRouteMap();

		$matched_route = null;

		foreach ( $route_map as $route_index => $routes )
		{
			$route_params = array();

			if ( isset($routes['params']) )
			{
				$route_params = $routes['params'];

				unset($routes['params']);
			}

			foreach ( $routes as $route_type => $route )
			{
				$matched_params = array();

				if ( preg_match_all("/^{$route['pattern']}$/i", $path_info, $matched_params) === 1 )
				{
					$params = array();

					if ( !empty($route_params) )
					{
						foreach ( $route_params as $i => $p )
						{
							$params[$p['name']] = array
							(
								'value' => $matched_params[$i + 1][0],
								'class' => $p['class']
							);
						}
					}

					if ( $route_type == 'identity' )
					{
						$identity = end($matched_params);

						if ( is_array($identity) )
							$params['identity']['value'] = $identity[0];
						else
							$params['identity']['value'] = $identity;
					}

					$route = array
					(
						'resource' => $this->_routes[$route_index]['resource'],
						'type' => $route_type,
						'params' => $params,
						'priority' => isset($this->_routes[$route_index]['priority'])
							? $this->_routes[$route_index]['priority'] : 0
					);

					if ( isset($this->_routes[$route_index]['class']) )
						$route['class'] = $this->_routes[$route_index]['class'];

					############################################################
					# resolve duplicate routes by priority #####################

					if ( !$matched_route )
						$matched_route = $route;
					else
					{
						if ( $matched_route['priority'] < $route['priority'] )
							$matched_route = $route;
					}
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
					if ( strpos($route_pattern, '/', $param_pos) !== false )
						$param_name = substr($route_pattern, $param_pos + 1, (strpos($route_pattern, '/', $param_pos) - 1) - $param_pos);
					else
						$param_name = substr($route_pattern, $param_pos + 1);

					if ( isset($route['params'], $route['params'][$param_name]) )
					{
						$param_regex = null;
						$param_class = null;

						if ( is_array($route['params'][$param_name]) )
						{
							$param_regex = $route['params'][$param_name]['regex'];
							
							if ( isset($route['params'][$param_name]['class']) )
								$param_class = $route['params'][$param_name]['class'];
						}
						else
							$param_regex = $route['params'][$param_name];

						$route_pattern = str_replace(":$param_name", "($param_regex)", $route_pattern);

						$map_entry['params'][] = array
						(
							'name' => $param_name,
							'class' => $param_class
						);
					}
					else
						throw new Prest_Exception("Param '$param_name' is not defined in route {$route['route']}");

					$param_pos = strpos($route_pattern, ':');
				}
				while ( $param_pos !== false );
			}

			$route_pattern = str_replace('/', '\/', $route_pattern);

			$map_entry['index']['pattern'] = $route_pattern;

			####################################################################
			# identity #########################################################

			if ( isset($route['identity']) and $route['identity'] !== false )
				$map_entry['identity']['pattern'] = $route_pattern . '\/(' . $route['identity'] . ')';

			####################################################################

			$route_map[$route_index] = $map_entry;
		}

		return $route_map;
	}
}

?>