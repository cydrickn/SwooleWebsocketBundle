<?php

namespace Cydrickn\SwooleWebsocketBundle\Event;

use Cydrickn\SwooleWebsocketBundle\Websocket\Server;
use Swoole\WebSocket\Frame;
use Symfony\Contracts\EventDispatcher\Event;

class MessageEvent extends Event
{
    public function __construct(public readonly Server $server, public readonly Frame $frame)
    {
        // Nothing
    }
}
