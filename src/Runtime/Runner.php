<?php

namespace Cydrickn\SwooleWebsocketBundle\Runtime;

use Cydrickn\SwooleWebsocketBundle\Websocket\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
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
        $server = new Server($this->options);

        $server->init();

        $server->on('WorkerStart', function () use ($server) {
            $this->kernel->boot();
            $server->setEventDispatcher($this->kernel->getContainer()->get('event_dispatcher'));
        });

        $server->on('Start', function () use ($server) {
            $this->output->writeln('Websocket is now listening in ' . $server->getServer()->host . ':' . $server->getServer()->port);
        });

        $server->on('request', function (Request $request, Response $response) use ($server) {
            $sfRequest = $this->convertRequest($request);
            $uri = $sfRequest->getRequestUri();
            if ($uri === '/ws/admin/restart' && $this->kernel->getEnvironment() === 'dev') {
                $server->getServer()->reload();
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
        });

        $server->setEvent();

        $server->start();

        return 0;
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
}
