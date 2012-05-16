<?php

use Phive\Queue\MongoDB\MongoDBQueue;

class MongoDBHandler extends AbstractHandler
{
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
        $collection = self::$conn->selectCollection('phive_tests', 'phive_queue');

        return new MongoDBQueue($collection);
    }
}