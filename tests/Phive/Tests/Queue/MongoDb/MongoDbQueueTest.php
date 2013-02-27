<?php

namespace Phive\Tests\Queue\MongoDb;

use Phive\Tests\Queue\HandlerAwareQueueTest;
use Phive\Queue\QueueInterface;
use Phive\Queue\MongoDb\MongoDbQueue;

class MongoDbQueueTest extends HandlerAwareQueueTest
{
    public static function createHandler()
    {
        return new MongoDbHandler(array(
            'server'    => $GLOBALS['mongo_server'],
            'db_name'   => $GLOBALS['mongo_db_name'],
            'coll_name' => $GLOBALS['mongo_coll_name'],
        ));
    }

    /**
     * @dataProvider        testThrowRuntimeExceptionProvider
     * @expectedException   \Phive\RuntimeException
     */
    public function testThrowRuntimeException(QueueInterface $queue, $method)
    {
        ('push' === $method) ? $queue->$method('item') : $queue->$method();
    }

    public function testThrowRuntimeExceptionProvider()
    {
        $e = $this->getMock('\\MongoException');
        $client = $this->getMock('\\MongoClient');

        foreach (get_class_methods('\\MongoClient') as $method) {
            $client->expects($this->any())->method($method)->will($this->throwException($e));
        }

        $queue = new MongoDbQueue($client, array('db' => '', 'coll' => ''));

        return array(
            array($queue, 'push'),
            array($queue, 'pop'),
            array($queue, 'peek'),
            array($queue, 'count'),
            array($queue, 'clear'),
        );
    }
}
