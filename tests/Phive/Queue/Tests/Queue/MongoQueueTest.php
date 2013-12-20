<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue\MongoQueue;
use Phive\Queue\Tests\Handler\MongoHandler;

class MongoQueueTest extends AbstractPersistentQueueTest
{
    public static function createHandler()
    {
        return new MongoHandler(array(
            'server'    => $GLOBALS['mongo_server'],
            'db_name'   => $GLOBALS['mongo_db_name'],
            'coll_name' => $GLOBALS['mongo_coll_name'],
        ));
    }

    /**
     * @dataProvider        throwRuntimeExceptionProvider
     * @expectedException   \Phive\Queue\Exception\RuntimeException
     */
    public function testThrowRuntimeException(MongoQueue $queue, $method, array $args)
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

        $queue = new MongoQueue($client, array('db' => '', 'coll' => ''));

        return array(
            array($queue, 'push',  array('item')),
            array($queue, 'pop',   array()),
            array($queue, 'count', array()),
            array($queue, 'clear', array()),
        );
    }
}
