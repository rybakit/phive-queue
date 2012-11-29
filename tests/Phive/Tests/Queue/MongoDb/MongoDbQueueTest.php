<?php

namespace Phive\Tests\Queue\MongoDb;

use Phive\Tests\Queue\HandlerAwareQueueTest;
use Phive\Queue\MongoDb\MongoDbQueue;
use Phive\RuntimeException;

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

    public function testThrowRuntimeException()
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

        foreach (array('push', 'pop', 'peek', 'clear', 'count') as $method) {
            try {
                if ('push' === $method) {
                    $queue->$method('item');
                } else {
                    $queue->$method();
                }
            } catch (RuntimeException $e) {
                continue;
            }

            $this->fail(get_class($queue).":$method() throws \\Phive\\RuntimeException on error.");
        }
    }
}
