<?php

use Phive\Queue\Redis\RedisQueue;

class RedisHandler extends AbstractHandler
{
    protected static $conn;

    public function prepare()
    {
        self::clear();
    }

    public function shutdown()
    {
        self::clear();
    }

    protected function setup()
    {
        self::$conn = new \Redis();
        self::$conn->connect('127.0.0.1', 6379);
        self::$conn->setOption(\Redis::OPT_PREFIX, 'phive_tests:queue:');
    }

    protected function createQueue()
    {
        return new RedisQueue(self::$conn);
    }

    protected static function clear()
    {
        $prefix = self::$conn->getOption(\Redis::OPT_PREFIX);
        $offset = strlen($prefix);

        $keys = self::$conn->keys('*');
        foreach ($keys as $key) {
            self::$conn->del(substr($key, $offset));
        }
    }
}