<?php

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\TarantoolQueue;

class TarantoolHandler extends Handler
{
    /**
     * @var \Tarantool
     */
    private $tarantool;

    public function createQueue()
    {
        return new TarantoolQueue(
            $this->tarantool,
            $this->getOption('space'),
            $this->getOption('tube_name')
        );
    }

    public function clear()
    {
        $this->tarantool->call('queue.truncate', [
            $this->getOption('tube_name'),
            $this->getOption('space'),
        ]);
    }

    protected function configure()
    {
        $this->tarantool = new \Tarantool($this->getOption('host'), $this->getOption('port'));
    }
}
