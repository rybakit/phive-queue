<?php

namespace Phive\Queue\Tests\Handler; 

use Phive\Queue\PredisQueue;
use Predis\Client;

class PredisHandler extends Handler
{
    /**
     * @var Client
     */
    private $redis;
    
    /**
     * @return \Phive\Queue\Queue
     */
    public function createQueue()
    {
        return new PredisQueue($this->redis);
    }

    public function clear()
    {
        $this->redis->del('items');
    }

    protected function configure()
    {
        $connection = 'tcp://'.$this->getOption('host').':'.$this->getOption('port');

        $this->redis = new Client($connection, array('prefix' => $this->getOption('prefix')));
    }
}
