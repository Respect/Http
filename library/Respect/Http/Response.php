<?php

namespace Respect\Http;

use ArrayObject;

class Response extends ArrayObject
{
    public $body = '';
	function __construct($body, array $headers)
	{
		$this->body = $body;
		$this->exchangeArray($headers);
	}
	function __toString()
	{
		return $this->body;
	}
}