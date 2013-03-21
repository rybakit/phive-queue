<?php

namespace Phive\Tests\Queue\MongoDb;

use Phive\Tests\Queue\AbstractPersistentQueueTest;
use Phive\Queue\MongoDb\MongoDbQueue;

class MongoDbQueueTest extends AbstractPersistentQueueTest
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
     * @dataProvider        throwRuntimeExceptionProvider
     * @expectedException   \Phive\Queue\RuntimeException
     */
    public function testThrowRuntimeException(MongoDbQueue $queue, $method, array $args)
    {
        call_user_func_array(array($queue, $method), $args);
    }

    public function throwRuntimeExceptionProvider()
    {
        $client = $this->getMock('\\MongoClient');
        $e = $this->getMock('\\MongoException');

        $methods = array_diff(get_class_methods('\\MongoClient'), array('__destruct'));
        foreach ($methods as $method) {
            $client->expects($this->any())->method($method)->will($this->throwException($e));
        }

        $queue = new MongoDbQueue($client, array('db' => '', 'coll' => ''));

        return array(
            array($queue, 'push',  array('item')),
            array($queue, 'pop',   array()),
            array($queue, 'slice', array(0, 1)),
            array($queue, 'count', array()),
            array($queue, 'clear', array()),
        );
    }
}
