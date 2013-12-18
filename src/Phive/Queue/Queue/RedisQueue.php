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

        $self = $this;
        $this->exceptional(function(\Redis $redis) use ($self, $item, $eta) {
            $prefix = $redis->getOption(\Redis::OPT_PREFIX);
            $redis->evaluate($self::SCRIPT_PUSH, array($prefix.'items', $eta, $item, $prefix.'sequence'));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $self = $this;
        $item = $this->exceptional(function(\Redis $redis) use ($self) {
            $prefix = $redis->getOption(\Redis::OPT_PREFIX);

            return $redis->evaluate($self::SCRIPT_POP, array($prefix.'items', time()));
        });

        if ($item) {
            return substr($item, strpos($item, ':') + 1);
        }

        throw new NoItemException();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->exceptional(function(\Redis $redis) {
            return $redis->zCard('items');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->exceptional(function(\Redis $redis) {
            return $redis->del(array('items', 'sequence'));
        });
    }

    /**
     * @param \Closure $func The function to execute.
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    protected function exceptional(\Closure $func)
    {
        try {
            $lastError = $this->redis->getLastError();
            $result = $func($this->redis);
            if ($error = $this->redis->getLastError() !== $lastError) {
                throw new RuntimeException($error);
            }
        } catch (\RedisException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }
}
