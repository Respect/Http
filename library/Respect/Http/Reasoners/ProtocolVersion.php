<?php

namespace Respect\Http\Reasoners;

use Respect\Http\Reasonable;
use Respect\Http\Message;

class ProtocolVersion implements Reasonable
{
	function __construct(Message $message, $name, $protocolVersion)
	{
		$message->context['protocol_version'] = $protocolVersion;
	}
}