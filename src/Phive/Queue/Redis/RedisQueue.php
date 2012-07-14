<?php

namespace Phive\Queue\Redis;

use Phive\CallbackIterator;
use Phive\Exception\RuntimeException;
use Phive\Queue\AbstractQueue;

class RedisQueue extends AbstractQueue
{
    const SCRIPT_POP = <<<'LUA'
        local item = redis.call('ZRANGEBYSCORE', ARGV[1], '-inf', ARGV[2], 'LIMIT', 0, 1)
        if 0 ~= #item then
            redis.call('ZREM', ARGV[1], unpack(item))
        end
        return unpack(item)
LUA;

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
     * @see \Phive\Queue\QueueInterface::push()
     */
    public function push($item, $eta = null)
    {
        $eta = $eta ? $this->normalizeEta($eta) : time();

        $unique = $this->redis->incr('sequence');
        $member = $unique.'@'.$item;

        $result = $this->redis->zAdd('items', $eta, $member);
        if (!$result) {
            throw new RuntimeException('Unable to push the item.');
        }
    }

    /**
     * @see \Phive\Queue\QueueInterface::pop()
     */
    public function pop()
    {
        $prefix = $this->redis->getOption(\Redis::OPT_PREFIX);
        $item = $this->redis->eval(static::SCRIPT_POP, array($prefix.'items', time()));

        if (false !== $item) {
            return substr($item, strpos($item, '@') + 1);
        }

        return false;
    }

    /**
     * @see \Phive\Queue\QueueInterface::peek()
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
     * @see \Phive\Queue\QueueInterface::count()
     */
    public function count()
    {
        return $this->redis->zCard('items');
    }

    /**
     * @see \Phive\Queue\QueueInterface::clear()
     */
    public function clear()
    {
        $this->redis->del(array('items', 'sequence'));
    }
}
