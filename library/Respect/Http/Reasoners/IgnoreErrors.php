<?php

namespace Respect\Http\Reasoners;

use Respect\Http\Reasonable;
use Respect\Http\Message;

class IgnoreErrors implements Reasonable
{
    function __construct(Message $message, $name, $shouldIgnoreErrors)
    {
        $message->context['ignore_errors'] = $shouldIgnoreErrors;
    }
}