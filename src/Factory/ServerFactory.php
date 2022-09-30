<?php

namespace Cydrickn\SwooleWebsocketBundle\Factory;

class ServerFactory
{
    public function getServer()
    {
        return $GLOBALS['runtimeServer'];
    }
}
