<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\NoItemException;
use Phive\Queue\Exception\RuntimeException;
use Phive\Queue\QueueUtils;

/**
 * RedisQueue requires redis server 2.6 or higher.
 */
class RedisQueue implements QueueInterface
{
    const SCRIPT_PUSH = <<<'LUA'
        local id = redis.call('INCR', KEYS[2])
        return redis.call('ZADD', KEYS[1], ARGV[2], id..':'..ARGV[1])
LUA;

    const SCRIPT_POP = <<<'LUA'
        local item = redis.call('ZRANGEBYSCORE', KEYS[1], '-inf', ARGV[1], 'LIMIT', 0, 1)
        if 0 == #item then
            return -1
        end
        item = unpack(item)
        redis.call('ZREM', KEYS[1], item)
        return item
LUA;

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);

        $result = $this->redis->evaluate(self::SCRIPT_PUSH, ['items', 'sequence', $item, $eta], 2);
        $this->ensureResult($result);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $result = $this->redis->evaluate(self::SCRIPT_POP, ['items', time()], 1);
        $this->ensureResult($result);

        if (-1 === $result) {
            throw new NoItemException();
        }

        return substr($result, strpos($result, ':') + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $result = $this->redis->zCard('items');
        $this->ensureResult($result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $result = $this->redis->del(['items', 'sequence']);
        $this->ensureResult($result);
    }

    /**
     * @param mixed $result
     *
     * @throws RuntimeException
     */
    protected function ensureResult($result)
    {
        if (false === $result) {
            throw new RuntimeException($this->redis->getLastError());
        }
    }
}
