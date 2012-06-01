<?php

use Phive\Queue\MongoDb\MongoDbQueue;

class MongoDbHandler extends AbstractHandler
{
    /**
     * @var \Mongo
     */
    protected static $conn;

    public function prepare()
    {
        self::$conn->selectDb('phive_tests')->drop();
    }

    public function shutdown()
    {
        self::$conn->selectDb('phive_tests')->drop();
        self::$conn->close();
    }

    protected function setup()
    {
        self::$conn = new \Mongo();
    }

    protected function createQueue()
    {
        $collection = self::$conn->selectCollection('phive_tests', 'queue');

        return new MongoDbQueue($collection);
    }
}
