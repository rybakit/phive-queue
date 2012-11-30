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

        $db = $this->getMockBuilder('\\MongoDB')
            ->disableOriginalConstructor()
            ->getMock();

        $coll = $this->getMockBuilder('\\MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->any())->method('selectCollection')->will($this->returnValue($coll));

        $db->expects($this->any())->method('command')->will($this->throwException($e));

        $coll->expects($this->any())->method('find')->will($this->throwException($e));
        $coll->expects($this->any())->method('insert')->will($this->throwException($e));
        $coll->expects($this->any())->method('count')->will($this->throwException($e));
        $coll->expects($this->any())->method('remove')->will($this->throwException($e));
        $coll->expects($this->any())->method('__get')->with($this->equalTo('db'))->will($this->returnValue($db));

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
