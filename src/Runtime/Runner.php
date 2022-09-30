<?php

namespace Cydrickn\SwooleWebsocketBundle\Runtime;

use App\Controller\TestController;
use Cydrickn\PHPWatcher\Watcher;
use Cydrickn\SwooleWebsocketBundle\Websocket;
use Cydrickn\SocketIO;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server as WebSocketServer;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Runtime\RunnerInterface;

class Runner implements RunnerInterface
{
    private ConsoleOutput $output;

    public function __construct(private KernelInterface $kernel, private array $options)
    {
        $this->output = new ConsoleOutput();
    }

    public function run(): int
    {
        if ($this->options['socketio'] && class_exists(SocketIO\Server::class)) {
            $server = $this->socketIORun();
        } else {
            $server = $this->defaultRun();
        }

        $hotReload = $this->options['hotReload'] ?? ['enabled' => false, 'basePath' => __DIR__];

        if ($hotReload['enabled']) {
            $this->addHotReload($server->getServer(), $hotReload['basePath']);
        }

        $GLOBALS['runtimeServer'] = $server;

        $server->start();

        return 0;
    }

    public function getKernelEventDispatcher(): EventDispatcherInterface
    {
        return $this->kernel->getContainer()->get('event_dispatcher');
    }

    protected function socketIORun()
    {
        $server = new SocketIO\Server($this->options);

        $server->setSystemEvents('Start', function () use ($server) {
            $server->onStart();
            echo 'Websocket is now listening in ' . $server->getHost() . ':' . $server->getPort() . PHP_EOL;
        });

        $server->setSystemEvents('WorkerStart', function (WebSocketServer $websocketServer, int $workerId) use ($server) {
            $this->kernel->boot();

            $eventData = new \stdClass();
            $eventData->workerId = $workerId;
            $this->getKernelEventDispatcher()->dispatch($eventData, 'workerStart');

            $server->onWorkerStart();
        });

        $server->setSystemEvents('WorkerExit', function (WebSocketServer $websocketServer, int $workerId) use ($server) {
            $eventData = new \stdClass();
            $eventData->workerId = $workerId;
            $this->getKernelEventDispatcher()->dispatch($eventData, 'workerExit');
            $server->onWorkerExit();
        });

        $server->setSystemEvents('WorkerError', function (WebSocketServer $websocketServer, int $workerId) use ($server) {
            $eventData = new \stdClass();
            $eventData->workerId = $workerId;
            $this->getKernelEventDispatcher()->dispatch($eventData, 'workerError');
            $server->onWorkerExit();
            $server->getServer()->shutdown();
        });

        $server->setSystemEvents('request', function (Request $request, Response $response) use ($server) {
            $this->setRequest($request, $response, $server->getServer());
        });

        return $server;
    }

    protected function defaultRun()
    {
        $server = new Websocket\Server($this->options);

        $server->init();

        $server->on('WorkerStart', function () use ($server) {
            $this->kernel->boot();
            $this->kernel->getContainer()->set('websocket.server', $server);
            $server->setEventDispatcher($this->getKernelEventDispatcher());
        });

        $server->on('Start', function () use ($server) {
            $this->output->writeln('Websocket is now listening in ' . $server->getServer()->host . ':' . $server->getServer()->port);
        });

        $server->on('request', function (Request $request, Response $response) use ($server) {
            $this->setRequest($request, $response, $server->getServer());
        });

        $server->setEvent();

        return $server;
    }

    protected function setRequest(Request $request, Response $response, WebSocketServer $server)
    {
        $sfRequest = $this->convertRequest($request);
        $uri = $sfRequest->getRequestUri();
        if ($uri === '/ws/admin/restart' && $this->kernel->getEnvironment() === 'dev') {
            $server->reload();
            $response->end('Worker will restart');
            return;
        }

        if ($this->options['serve_http'] ?? false) {
            $sfResponse = $this->kernel->handle($sfRequest);
            $this->convertResponse($sfResponse, $response);

            if ($this->kernel instanceof TerminableInterface) {
                $this->kernel->terminate($sfRequest, $sfResponse);
            }
            return;
        }

        $sfResponse = new SymfonyResponse('Not found', SymfonyResponse::HTTP_NOT_FOUND);
        $this->convertResponse($sfResponse, $response);
    }

    protected function convertRequest(Request $request)
    {
        $sfRequest = new SymfonyRequest(
            $request->get ?? [],
            $request->post ?? [],
            [],
            $request->cookie ?? [],
            $request->files ?? [],
            array_change_key_case($request->server ?? [], CASE_UPPER),
            $request->rawContent()
        );
        $sfRequest->headers = new HeaderBag($request->header ?? []);

        return $sfRequest;
    }

    protected function convertResponse(SymfonyResponse $sfResponse, Response $response): void
    {
        foreach ($sfResponse->headers->all() as $name => $values) {
            $response->header((string) $name, $values);
        }

        $response->status($sfResponse->getStatusCode());

        switch (true) {
            case $sfResponse instanceof BinaryFileResponse && $sfResponse->headers->has('Content-Range'):
            case $sfResponse instanceof StreamedResponse:
                ob_start(function ($buffer) use ($response) {
                    $response->write($buffer);

                    return '';
                }, 4096);
                $sfResponse->sendContent();
                ob_end_clean();
                $response->end();
                break;
            case $sfResponse instanceof BinaryFileResponse:
                $response->sendfile($sfResponse->getFile()->getPathname());
                break;
            default:
                $response->end($sfResponse->getContent());
        }
    }

    protected function addHotReload(WebSocketServer $server, string $basePath): void
    {
        if (!class_exists(Watcher::class)) {
            return;
        }

        $watcher = new Watcher(
            [$basePath],
            [
                $basePath . '/vendor/',
                $basePath . '/var/',
                $basePath . '/.idea/',
                $basePath . '/var/cache/',
                $basePath . '/var/log/',
            ],
            function () use ($server) {
                $server->reload();
            }
        );

        $process = new \Swoole\Process(function () use ($watcher) {
            $watcher->tick();
        });

        $server->addProcess($process);
    }
}
