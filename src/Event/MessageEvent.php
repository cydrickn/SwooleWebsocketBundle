<?php

namespace Cydrickn\SwooleWebsocketBundle\Event;

use Cydrickn\SwooleWebsocketBundle\Websocket\Server;
use Swoole\WebSocket\Frame;

class MessageEvent
{
    public function __construct(public readonly Server $server, Frame $frame)
    {
    }
}
