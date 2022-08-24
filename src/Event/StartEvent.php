<?php

namespace Cydrickn\SwooleWebsocketBundle\Event;

use Cydrickn\SwooleWebsocketBundle\Websocket\Server;

class StartEvent
{
    public function __construct(public readonly Server $server)
    {
    }
}
