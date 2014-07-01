<?php

namespace Phive\Queue\Tests\Handler; 

use Phive\Queue\PredisQueue;
use Predis\Client;

class PredisHandler extends Handler
{
    /**
     * @var Client
     */
    private $client;
    
    /**
     * @return \Phive\Queue\Queue
     */
    public function createQueue()
    {
        return new PredisQueue($this->client);
    }

    public function clear()
    {
        $this->client->del('items');
    }

    protected function configure()
    {
        $conn = 'tcp://'.$this->getOption('host').':'.$this->getOption('port');

        $this->client = new Client($conn, ['prefix' => $this->getOption('prefix')]);
    }
}
