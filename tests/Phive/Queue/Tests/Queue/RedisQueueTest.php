<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue\RedisQueue;
use Phive\Queue\Tests\Handler\RedisHandler;

/**
 * @requires extension redis
 */
class RedisQueueTest extends AbstractPersistentQueueTest
{
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

    public static function createHandler(array $config)
    {
        return new RedisHandler(array(
            'host'   => $config['redis_host'],
            'port'   => $config['redis_port'],
            'prefix' => $config['redis_prefix'],
        ));
    }
}
