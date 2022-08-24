<?php

namespace Cydrickn\SwooleWebsocketBundle\Websocket;

use Cydrickn\SwooleWebsocketBundle\Event\StartEvent;
use Swoole\WebSocket\Server as WebsocketServer;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Server
{
    protected ?WebsocketServer $server = null;
    protected bool $initialized = false;
    protected array $config;

    public function __construct(private EventDispatcher $eventDispatcher, array $config)
    {
        $this->config = ['host' => '127.0.0.1', 'port' => 8080, ...$config];
    }

    public function init()
    {
        if ($this->initialized) {
            return;
        }

        $this->setServer(new WebsocketServer($this->config['host'], $this->config['port'], SWOOLE_PROCESS), true);
    }

    public function setServer(WebsocketServer $server, bool $setInitialized = true): void
    {
        $this->server = $server;
        if ($setInitialized) {
            $this->initialized = true;
        }
    }

    public function beforeStart(): void
    {
        $this->server->on('Start', function () {
            $this->eventDispatcher->dispatch(new StartEvent($this));
        });
    }

    public function start(): void
    {
        $this->beforeStart();
        $this->server->start();
    }

    public function getServer(): WebsocketServer
    {
        return $this->server;
    }
}
