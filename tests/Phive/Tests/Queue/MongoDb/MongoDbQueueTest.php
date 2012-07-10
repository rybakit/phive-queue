<?php

namespace Phive\Tests\Queue\MongoDb;

use Phive\Tests\Queue\AbstractQueueTestCase;
use Phive\Queue\MongoDb\MongoDbQueue;

class MongoDbQueueTest extends AbstractQueueTestCase
{
    /**
     * @var MongoDbQueueManager
     */
    protected static $manager;

    public static function setUpBeforeClass()
    {
        if (!class_exists('\Mongo')) {
            return;
        }

        parent::setUpBeforeClass();

        self::$manager = new MongoDbQueueManager(array(
            'server'        => $GLOBALS['mongo_server'],
            'db'            => $GLOBALS['mongo_db'],
            'collection'    => $GLOBALS['mongo_collection'],
        ));
    }

    public function setUp()
    {
        if (!self::$manager) {
            $this->markTestSkipped('MongoDbQueue requires the php "mongo" extension.');
        }

        parent::setUp();

        self::$manager->reset();
    }

    /**
     * @return \Phive\Queue\QueueInterface
     *
     * @throws \LogicException
     */
    public function createQueue()
    {
        if (self::$manager) {
            return self::$manager->createQueue();
        }

        throw new \LogicException('RedisQueueManager is not initialized.');
    }
}
