<?php

class Prest_Http_Response_ClientError extends Prest_Http_Response_Abstract
{


	public function code402()
	{

	}

	public function code403()
	{

	}

	public function code404( $i_params )
	{
		$response = $this->_service->getResponse();
		$headers = $response->getHeaders();

		$headers->clearAll()
				->setResponseCode(404);

		$response->send();
	}

	public function code405()
	{

	}

	public function code406()
	{

	}

	public function code407()
	{

	}

	public function code408()
	{

	}

	public function code409()
	{

	}

	public function code410()
	{

	}

	public function code411()
	{

	}

	public function code412()
	{

	}

	public function code413()
	{

	}

	public function code414()
	{

	}

	public function code415()
	{

	}

	public function code416()
	{

	}

	public function code417()
	{

	}
}

?>