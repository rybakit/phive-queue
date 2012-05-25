<?php

namespace Phive\Tests\Queue\MongoDB;

use Phive\Tests\Queue\AbstractQueueTest;
use Phive\Queue\MongoDB\MongoDBQueue;

class MongoDBQueueTest extends AbstractQueueTest
{
    /**
     * @var \Mongo
     */
    protected static $conn;

    /**
     * @var \MongoCollection
     */
    protected static $collection;

    public static function setUpBeforeClass()
    {
        if (!class_exists('\Mongo')) {
            return;
        }

        parent::setUpBeforeClass();

        $server = isset($GLOBALS['mongo_server']) ? $GLOBALS['mongo_server'] : 'mongodb://localhost:27017';

        self::$conn = new \Mongo($server);
        self::$conn->dropDB('phive_tests');
        self::$collection = self::$conn->selectCollection('phive_tests', 'queue');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        if (self::$collection) {
            self::$collection->db->drop();
            self::$conn->close();
        }
    }

    public function setUp()
    {
        if (!self::$collection) {
            $this->markTestSkipped('MongoDBQueue requires the php "mongo" extension');
        }

        parent::setUp();

        self::$collection->remove(array(), array('safe' => true));
    }

    protected function createQueue()
    {
        return new MongoDBQueue(self::$collection);
    }
}
