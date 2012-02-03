<?php

namespace Respect\Http;

use ArrayObject;

class Request extends ArrayObject
{
	public $method, $uri, $sent = false, $body, $headersSent = array(), $bodySent;

	static $globalHeaders = false;
	static function __callStatic($method, $arguments)
	{
		list($uri) = $arguments;
		return new static($method, $uri);
	}
	function __call($headerName, $arguments)
	{
		list($headerValue) = $arguments;
		$headerName = ucfirst(preg_replace('/[A-Z0-9]+/', '-$0', $headerName));
		$this->headersSent[] = "$headerName: $headerValue";
		return $this;
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
	function body($data)
	{
		$this->bodySent = $data;
		return $this;
	}
	function send()
	{
		$context = array('method' => strtoupper($this->method));

		if ($this->headersSent)
			$context['header'] = implode("\r\n", $this->headersSent);

		if ($this->bodySent)
			$context['content'] = $this->bodySent;

		$this->body = file_get_contents(
			$this->uri, false, stream_context_create(array('http' => $context))
		); 
		$this->exchangeArray(
		    //we need this in order to be testable, sorry
			static::$globalHeaders ? $GLOBALS['http_response_header'] : $http_response_header
		);
		$this->sent = true;
	}
}