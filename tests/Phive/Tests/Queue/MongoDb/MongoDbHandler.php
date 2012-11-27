<?php

namespace Phive\Tests\Queue\MongoDb;

use Phive\Queue\MongoDb\MongoDbQueue;
use Phive\Tests\Queue\AbstractHandler;

class MongoDbHandler extends AbstractHandler
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
        return new MongoDbQueue($this->client, array(
            'database'      => $this->getOption('db_name'),
            'collection'    => $this->getOption('coll_name'),
        ));
    }

    public function reset()
    {
        $this->client->dropDB($this->getOption('db_name'));
    }

    public function clear()
    {
        $this->getCollection()->remove(array(), array('safe' => true));
    }

    protected function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->client->selectCollection(
                $this->getOption('db_name'),
                $this->getOption('coll_name')
            );
        }

        return $this->collection;
    }

    protected function configure()
    {
        $this->client = new \MongoClient($this->getOption('server'));
    }
}
