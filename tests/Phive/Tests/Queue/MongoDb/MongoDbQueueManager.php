<?php

namespace Phive\Tests\Queue\MongoDb;

use Phive\Queue\MongoDb\MongoDbQueue;
use Phive\Tests\Queue\AbstractQueueManager;

class MongoDbQueueManager extends AbstractQueueManager
{
    /**
     * @var \Mongo
     */
    protected $mongo;

    public function createQueue()
    {
        $this->initMongo();

        $collection = $this->mongo->selectCollection(
            $this->getOption('db_name'),
            $this->getOption('coll_name')
        );

        return new MongoDbQueue($collection);
    }

    public function reset()
    {
        $this->initMongo();

        $this->mongo->dropDB($this->getOption('db_name'));
        //$collection->remove(array(), array('safe' => true));
    }

    protected function initMongo()
    {
        if (!$this->mongo) {
            $this->mongo = new \Mongo($this->getOption('mongo_server'));
        }
    }
}
