<?php

namespace Respect\Http;

use ArrayObject;

class Request extends ArrayObject
{
	public $method, 
	       $uri, 
	       $body, 
	       $sent = false, 
	       $headersSent = array(), 
	       $content,
	       $proxy,
	       $followRedirects,
	       $protocolVersion,
	       $timeout,
	       $ignoreErrors;

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
	function content($data)
	{
		$this->content = $data;
		return $this;
	}
	function proxy($address)
	{
		$this->proxy = $address;
		return $this;
	}
	function followRedirects($numberOfRedirects)
	{
		$this->followRedirects = $numberOfRedirects;
		return $this;
	}
	function protocolVersion($versionNumber)
	{
		$this->protocolVersion = $versionNumber;
		return $this;
	}
	function timeout($seconds)
	{
		$this->timeout = $seconds;
		return $this;
	}
	function ignoreErrors($shouldIgnore)
	{
		$this->ignoreErrors = $shouldIgnore;
		return $this;
	}
	function send()
	{
		$context = array('method' => strtoupper($this->method));

		if ($this->headersSent)
			$context['header'] = implode("\r\n", $this->headersSent);

		if ($this->content)
			$context['content'] = $this->content;

		if ($this->proxy)
			$context['proxy'] = $this->proxy;

		if ($this->followRedirects === 0)
			$context['follow_redirects'] = false;

		if ($this->followRedirects)
			$context['max_redirects'] = $this->followRedirects;

		if ($this->protocolVersion)
			$context['protocol_version'] = $this->protocolVersion;
			
		if ($this->timeout)
			$context['timeout'] = $this->timeout;
			
		if ($this->ignoreErrors)
			$context['ignore_errors'] = $this->ignoreErrors;

	    $stream = fopen($this->uri, 'rb', false, stream_context_create(array('http' => $context)));
	    $this->body = stream_get_contents($stream);
	    $meta = stream_get_meta_data($stream);
		$this->exchangeArray($meta['wrapper_data']);
		$this->sent = true;
	}
}