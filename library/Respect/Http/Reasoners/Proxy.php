<?php

namespace Respect\Http\Reasoners;

use Respect\Http\Reasonable;
use Respect\Http\Message;

class Proxy implements Reasonable
{
	function __construct(Message $message, $name, $proxy)
	{
		$message->context['proxy'] = $proxy;
	}
}