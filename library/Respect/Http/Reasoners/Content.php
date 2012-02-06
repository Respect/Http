<?php

namespace Respect\Http\Reasoners;

use Respect\Http\Reasonable;
use Respect\Http\Message;

class Content implements Reasonable
{
	function __construct(Message $message, $name, $content)
	{
		$message->context['content'] = $content;
	}
}