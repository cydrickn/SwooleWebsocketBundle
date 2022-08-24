<?php

namespace Cydrickn\SwooleWebsocketBundle\Command;

use Cydrickn\SwooleWebsocketBundle\Websocket\Server;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('websocket:server')]
class WebsocketServerCommand extends Command
{
    public function __construct(private Server $server)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->setHelp('Websocket Server')
            ->addOption('host', 'h', InputOption::VALUE_OPTIONAL, '127.0.0.1')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, '8080')
        ;
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
    }
}
