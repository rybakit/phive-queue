<?php

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\Queue\MongoQueue;

class MongoHandler extends AbstractHandler
{
    /**
     * @var \MongoClient
     */
    private $client;

    /**
     * @var \MongoCollection
     */
    private $coll;

    public function createQueue()
    {
        return new MongoQueue(
            $this->client,
            $this->getOption('db_name'),
            $this->getOption('coll_name')
        );
    }

    public function reset()
    {
        $this->client->dropDB($this->getOption('db_name'));
    }

    public function clear()
    {
        $this->coll->remove();
    }

    protected function configure()
    {
        $this->client = new \MongoClient($this->getOption('server'));
        $this->coll = $this->client->selectCollection($this->getOption('db_name'), $this->getOption('coll_name'));
        $this->coll->ensureIndex(['eta' => 1]);
    }
}
