<?php

namespace Phive\Tests\Queue\Redis\PhpRedis;

use Phive\Queue\Tests\AbstractQueueTest;
use Phive\Queue\Redis\RedisQueue;

class RedisQueueTest extends AbstractQueueTest
{
    /**
     * @var \Redis
     */
    protected static $conn;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$conn = self::createConnection();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::clear(self::$conn);
        self::$conn->close();
        self::$conn = null;
    }

    public function setUp()
    {
        parent::setUp();

        self::clear(self::$conn);
    }

    public static function createConnection()
    {
        $host = isset($GLOBALS['redis_host']) ? $GLOBALS['redis_host'] : '127.0.0.1';
        $port = isset($GLOBALS['redis_port']) ? $GLOBALS['redis_port'] : 6379;

        $redis = new \Redis();
        $redis->connect($host, $port);
        $redis->setOption(\Redis::OPT_PREFIX, 'phive_queue_tests:');

        return $redis;
    }

    protected function createQueue()
    {
        return new RedisQueue(self::$conn);
    }

    protected static function clear(\Redis $redis)
    {
        $prefix = $redis->getOption(\Redis::OPT_PREFIX);
        $offset = strlen($prefix);

        $keys = $redis->keys('*');
        foreach ($keys as $key) {
            $redis->del(substr($key, $offset));
        }
    }
}