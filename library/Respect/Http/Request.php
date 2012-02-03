<?php

namespace Respect\Http;

use ArrayObject;

class Request extends ArrayObject
{
	public $method, 
	       $uri, 
	       $body, 
	       $sent = false, 
	       $context = array('method' => '', 'header' => ''),
	       $meta,
	       $stream;

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
		$this->context['header'] .= "$headerName: $headerValue\r\n";
		return $this;
	}
	function __construct($method, $uri)
	{
		$this->method = $this->context['method'] = strtoupper($method);
		$this->uri = $uri;
	}
	function __toString()
	{
		if (!$this->sent)
			$this->send();
		return (string) $this->body ?: '';
	}
	function content($data)
	{
		$this->context['content'] = $data;
		return $this;
	}
	function proxy($address)
	{
		$this->context['proxy'] = $address;
		return $this;
	}
	function followRedirects($numberOfRedirects)
	{
		$this->context['follow_redirects'] = true;

		if ($numberOfRedirects === 0)
			$this->context['follow_redirects'] = false;

		if ($numberOfRedirects)
			$this->context['max_redirects'] = $numberOfRedirects;

		return $this;
	}
	function protocolVersion($versionNumber)
	{
		$this->context['protocol_version'] = $versionNumber;
		return $this;
	}
	function timeout($seconds)
	{
		$this->context['timeout'] = $seconds;
		return $this;
	}
	function ignoreErrors($shouldIgnore)
	{
		$this->context['ignore_errors'] = $shouldIgnore;
		return $this;
	}
	function send()
	{
	    $this->stream = fopen(
		    $this->uri, 'rb', false, stream_context_create(array('http' => $this->context))
	    );
	    $this->body = stream_get_contents($this->stream);
	    $this->meta = stream_get_meta_data($this->stream);
		$this->exchangeArray($this->meta['wrapper_data']);
		$this->sent = true;
	}
}