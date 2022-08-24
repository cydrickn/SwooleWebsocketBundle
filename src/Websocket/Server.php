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
    protected ?WebsocketServer $server;
    protected bool $initialized;
    protected array $config;
    protected bool $runtime;
    protected ?Kernel $kernel;

    public function __construct(private EventDispatcherInterface $eventDispatcher, array $config = [])
    {
        $this->config = ['host' => '127.0.0.1', 'port' => 8080, ...$config];
        $this->server = null;
        $this->initialized = false;
        $this->runtime = false;
        $this->kernel = null;
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
        $this->server->on('Open', function (WebsocketServer $server, Request $request) {
            $eventDispatcher = $this->eventDispatcher;
            if ($this->runtime) {
                $this->kernel->boot();
                $eventDispatcher = $this->kernel->getContainer()->get(EventDispatcherInterface::class);
            }
            $eventDispatcher->dispatch(new OpenEvent($this, $request), 'websocket:open');
        });

        $this->server->on('Message', function (WebsocketServer $server, Frame $frame) {
            $eventDispatcher = $this->eventDispatcher;
            if ($this->runtime) {
                $this->kernel->boot();
                $eventDispatcher = $this->kernel->getContainer()->get(EventDispatcherInterface::class);
            }
            $eventDispatcher->dispatch(new MessageEvent($this, $frame), 'websocket:message');
        });

        $this->server->on('Close', function (WebsocketServer $server, int $fd) {
            $eventDispatcher = $this->eventDispatcher;
            if ($this->runtime) {
                $this->kernel->boot();
                $eventDispatcher = $this->kernel->getContainer()->get(EventDispatcherInterface::class);
            }
            $eventDispatcher->dispatch(new CloseEvent($this, $fd), 'websocket:close');
        });
    }

    public function on(string $event, callable $callable): void
    {
        $this->server->on($event, $callable);
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

    public function setHost(string $host): void
    {
        $this->config['host'] = $host;
    }

    public function setPort(int $port): void
    {
        $this->config['port'] = $port;
    }

    public function setRuntime(bool $runtime): void
    {
        $this->runtime = $runtime;
    }

    public function setKernel(Kernel $kernel): void
    {
        $this->kernel = $kernel;
    }
}
