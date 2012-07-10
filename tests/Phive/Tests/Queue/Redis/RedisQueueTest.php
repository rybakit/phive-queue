<?php

namespace Phive\Tests\Queue\Redis;

use Phive\Tests\Queue\AbstractQueueTestCase;
use Phive\Queue\Redis\RedisQueue;

class RedisQueueTest extends AbstractQueueTestCase
{
    /**
     * @var RedisQueueManager
     */
    protected static $manager;

    public static function setUpBeforeClass()
    {
        if (!class_exists('\Redis')) {
            return;
        }

        parent::setUpBeforeClass();

        self::$manager = new RedisQueueManager(array(
            'host'      => $GLOBALS['redis_host'],
            'port'      => $GLOBALS['redis_port'],
            'prefix'    => $GLOBALS['redis_prefix'],
        ));
    }

    public function setUp()
    {
        if (!self::$manager) {
            $this->markTestSkipped('RedisQueue requires the php "phpredis" extension.');
        }

        parent::setUp();

        self::$manager->reset();
    }

    /**
     * @return \Phive\Queue\QueueInterface
     *
     * @throws \LogicException
     */
    public function createQueue()
    {
        if (self::$manager) {
            return self::$manager->createQueue();
        }

        throw new \LogicException('RedisQueueManager is not initialized.');
    }
}
