<?php

class Prest_Resource_ActionConfig
{
	const ACCEPT_CONTENT = 'accept_content';
	const SUPPORTED_MEDIA_TYPES = 'supported_media_types';
	const DEFAULT_MEDIA_TYPE = 'default_media_type';

	const REQUIRED_INPUT_HEADERS = 'required_input_headers';
	
	private static $_instance;
	
	public function getDefaultConfig()
	{
		return array
		(
			'indexGet' => $this->_getDefaultIndexGetConfig(),
			'indexPut' => $this->_getDefaultIndexPutConfig(),
			'indexDelete' => $this->_getDefaultIndexDeleteConfig(),
			'indexPost' => $this->_getDefaultIndexPostConfig(),
			'indexHead' => $this->_getDefaultIndexHeadConfig(),
			'indexOptions' => $this->_getDefaultIndexOptionsConfig(),
			
			'identityGet' => $this->_getDefaultIdentityGetConfig(),
			'identityPut' => $this->_getDefaultIdentityPutConfig(),
			'identityDelete' => $this->_getDefaultIdentityDeleteConfig(),
			'identityPost' => $this->_getDefaultIdentityPostConfig(),
			'identityHead' => $this->_getDefaultIdentityHeadConfig(),
			'identityOptions' => $this->_getDefaultIdentityOptionsConfig(),
			
			'localizationGet' => $this->_getDefaultLocalizationGetConfig(),
			'localizationPut' => $this->_getDefaultLocalizationPutConfig(),
			'localizationDelete' => $this->_getDefaultLocalizationDeleteConfig(),
			'localizationPost' => $this->_getDefaultLocalizationPostConfig(),
			'localizationHead' => $this->_getDefaultLocalizationHeadConfig(),
			'localizationOptions' => $this->_getDefaultLocalizationOptionsConfig()
		);
	}
	
	############################################################################
	# index default config #####################################################
	
	protected function _getDefaultIndexGetConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	protected function _getDefaultIndexPutConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultIndexDeleteConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultIndexPostConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultIndexHeadConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	protected function _getDefaultIndexOptionsConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	############################################################################
	# identity default config ##################################################
	
	protected function _getDefaultIdentityGetConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	protected function _getDefaultIdentityPutConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultIdentityDeleteConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultIdentityPostConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultIdentityHeadConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	protected function _getDefaultIdentityOptionsConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	############################################################################
	# localization default config ##############################################
	
	protected function _getDefaultLocalizationGetConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	protected function _getDefaultLocalizationPutConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultLocalizationDeleteConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultLocalizationPostConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => true
		);
	}
	
	protected function _getDefaultLocalizationHeadConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	protected function _getDefaultLocalizationOptionsConfig()
	{
		return array
		(
			self::ACCEPT_CONTENT => false
		);
	}
	
	public static function getInstance()
	{
		if ( !self::$_instance )
			self::$_instance = new self;
		
		return self::$_instance;
	}
}

?>