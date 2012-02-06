<?php

namespace Respect\Http\Reasoners;

use Respect\Http\Reasonable;
use Respect\Http\Message;

class Header implements Reasonable
{
	function __construct(Message $message, $name, $value)
	{
		$name = ucfirst(preg_replace('/[A-Z0-9]+/', '-$0', $name));
		$message->context['header'] .= "$name: $value\r\n";
	}
}