<?php

namespace Phive\Tests\Queue\Redis;

use Phive\Queue\Redis\RedisQueue;
use Phive\Tests\Queue\AbstractHandler;

class RedisHandler extends AbstractHandler
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function __construct(array $options = array())
    {
        if (!class_exists('\Redis')) {
            throw new \RuntimeException(__CLASS__.' requires the php "redis" extension.');
        }

        parent::__construct($options);

        $this->configure();
    }

    public function createQueue()
    {
        return new RedisQueue($this->redis);
    }

    public function reset()
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
