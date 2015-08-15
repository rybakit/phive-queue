<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\NoItemAvailableException;
use Phive\Queue\QueueException;
use Phive\Queue\RedisQueue;
use Phive\Queue\Tests\Handler\RedisHandler;

/**
 * @requires function Redis::connect
 */
class RedisQueueTest extends QueueTest
{
    use Performance;
    use Concurrency;

    protected function getUnsupportedItemTypes()
    {
        return [Types::TYPE_ARRAY, Types::TYPE_OBJECT];
    }

    /**
     * @dataProvider provideItemsOfUnsupportedTypes
     * @expectedException PHPUnit_Framework_Exception
     * @expectedExceptionMessageRegExp /could not be converted to string|Array to string conversion/
     */
    public function testUnsupportedItemType($item)
    {
        $this->queue->push($item);
    }

    /**
     * @requires function Redis::_serialize
     * @dataProvider provideItemsOfVariousTypes
     */
    public function testSupportItemTypeWithSerializerLoose($item)
    {
        $redis = self::getHandler()->createRedis();
        $queue = new RedisQueue($redis);

        $serializers = [\Redis::SERIALIZER_PHP];
        if (defined('Redis::SERIALIZER_IGBINARY')) {
            $serializers[] = \Redis::SERIALIZER_IGBINARY;
        }

        foreach ($serializers as $serializer) {
            $redis->setOption(\Redis::OPT_SERIALIZER, $serializer);

            $queue->push($item);
            $this->assertEquals($item, $queue->pop());
        }
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     */
    public function testThrowExceptionOnErrorResponse($method)
    {
        $mock = $this->getMock('Redis');

        $redisMethods = get_class_methods('Redis');
        foreach ($redisMethods as $redisMethod) {
            $mock->expects($this->any())->method($redisMethod)->will($this->returnValue(false));
        }

        $queue = new RedisQueue($mock);

        try {
            $this->callQueueMethod($queue, $method);
        } catch (NoItemAvailableException $e) {
        } catch (QueueException $e) {
            return;
        }

        $this->fail();
    }

    public static function createHandler(array $config)
    {
        return new RedisHandler([
            'host'   => $config['PHIVE_REDIS_HOST'],
            'port'   => $config['PHIVE_REDIS_PORT'],
            'prefix' => $config['PHIVE_REDIS_PREFIX'],
        ]);
    }
}
