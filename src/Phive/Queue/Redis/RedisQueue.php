<?php

namespace Phive\Queue\Redis;

use Phive\Queue\AdvancedQueueInterface;
use Phive\Queue\AbstractQueue;
use Phive\CallbackIterator;

class RedisQueue extends AbstractQueue implements AdvancedQueueInterface
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * Constructor.
     *
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
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
        $member = $unique.'@'.$item;

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
                return substr($key, strpos($key, '@') + 1);
            }
        }
    }

    /**
     * @see AdvancedQueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        $range = $this->redis->zRangeByScore('items', '-inf', time(), array('limit' => array($skip, $limit)));
        if (empty($range)) {
            return false;
        }

        return new CallbackIterator(new \ArrayIterator($range), function ($data) {
            return substr($data, strpos($data, '@') + 1);
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
}
