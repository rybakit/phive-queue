<?php

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\Queue\MongoQueue;

class MongoHandler extends AbstractHandler
{
    /**
     * @var \MongoClient
     */
    protected $client;

    /**
     * @var \MongoCollection
     */
    protected $collection;

    public function __construct(array $options = array())
    {
        if (!extension_loaded('mongo')) {
            throw new \RuntimeException('The "mongo" extension is not loaded.');
        }

        parent::__construct($options);
    }

    public function createQueue()
    {
        return new MongoQueue($this->client, array(
            'db'   => $this->getOption('db_name'),
            'coll' => $this->getOption('coll_name'),
        ));
    }

    public function reset()
    {
        $this->client->dropDB($this->getOption('db_name'));
    }

    public function clear()
    {
        $this->getCollection()->remove();
    }

    protected function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->client->selectCollection(
                $this->getOption('db_name'),
                $this->getOption('coll_name')
            );
            $this->collection->ensureIndex(array('eta' => 1));
        }

        return $this->collection;
    }

    protected function configure()
    {
        $this->client = new \MongoClient($this->getOption('server'));
    }
}
