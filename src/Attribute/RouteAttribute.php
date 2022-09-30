<?php

namespace Cydrickn\SwooleWebsocketBundle\Attribute;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class RouteAttribute
{
    public function __construct(public readonly string $path)
    {
    }
}
