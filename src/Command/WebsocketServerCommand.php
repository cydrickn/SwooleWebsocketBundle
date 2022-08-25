<?php

namespace Cydrickn\SwooleWebsocketBundle\Command;

use Cydrickn\SwooleWebsocketBundle\Runtime\Runtime;
use Cydrickn\SwooleWebsocketBundle\Websocket\Server;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand('websocket:server')]
class WebsocketServerCommand extends Command
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setHelp('Websocket Server')
            ->addOption('host', '', InputOption::VALUE_OPTIONAL, 'Host',  '127.0.0.1')
            ->addOption('port', '', InputOption::VALUE_OPTIONAL, 'Port', 8000)
        ;
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $options = ['host' => $input->getOption('host'), 'port' => $input->getOption('port')];
        if ($this->getRuntime() === Runtime::class) {
            $options = array_replace_recursive($options, $this->getRuntimeOption());
        }

        $server = new Server($options);
        $server->setEventDispatcher($this->eventDispatcher);
        $server->init();
        $server->on('Start', function () use ($output, $server) {
            $output->writeln('Websocket is now listening in ' . $server->getServer()->host . ':' . $server->getServer()->port);
        });
        $server->setEvent();
        $this->server = $server;
        $server->start();
    }

    private function getRuntime(): string
    {
        return $_SERVER['APP_RUNTIME'] ?? $_ENV['APP_RUNTIME'] ?? 'Symfony\\Component\\Runtime\\SymfonyRuntime';
    }

    private function getRuntimeOption(): array
    {
        return $_SERVER['APP_RUNTIME_OPTIONS'] ?? $_ENV['APP_RUNTIME_OPTIONS'] ?? [];
    }
}
