<?php

namespace Respect\Http;

use ArrayObject;

class Client extends ArrayObject
{
	public $method, $uri, $sent = false, $body;

	static $globalHeaders = false;
	static function __callStatic($method, $arguments)
	{
		list($uri) = $arguments;
		return new static($method, $uri);
	}
	function __construct($method, $uri)
	{
		list($this->method, $this->uri) = func_get_args();
	}
	function __toString()
	{
		if (!$this->sent)
			$this->send();
		return (string) $this->body ?: '';
	}
	function send()
	{
		$context = stream_context_create(array(
			'http' => array(
				'method' => strtoupper($this->method)
			)
		));
		$this->body = file_get_contents($this->uri, false, $context); 
		$this->exchangeArray(
		    //we need this in order to be testable, sorry
			Client::$globalHeaders ? $GLOBALS['http_response_header'] : $http_response_header
		);
		$this->sent = true;
	}
}