<?php

namespace Phive\Queue\Redis;

use Phive\CallbackIterator;
use Phive\RuntimeException;
use Phive\Queue\AbstractQueue;

/**
 * RedisQueue requires Redis >= 2.6 (for a Lua scripting feature) and
 * phpredis >= 2.2.2 which has a fix @link https://github.com/nicolasff/phpredis/pull/189
 * for a PHP 5.4 bug @link https://bugs.php.net/bug.php?id=62112.
 */
class RedisQueue extends AbstractQueue
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
    protected $redis;

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
        $eta = $this->normalizeEta($eta);

        $self = $this;
        $this->exceptional(function(\Redis $redis) use ($self, $item, $eta) {
            $prefix = $redis->getOption(\Redis::OPT_PREFIX);
            $redis->eval($self::SCRIPT_PUSH, array($prefix.'items', $eta, $item, 'sequence'));
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

            return $redis->eval($self::SCRIPT_POP, array($prefix.'items', time()));
        });

        if (false === $item) {
            return false;
        }

        return substr($item, strpos($item, ':') + 1);
    }

    /**
     * {@inheritdoc}
     */
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        $range = $this->exceptional(function(\Redis $redis) use ($limit, $skip) {
            return $redis->zRangeByScore('items', '-inf', time(),
                array('limit' => array($skip, $limit))
            );
        });

        if (empty($range)) {
            return false;
        }

        return new CallbackIterator(new \ArrayIterator($range), function ($data) {
            return substr($data, strpos($data, ':') + 1);
        });
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
     * @param Closure $func The function to execute.
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
