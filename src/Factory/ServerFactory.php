<?php

namespace Cydrickn\SwooleWebsocketBundle\Factory;

class ServerFactory
{
    public static function handle()
    {
        return $GLOBALS['runtimeServer'];
    }
}
