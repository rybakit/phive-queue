<?php

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\Queue\RedisQueue;

class RedisHandler extends AbstractHandler
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function createQueue()
    {
        return new RedisQueue($this->redis);
    }

    public function reset()
    {
        $this->clear();
    }

    public function clear()
    {
        $prefix = $this->redis->getOption(\Redis::OPT_PREFIX);
        $offset = strlen($prefix);

        $keys = $this->redis->keys('*');
        foreach ($keys as $key) {
            $this->redis->del(substr($key, $offset));
        }
    }

    protected function configure()
    {
        $this->redis = new \Redis();
        $this->redis->connect($this->getOption('host'), $this->getOption('port'));
        $this->redis->setOption(\Redis::OPT_PREFIX, $this->getOption('prefix'));
    }
}
