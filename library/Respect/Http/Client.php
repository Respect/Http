<?php

namespace Respect\Http;

class Client
{
	static $globalHeaders = false;
	static function __callStatic($method, $arguments)
	{
		list($uri) = $arguments;
		$context = stream_context_create(array(
			'http' => array(
				'method' => strtoupper($method)
			)
		));
		return new Response(
			file_get_contents($uri, false, $context), 
			//we need this in order to be testable, sorry
			static::$globalHeaders ? $GLOBALS['http_response_header'] : $http_response_header
		);
	}
}