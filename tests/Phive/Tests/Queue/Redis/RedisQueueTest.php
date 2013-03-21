<?php

namespace Phive\Tests\Queue\Redis;

use Phive\Tests\Queue\AbstractPersistentQueueTest;
use Phive\Queue\Redis\RedisQueue;

class RedisQueueTest extends AbstractPersistentQueueTest
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
    public function testThrowRuntimeException(RedisQueue $queue, $method, array $args)
    {
        call_user_func_array(array($queue, $method), $args);
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
            array($queue, 'push',  array('item')),
            array($queue, 'pop',   array()),
            array($queue, 'slice', array(0, 1)),
            array($queue, 'count', array()),
            array($queue, 'clear', array()),
        );
    }
}
