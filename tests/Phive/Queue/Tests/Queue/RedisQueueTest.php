<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue\RedisQueue;
use Phive\Queue\Tests\Handler\RedisHandler;

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
     * @expectedException   \Phive\Queue\Exception\RuntimeException
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
            array($queue, 'count', array()),
            array($queue, 'clear', array()),
        );
    }
}
