<?php

namespace Cydrickn\SwooleWebsocketBundle\Event;

use Cydrickn\SwooleWebsocketBundle\Websocket\Server;
use Swoole\Http\Request;
use Symfony\Contracts\EventDispatcher\Event;

class OpenEvent extends Event
{
    public function __construct(public readonly Server $server, public readonly Request $request)
    {
        // Nothing
    }
}
