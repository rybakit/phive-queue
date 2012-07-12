<?php

namespace Phive\Tests\Queue\MongoDb;

use Phive\Queue\MongoDb\MongoDbQueue;
use Phive\Tests\Queue\AbstractHandler;

class MongoDbHandler extends AbstractHandler
{
    /**
     * @var \Mongo
     */
    protected $mongo;

    public function __construct(array $options = array())
    {
        if (!extension_loaded('mongo')) {
            throw new \RuntimeException('The "mongo" extension is not loaded.');
        }

        parent::__construct($options);

        $this->configure();
    }

    public function createQueue()
    {
        $collection = $this->mongo->selectCollection(
            $this->getOption('db_name'),
            $this->getOption('coll_name')
        );

        return new MongoDbQueue($collection);
    }

    public function reset()
    {
        $this->mongo->dropDB($this->getOption('db_name'));
        //$collection->remove(array(), array('safe' => true));
    }

    protected function configure()
    {
        $this->mongo = new \Mongo($this->getOption('server'));
    }
}
