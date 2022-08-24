<?php

namespace Cydrickn\SwooleWebsocketBundle\Command;

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
            ->addOption('port', '', InputOption::VALUE_OPTIONAL, 'Port', 8080)
        ;
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $server = new Server($this->eventDispatcher, ['host' => $input->getOption('host'), 'port' => $input->getOption('port')]);
        $server->init();
        $server->on('Start', function () use ($output, $server) {
            $output->writeln('Websocket is now listening in ' . $server->getServer()->host . ':' . $server->getServer()->port);
        });
        $server->start();
    }
}
