<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\RedisHandler;

/**
 * @requires extension redis
 */
class RedisQueueTest extends QueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    public function provideItemsOfVariousSupportedTypes()
    {
        return array_diff_key(parent::provideItemsOfVariousSupportedTypes(), [
            'array'     => false,
            'object'    => false,
        ]);
    }

    public function testSerialize()
    {
        if (!method_exists('Redis','_serialize')) {
            $this->markTestSkipped('Redis::_serialize() does not exists');
        }

        $redis = $this->queue->getRedis();

        $serializers = [\Redis::SERIALIZER_PHP];
        if (defined('Redis::SERIALIZER_IGBINARY')) {
            $serializers[] = \Redis::SERIALIZER_IGBINARY;
        }

        $items = parent::provideItemsOfVariousSupportedTypes();

        foreach ($serializers as $serializer) {
            $redis->setOption(\Redis::OPT_SERIALIZER, $serializer);

            foreach ($items as $item) {
                $this->queue->push($item[0]);
                $this->assertEquals($item[0], $this->queue->pop());
            }
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
