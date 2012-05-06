<?php

namespace Phive\Queue\Redis;

use Phive\Queue\AdvancedQueueInterface;
use Phive\Queue\AbstractQueue;
use Phive\PhpSerializer;

class RedisQueue extends AbstractQueue implements AdvancedQueueInterface
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var \Phive\PhpSerializer
     */
    protected $serializer;

    /**
     * Constructor.
     *
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
        $this->serializer = $this->createSerializer();
    }

    /**
     * Retrieves \Redis instance.
     *
     * @return \Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @see QueueInterface::push()
     */
    public function push($item, $eta = null)
    {
        $eta = $eta ? $this->normalizeEta($eta) : time();

        $unique = $this->redis->incr('sequence');
        $member = $unique.'@'.$this->serializer->serialize($item);

        $result = $this->redis->zAdd('items', $eta, $member);
        if (!$result) {
            throw new \RuntimeException('Unable to push the item.');
        }
    }

    /**
     * TODO
     * Implement zpop using lua scripting (available since redis 2.6):
     *
     * @link http://grokbase.com/t/gg/redis-db/123vpe6070/zpop-atomic
     *
     *     val = redis.call('zrange', KEYS[1], 0, 0)
     *     if val then redis.call('zremrangebyrank', KEYS[1], 0, 0) end
     *     return val
     *
     * For now it's not supported by phpredis (@link https://github.com/nicolasff/phpredis/issues/97)
     *
     * @see QueueInterface::pop()
     */
    public function pop()
    {
        while (true) {
            $this->redis->watch('items');
            $range = $this->redis->zRangeByScore('items', '-inf', time(), array('limit' => array(0, 1)));

            if (empty($range)) {
                $this->redis->unwatch();
                return false;
            }

            $key = reset($range);
            $result = $this->redis->multi()
                ->zRem('items', $key)
                ->exec();

            if (!empty($result[0])) {
                $data = substr($key, strpos($key, '@') + 1);

                return $this->serializer->unserialize($data);
            }
        }
    }

    /**
     * @see AdvancedQueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        if ($limit <= 0) {
            // Parameter limit must either be -1 or a value greater than or equal 0
            throw new \OutOfRangeException('Parameter limit must be greater than 0.');
        }
        if ($skip < 0) {
            throw new \OutOfRangeException('Parameter skip must be greater than or equal 0.');
        }

        $range = $this->redis->zRangeByScore('items', '-inf', time(), array('limit' => array($skip, $limit)));
        if (empty($range)) {
            return false;
        }

        $serializer = $this->serializer;
        return new IterableResult($range, function ($data) use ($serializer) {
            $data = substr($data, strpos($data, '@') + 1);
            return $serializer->unserialize($data);
        });
    }

    /**
     * @see AdvancedQueueInterface::count()
     */
    public function count()
    {
        return $this->redis->zCard('items');
    }

    /**
     * @see AdvancedQueueInterface::clear()
     */
    public function clear()
    {
        $this->redis->del(array('items', 'sequence'));
    }

    /**
     * @return \Phive\PhpSerializer
     */
    protected function createSerializer()
    {
        return new PhpSerializer();
    }
}