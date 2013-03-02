<?php

namespace Phive\Tests\Queue\Redis;

use Phive\Tests\Queue\HandlerAwareQueueTest;
use Phive\Queue\Redis\RedisQueue;

class RedisQueueTest extends HandlerAwareQueueTest
{
    public static function createHandler()
    {
        return new RedisHandler(array(
            'host'   => $GLOBALS['redis_host'],
            'port'   => $GLOBALS['redis_port'],
            'prefix' => $GLOBALS['redis_prefix'],
        ));
    }

    /**
     * @dataProvider        throwRuntimeExceptionProvider
     * @expectedException   \Phive\Queue\RuntimeException
     */
    public function testThrowRuntimeException(RedisQueue $queue, $method)
    {
        ('push' === $method) ? $queue->$method('item') : $queue->$method();
    }

    public function throwRuntimeExceptionProvider()
    {
        $redis = $this->getMock('\\Redis');
        $e = $this->getMock('\\RedisException');

        $methods = array_diff(get_class_methods('\\Redis'), array('__destruct'));
        foreach ($methods as $method) {
            $redis->expects($this->any())->method($method)->will($this->throwException($e));
        }

        $queue = new RedisQueue($redis);

        return array(
            array($queue, 'push'),
            array($queue, 'pop'),
            array($queue, 'peek'),
            array($queue, 'count'),
            array($queue, 'clear'),
        );
    }
}
