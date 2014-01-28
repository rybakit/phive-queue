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
        local id = redis.call('INCR', ARGV[4])
        return redis.call('ZADD', ARGV[1], ARGV[2], id..':'..ARGV[3])
LUA;

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
        $prefix = $this->redis->getOption(\Redis::OPT_PREFIX);
        
        $result = $this->redis->evaluate(self::SCRIPT_PUSH, [$prefix.'items', $eta, $item, $prefix.'sequence']);
        $this->ensureResult($result);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $prefix = $this->redis->getOption(\Redis::OPT_PREFIX);

        $result = $this->redis->evaluate(self::SCRIPT_POP, [$prefix.'items', time()]);
        $this->ensureResult($result);

        if ($result) {
            return substr($result, strpos($result, ':') + 1);
        }

        throw new NoItemException();
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
