<?php

namespace Respect\Http\Reasoners;

use Respect\Http\Reasonable;
use Respect\Http\Message;

class FollowRedirects implements Reasonable
{
    function __construct(Message $message, $name, $numberOfRedirects)
    {
        $message->context['follow_redirects'] = true;

        if ($numberOfRedirects === 0)
            $message->context['follow_redirects'] = false;

        if ($numberOfRedirects)
            $message->context['max_redirects'] = $numberOfRedirects;
    }
}