<?php

namespace Phive\Queue;

/**
 * RedisQueue requires redis server 2.6 or higher.
 */
class RedisQueue implements Queue
{
    const SCRIPT_PUSH = <<<'LUA'
        local id = redis.call('INCR', KEYS[2])
        return redis.call('ZADD', KEYS[1], ARGV[2], id..':'..ARGV[1])
LUA;

    const SCRIPT_POP = <<<'LUA'
        local items = redis.call('ZRANGEBYSCORE', KEYS[1], '-inf', ARGV[1], 'LIMIT', 0, 1)
        if 0 == #items then return -1 end
        redis.call('ZREM', KEYS[1], items[1])
        return string.sub(items[1], string.find(items[1], ':') + 1)
LUA;

    /**
     * @var \Redis
     */
    private $redis;

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
        $eta = normalize_eta($eta);

        if (\Redis::SERIALIZER_NONE !== $this->redis->getOption(\Redis::OPT_SERIALIZER)) {
            $item = $this->redis->_serialize($item);
        }

        $result = $this->redis->evaluate(self::SCRIPT_PUSH, ['items', 'seq', $item, $eta], 2);
        $this->assertResult($result);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $result = $this->redis->evaluate(self::SCRIPT_POP, ['items', time()], 1);
        $this->assertResult($result);

        if (-1 === $result) {
            throw new NoItemAvailableException($this);
        }

        if (\Redis::SERIALIZER_NONE !== $this->redis->getOption(\Redis::OPT_SERIALIZER)) {
            return $this->redis->_unserialize($result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $result = $this->redis->zCard('items');
        $this->assertResult($result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $result = $this->redis->del(['items', 'seq']);
        $this->assertResult($result);
    }

    /**
     * @param mixed $result
     *
     * @throws QueueException
     */
    protected function assertResult($result)
    {
        if (false === $result) {
            throw new QueueException($this, $this->redis->getLastError());
        }
    }
}
