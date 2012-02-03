<?php

namespace Respect\Http;

class Client
{
	static function __callStatic($method, $arguments)
	{
		global $http_response_header;
		$http_response_header = array();
		list($uri) = $arguments;
		$context = stream_context_create(array(
			'http' => array(
				'method' => strtoupper($method)
			)
		));
		return new Response(file_get_contents($uri, false, $context), $http_response_header);
	}
}