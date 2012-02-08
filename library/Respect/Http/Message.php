<?php

namespace Respect\Http;

use ArrayObject;
use ReflectionClass;

class Message extends ArrayObject
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
	function __call($reasonerName, $arguments)
	{
		$reasonerClass = 'Respect\\Http\\Reasoners\\'.ucfirst($reasonerName);

		if (!class_exists($reasonerClass))
			$reasonerClass = 'Respect\\Http\\Reasoners\\Header';

		$reasonerReflection = new ReflectionClass($reasonerClass);
		array_unshift($arguments, $this, $reasonerName);
		$this->reasoners[] = $reasonerReflection->newInstanceArgs($arguments);

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