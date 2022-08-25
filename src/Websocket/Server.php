<?php

namespace Cydrickn\SwooleWebsocketBundle\Websocket;

use Cydrickn\SwooleWebsocketBundle\Event\CloseEvent;
use Cydrickn\SwooleWebsocketBundle\Event\HandshakeEvent;
use Cydrickn\SwooleWebsocketBundle\Event\MessageEvent;
use Cydrickn\SwooleWebsocketBundle\Event\OpenEvent;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Kernel;

class Server
{
    protected const DEFAULT_OPTIONS = [
        'host' => '127.0.0.1',
        'port' => 8000,
        'mode' => 2, // SWOOLE_PROCESS
        'sock_type' => 1, // SWOOLE_SOCK_TCP
        'settings' => [],
    ];

    protected ?WebsocketServer $server;
    protected bool $initialized;
    protected array $config;
    protected bool $runtime;
    protected ?EventDispatcherInterface $eventDispatcher;

    public function __construct(array $config = [])
    {
        $this->config = array_replace_recursive(self::DEFAULT_OPTIONS, $config);
        $this->server = null;
        $this->initialized = false;
        $this->eventDispatcher = null;
    }

    public function init()
    {
        if ($this->initialized) {
            return;
        }

        $this->setServer(new WebsocketServer($this->config['host'], $this->config['port'], SWOOLE_PROCESS), true);
        $this->server->set($this->config['settings']);
    }

    public function setServer(WebsocketServer $server, bool $setInitialized = true): void
    {
        $this->server = $server;
        if ($setInitialized) {
            $this->initialized = true;
        }
    }

    public function setEvent(): void
    {
        $this->server->on('Open', function (WebsocketServer $server, Request $request) {
            if ($this->eventDispatcher === null) {
                return;
            }
            $this->eventDispatcher->dispatch(new OpenEvent($this, $request));
        });

        $this->server->on('Message', function (WebsocketServer $server, Frame $frame) {
            if ($this->eventDispatcher === null) {
                return;
            }
            $this->eventDispatcher->dispatch(new MessageEvent($this, $frame));
        });

        $this->server->on('Close', function (WebsocketServer $server, int $fd) {
            if ($this->eventDispatcher === null) {
                return;
            }
            $this->eventDispatcher->dispatch(new CloseEvent($this, $fd));
        });
    }

    public function on(string $event, callable $callable): void
    {
        $this->server->on($event, $callable);
    }

    public function start(): void
    {
        $this->server->start();
    }

    public function getServer(): WebsocketServer
    {
        return $this->server;
    }

    public function setHost(string $host): void
    {
        $this->config['host'] = $host;
    }

    public function setPort(int $port): void
    {
        $this->config['port'] = $port;
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
