<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\RedisQueue;
use Phive\Queue\Tests\Handler\RedisHandler;

/**
 * @requires extension redis
 */
class RedisQueueTest extends QueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    public function getUnsupportedItemTypes()
    {
        return ['array', 'object'];
    }

    /**
     * @dataProvider provideItemsOfVariousTypes
     */
    public function testSupportedItemTypeLooseWithSerializer($item)
    {
        if (!method_exists('Redis', '_serialize')) {
            $this->markTestSkipped('Redis::_serialize() is required.');
        }

        $handler = self::getHandler();

        $redis = new \Redis();
        $redis->connect($handler->getOption('host'), $handler->getOption('port'));
        $redis->setOption(\Redis::OPT_PREFIX, $handler->getOption('prefix'));

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

    public static function createHandler(array $config)
    {
        return new RedisHandler([
            'host'   => $config['PHIVE_REDIS_HOST'],
            'port'   => $config['PHIVE_REDIS_PORT'],
            'prefix' => $config['PHIVE_REDIS_PREFIX'],
        ]);
    }
}
