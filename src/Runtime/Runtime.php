<?php

namespace Cydrickn\SwooleWebsocketBundle\Runtime;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;

class Runtime extends SymfonyRuntime
{
    public function getRunner(?object $application): RunnerInterface
    {
        if ($application instanceof KernelInterface) {
            return new Runner($application, $this->options);
        }

        return parent::getRunner($application);
    }
}
