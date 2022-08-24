<?php

namespace Cydrickn\SwooleWebsocketBundle\Event;

use Cydrickn\SwooleWebsocketBundle\Websocket\Server;
use Symfony\Contracts\EventDispatcher\Event;

class CloseEvent extends Event
{
    public function __construct(public readonly Server $server, public readonly int $fd)
    {
        // Nothing
    }
}
