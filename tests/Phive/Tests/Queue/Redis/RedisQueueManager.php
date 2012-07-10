<?php

namespace Phive\Tests\Queue\Redis;

use Phive\Queue\Redis\RedisQueue;
use Phive\Tests\Queue\AbstractQueueManager;

class RedisQueueManager extends AbstractQueueManager
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function createQueue()
    {
        $this->initRedis();

        return new RedisQueue($this->redis);
    }

    public function reset()
    {
        $this->initRedis();

        $prefix = $this->redis->getOption(\Redis::OPT_PREFIX);
        $offset = strlen($prefix);

        $keys = $this->redis->keys('*');
        foreach ($keys as $key) {
            $this->redis->del(substr($key, $offset));
        }
    }

    protected function initRedis()
    {
        if (!$this->redis) {
            $this->redis = new \Redis();
            $this->redis->connect($this->getOption('host'), $this->getOption('port'));
            $this->redis->setOption(\Redis::OPT_PREFIX, $this->getOption('prefix'));
        }
    }
}
